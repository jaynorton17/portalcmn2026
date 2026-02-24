(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var config = window.cmnLiveChat || {};
    var root = document.querySelector('[data-cmn-livechat-root]');
    if (!root || !config || !config.ajaxUrl || !config.nonce) {
      return;
    }

    var toggleBtn = root.querySelector('[data-cmn-livechat-toggle]');
    var closeBtn = root.querySelector('[data-cmn-livechat-close]');
    var panel = root.querySelector('[data-cmn-livechat-panel]');
    var statusEl = root.querySelector('[data-cmn-livechat-status]');
    var prechatWrap = root.querySelector('[data-cmn-livechat-prechat]');
    var threadWrap = root.querySelector('[data-cmn-livechat-thread]');
    var startForm = root.querySelector('[data-cmn-livechat-start-form]');
    var sendForm = root.querySelector('[data-cmn-livechat-send-form]');
    var messagesEl = root.querySelector('[data-cmn-livechat-messages]');
    var feedbackWrap = root.querySelector('[data-cmn-livechat-feedback]');
    var feedbackForm = root.querySelector('[data-cmn-livechat-feedback-form]');
    var feedbackMsg = root.querySelector('[data-cmn-livechat-feedback-msg]');

    if (!toggleBtn || !panel || !startForm || !sendForm || !messagesEl) {
      return;
    }

    var state = {
      isOpen: false,
      threadToken: '',
      ticketRef: '',
      lastMessageId: 0,
      pollTimer: null,
      busy: false,
      initialised: false,
      threadClosed: false,
      feedbackSubmitted: false,
    };

    var storageKey = String(config.storageKey || 'cmn_livechat_thread_token_v1');
    var pollMs = Math.max(4000, parseInt(config.pollSeconds || 8, 10) * 1000 || 8000);
    var userContext = (config.userContext && typeof config.userContext === 'object') ? config.userContext : {};
    var identity = {
      enabled: !!parseInt(userContext.isLoggedIn || '0', 10),
      name: String(userContext.name || '').trim(),
      email: String(userContext.email || '').trim(),
      userType: String(userContext.userType || 'other').trim().toLowerCase()
    };
    if (['school', 'candidate', 'other'].indexOf(identity.userType) === -1) {
      identity.userType = 'other';
    }

    var applyIdentityDefaults = function () {
      var nameInput = startForm.querySelector('input[name="name"]');
      var emailInput = startForm.querySelector('input[name="email"]');
      var userTypeSelect = startForm.querySelector('select[name="user_type"]');
      if (!nameInput || !emailInput || !userTypeSelect) {
        return;
      }
      if (!identity.enabled) {
        nameInput.readOnly = false;
        emailInput.readOnly = false;
        userTypeSelect.disabled = false;
        return;
      }
      if (identity.name) {
        nameInput.value = identity.name;
      }
      if (identity.email) {
        emailInput.value = identity.email;
      }
      if (identity.userType) {
        userTypeSelect.value = identity.userType;
      }
      nameInput.readOnly = !!identity.name;
      emailInput.readOnly = !!identity.email;
      userTypeSelect.disabled = !!identity.userType;
    };

    var setStatus = function (message, tone) {
      if (!statusEl) {
        return;
      }
      var txt = String(message || '').trim();
      statusEl.textContent = txt;
      statusEl.classList.remove('is-success', 'is-error', 'is-warning');
      if (!txt) {
        return;
      }
      var resolvedTone = String(tone || 'warning').toLowerCase();
      if (resolvedTone !== 'success' && resolvedTone !== 'error') {
        resolvedTone = 'warning';
      }
      statusEl.classList.add('is-' + resolvedTone);
    };

    var saveThreadToken = function (token) {
      try {
        if (!token) {
          window.localStorage.removeItem(storageKey);
        } else {
          window.localStorage.setItem(storageKey, String(token));
        }
      } catch (_err) {
        // Ignore localStorage failures.
      }
    };

    var readThreadToken = function () {
      try {
        return String(window.localStorage.getItem(storageKey) || '').trim();
      } catch (_err) {
        return '';
      }
    };

    var setThreadMode = function (showThread) {
      var threadVisible = !!showThread;
      prechatWrap.hidden = threadVisible;
      threadWrap.hidden = !threadVisible;
      if (!threadVisible) {
        state.lastMessageId = 0;
        state.threadClosed = false;
        var sendBox = sendForm.querySelector('textarea[name="message"]');
        var sendBtn = sendForm.querySelector('button[type="submit"]');
        if (sendBox) {
          sendBox.disabled = false;
          sendBox.placeholder = 'Type your message...';
        }
        if (sendBtn) {
          sendBtn.disabled = false;
        }
        state.feedbackSubmitted = false;
        if (feedbackWrap) {
          feedbackWrap.hidden = true;
        }
        if (feedbackForm) {
          feedbackForm.reset();
        }
        if (feedbackMsg) {
          feedbackMsg.textContent = '';
        }
        applyIdentityDefaults();
      }
    };

    var setPanelOpen = function (open) {
      state.isOpen = !!open;
      panel.hidden = !state.isOpen;
      toggleBtn.setAttribute('aria-expanded', state.isOpen ? 'true' : 'false');
      if (state.isOpen) {
        root.classList.add('is-open');
      } else {
        root.classList.remove('is-open');
      }
    };

    var setSendAvailability = function (isClosed) {
      state.threadClosed = !!isClosed;
      var sendBox = sendForm.querySelector('textarea[name="message"]');
      var sendBtn = sendForm.querySelector('button[type="submit"]');
      if (sendBox) {
        sendBox.disabled = !!isClosed;
        if (isClosed) {
          sendBox.placeholder = 'This chat is closed.';
        } else if (!sendBox.placeholder || sendBox.placeholder === 'This chat is closed.') {
          sendBox.placeholder = 'Type your message...';
        }
      }
      if (sendBtn) {
        sendBtn.disabled = !!isClosed;
      }
    };

    var stopPolling = function () {
      if (state.pollTimer) {
        window.clearInterval(state.pollTimer);
        state.pollTimer = null;
      }
    };

    var ajax = function (action, payload) {
      var body = new FormData();
      body.append('action', action);
      body.append('nonce', String(config.nonce || ''));
      Object.keys(payload || {}).forEach(function (key) {
        body.append(key, payload[key]);
      });
      return fetch(String(config.ajaxUrl), {
        method: 'POST',
        credentials: 'same-origin',
        body: body,
      }).then(function (response) {
        return response.json().then(function (json) {
          return {
            ok: response.ok,
            status: response.status,
            json: json,
          };
        });
      });
    };

    var resolveMessageTime = function (value) {
      var raw = String(value || '').trim();
      if (!raw) {
        return '';
      }
      var normalized = raw.replace(' ', 'T');
      var dateObj = new Date(normalized);
      if (isNaN(dateObj.getTime())) {
        return raw;
      }
      return dateObj.toLocaleString([], {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
      });
    };

    var appendMessages = function (messages, resetBeforeAppend) {
      if (!Array.isArray(messages)) {
        return;
      }
      if (resetBeforeAppend) {
        messagesEl.innerHTML = '';
      }
      messages.forEach(function (msg) {
        var msgId = parseInt(msg && msg.id ? msg.id : '0', 10) || 0;
        if (msgId > 0) {
          var existing = messagesEl.querySelector('[data-livechat-message-id="' + String(msgId) + '"]');
          if (existing) {
            return;
          }
        }

        var row = document.createElement('div');
        row.className = 'cmn-livechat-message ' + (String(msg.sender_type || '') === 'support' ? 'is-support' : 'is-visitor');
        if (msgId > 0) {
          row.setAttribute('data-livechat-message-id', String(msgId));
          if (msgId > state.lastMessageId) {
            state.lastMessageId = msgId;
          }
        }

        var bubble = document.createElement('div');
        bubble.className = 'cmn-livechat-message__bubble';
        bubble.textContent = String(msg.message || '');

        var meta = document.createElement('div');
        meta.className = 'cmn-livechat-message__meta';
        var who = String(msg.sender_name || (String(msg.sender_type || '') === 'support' ? 'Support' : 'You'));
        var when = resolveMessageTime(msg.created_at_label || msg.created_at || '');
        meta.textContent = who + (when ? ' · ' + when : '');

        row.appendChild(bubble);
        row.appendChild(meta);
        messagesEl.appendChild(row);
      });
      messagesEl.scrollTop = messagesEl.scrollHeight;
    };

    var setFeedbackState = function (closed, submitted, feedback) {
      if (!feedbackWrap || !feedbackForm) {
        return;
      }
      var isClosed = !!closed;
      var isSubmitted = !!submitted;
      state.feedbackSubmitted = isSubmitted;
      feedbackWrap.hidden = !(isClosed);
      if (!isClosed) {
        feedbackForm.hidden = true;
        Array.prototype.slice.call(feedbackForm.querySelectorAll('select, textarea, button')).forEach(function (field) {
          field.disabled = false;
        });
        if (feedbackMsg) {
          feedbackMsg.textContent = '';
          feedbackMsg.classList.remove('is-success', 'is-error');
        }
        return;
      }

      if (isSubmitted) {
        feedbackForm.hidden = false;
        Array.prototype.slice.call(feedbackForm.querySelectorAll('select, textarea, button')).forEach(function (field) {
          field.disabled = true;
        });
        if (feedbackMsg) {
          feedbackMsg.textContent = 'Thanks for your feedback.';
          feedbackMsg.classList.remove('is-error');
          feedbackMsg.classList.add('is-success');
        }
        return;
      }

      feedbackForm.hidden = false;
      Array.prototype.slice.call(feedbackForm.querySelectorAll('select, textarea, button')).forEach(function (field) {
        field.disabled = false;
      });
      if (feedbackMsg) {
        feedbackMsg.textContent = '';
        feedbackMsg.classList.remove('is-success', 'is-error');
      }
      if (feedback && typeof feedback === 'object') {
        ['support_rating', 'response_time_rating', 'overall_satisfaction', 'issue_resolved', 'comments'].forEach(function (field) {
          var input = feedbackForm.querySelector('[name="' + field + '"]');
          if (!input) {
            return;
          }
          var rawValue = feedback[field];
          if (field === 'comments') {
            input.value = String(rawValue || '');
          } else if (rawValue !== null && typeof rawValue !== 'undefined') {
            input.value = String(rawValue);
          }
        });
      }
    };

    var resetToPrechat = function () {
      stopPolling();
      state.threadToken = '';
      state.ticketRef = '';
      state.lastMessageId = 0;
      state.threadClosed = false;
      saveThreadToken('');
      messagesEl.innerHTML = '';
      startForm.reset();
      setThreadMode(false);
    };

    var applyThreadState = function (ticketStatus, threadStatus, feedbackSubmitted, feedbackPayload) {
      var closed = String(ticketStatus || '') === 'closed' || String(threadStatus || '') !== 'open';
      var shouldPromptFeedback = closed && state.isOpen;
      setSendAvailability(closed);
      setFeedbackState(shouldPromptFeedback, !!feedbackSubmitted, feedbackPayload || null);
      if (closed) {
        // Clear persisted token so a page refresh resets to a fresh chat start.
        saveThreadToken('');
        if (!shouldPromptFeedback) {
          resetToPrechat();
          setStatus('', 'success');
          return;
        }
        if (!!feedbackSubmitted) {
          setStatus('This chat is closed. Thanks for your feedback.', 'success');
        } else {
          setStatus('This chat is closed. Please rate your support experience below.', 'warning');
        }
      } else {
        setStatus('', 'success');
      }
    };

    var pollThread = function (silent) {
      if (!state.threadToken) {
        return Promise.resolve();
      }
      return ajax('cmn_livechat_poll', {
        thread_token: state.threadToken,
        last_message_id: String(state.lastMessageId || 0),
      }).then(function (response) {
        var payload = response && response.json ? response.json : null;
        if (!payload || !payload.success || !payload.data) {
          if (response && (response.status === 403 || response.status === 404)) {
            resetToPrechat();
          }
          if (!silent) {
            setStatus((payload && payload.data && payload.data.message) ? payload.data.message : 'Unable to refresh chat right now.', 'error');
          }
          return;
        }
        var data = payload.data;
        if (Array.isArray(data.messages) && data.messages.length) {
          appendMessages(data.messages, false);
        }
        if (data.ticket_ref) {
          state.ticketRef = String(data.ticket_ref);
        }
        if (typeof data.last_message_id !== 'undefined') {
          var last = parseInt(String(data.last_message_id), 10) || 0;
          if (last > state.lastMessageId) {
            state.lastMessageId = last;
          }
        }
        applyThreadState(
          data.ticket_status,
          data.thread_status,
          !!parseInt(data.feedback_submitted || '0', 10),
          data.feedback || null
        );
      }).catch(function () {
        if (!silent) {
          setStatus('Unable to refresh chat right now.', 'error');
        }
      });
    };

    var startPolling = function () {
      stopPolling();
      state.pollTimer = window.setInterval(function () {
        if (document.hidden || !state.threadToken) {
          return;
        }
        pollThread(true);
      }, pollMs);
    };

    var openWithThread = function (threadToken, initialMessages, ticketRef, ticketStatus) {
      state.threadToken = String(threadToken || '').trim();
      if (!state.threadToken) {
        resetToPrechat();
        return;
      }
      state.ticketRef = String(ticketRef || '').trim();
      state.lastMessageId = 0;
      saveThreadToken(state.threadToken);
      setThreadMode(true);
      setPanelOpen(true);
      setStatus('', 'success');
      messagesEl.innerHTML = '';
      appendMessages(Array.isArray(initialMessages) ? initialMessages : [], true);
      applyThreadState(ticketStatus || 'open', 'open', false, null);
      startPolling();
      pollThread(true);
    };

    startForm.addEventListener('submit', function (event) {
      event.preventDefault();
      if (state.busy) {
        return;
      }

      var name = String((startForm.querySelector('input[name="name"]') || {}).value || '').trim();
      var email = String((startForm.querySelector('input[name="email"]') || {}).value || '').trim();
      var userType = String((startForm.querySelector('select[name="user_type"]') || {}).value || '').trim();
      var message = String((startForm.querySelector('textarea[name="message"]') || {}).value || '').trim();

      if (identity.enabled) {
        if (identity.name) {
          name = identity.name;
        }
        if (identity.email) {
          email = identity.email;
        }
        if (identity.userType) {
          userType = identity.userType;
        }
      }

      if (name.length < 2) {
        setStatus('Please enter your full name.', 'warning');
        return;
      }
      if (!email || email.indexOf('@') < 1) {
        setStatus('Please enter a valid email address.', 'warning');
        return;
      }
      if (!userType && !identity.enabled) {
        setStatus('Please select who you are.', 'warning');
        return;
      }
      if (!message) {
        setStatus('Please enter a message.', 'warning');
        return;
      }

      state.busy = true;
      var submitBtn = startForm.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
      }
      setStatus('Starting chat...', 'warning');

      ajax('cmn_livechat_start', {
        name: name,
        email: email,
        user_type: userType,
        message: message,
      }).then(function (response) {
        var payload = response && response.json ? response.json : null;
        if (!payload || !payload.success || !payload.data) {
          setStatus((payload && payload.data && payload.data.message) ? payload.data.message : 'Unable to start chat.', 'error');
          return;
        }
        startForm.reset();
        openWithThread(
          payload.data.thread_token || '',
          payload.data.messages || [],
          payload.data.ticket_ref || '',
          payload.data.status || 'open'
        );
      }).catch(function () {
        setStatus('Unable to start chat right now.', 'error');
      }).finally(function () {
        state.busy = false;
        if (submitBtn) {
          submitBtn.disabled = false;
        }
      });
    });

    sendForm.addEventListener('submit', function (event) {
      event.preventDefault();
      if (state.busy || !state.threadToken || state.threadClosed) {
        return;
      }

      var messageInput = sendForm.querySelector('textarea[name="message"]');
      var submitBtn = sendForm.querySelector('button[type="submit"]');
      var text = String((messageInput || {}).value || '').trim();
      if (!text) {
        return;
      }

      state.busy = true;
      if (submitBtn) {
        submitBtn.disabled = true;
      }
      if (messageInput) {
        messageInput.disabled = true;
      }
      setStatus('Sending...', 'warning');

      ajax('cmn_livechat_send', {
        thread_token: state.threadToken,
        message: text,
        last_message_id: String(state.lastMessageId || 0),
      }).then(function (response) {
        var payload = response && response.json ? response.json : null;
        if (!payload || !payload.success || !payload.data) {
          if (response && (response.status === 403 || response.status === 404)) {
            resetToPrechat();
          }
          setStatus((payload && payload.data && payload.data.message) ? payload.data.message : 'Unable to send message.', 'error');
          return;
        }
        var data = payload.data;
        if (messageInput) {
          messageInput.value = '';
        }
        if (Array.isArray(data.messages) && data.messages.length) {
          appendMessages(data.messages, false);
        }
        if (typeof data.last_message_id !== 'undefined') {
          var last = parseInt(String(data.last_message_id), 10) || 0;
          if (last > state.lastMessageId) {
            state.lastMessageId = last;
          }
        }
        applyThreadState(data.status || 'open', data.status || 'open', state.feedbackSubmitted, null);
        setStatus('', 'success');
      }).catch(function () {
        setStatus('Unable to send message.', 'error');
      }).finally(function () {
        state.busy = false;
        if (submitBtn) {
          submitBtn.disabled = false;
        }
        if (messageInput) {
          messageInput.disabled = state.threadClosed;
          if (!state.threadClosed) {
            messageInput.focus();
          }
        }
      });
    });

    if (feedbackForm) {
      feedbackForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (state.busy || !state.threadToken || !state.threadClosed || state.feedbackSubmitted) {
          return;
        }

        var supportRating = parseInt((feedbackForm.querySelector('select[name="support_rating"]') || {}).value || '0', 10);
        var responseRating = parseInt((feedbackForm.querySelector('select[name="response_time_rating"]') || {}).value || '0', 10);
        var overallRating = parseInt((feedbackForm.querySelector('select[name="overall_satisfaction"]') || {}).value || '0', 10);
        var issueResolved = String((feedbackForm.querySelector('select[name="issue_resolved"]') || {}).value || '').trim();
        var comments = String((feedbackForm.querySelector('textarea[name="comments"]') || {}).value || '').trim();

        if (supportRating < 1 || responseRating < 1 || overallRating < 1 || (issueResolved !== '1' && issueResolved !== '0')) {
          if (feedbackMsg) {
            feedbackMsg.textContent = 'Please complete all required feedback fields.';
            feedbackMsg.classList.remove('is-success');
            feedbackMsg.classList.add('is-error');
          }
          return;
        }

        state.busy = true;
        if (feedbackMsg) {
          feedbackMsg.textContent = 'Submitting feedback...';
          feedbackMsg.classList.remove('is-success', 'is-error');
        }

        ajax('cmn_livechat_feedback_submit', {
          thread_token: state.threadToken,
          support_rating: String(supportRating),
          response_time_rating: String(responseRating),
          overall_satisfaction: String(overallRating),
          issue_resolved: issueResolved,
          comments: comments,
        }).then(function (response) {
          var payload = response && response.json ? response.json : null;
          if (!payload || !payload.success || !payload.data) {
            if (feedbackMsg) {
              feedbackMsg.textContent = (payload && payload.data && payload.data.message) ? payload.data.message : 'Unable to submit feedback.';
              feedbackMsg.classList.remove('is-success');
              feedbackMsg.classList.add('is-error');
            }
            return;
          }
          state.feedbackSubmitted = true;
          setFeedbackState(true, true, payload.data.feedback || null);
          if (feedbackMsg) {
            feedbackMsg.textContent = 'Thanks for your feedback.';
            feedbackMsg.classList.remove('is-error');
            feedbackMsg.classList.add('is-success');
          }
          setStatus('This chat is closed. Thanks for your feedback.', 'success');
        }).catch(function () {
          if (feedbackMsg) {
            feedbackMsg.textContent = 'Unable to submit feedback.';
            feedbackMsg.classList.remove('is-success');
            feedbackMsg.classList.add('is-error');
          }
        }).finally(function () {
          state.busy = false;
        });
      });
    }

    toggleBtn.addEventListener('click', function () {
      setPanelOpen(!state.isOpen);
      if (state.isOpen) {
        if (state.threadToken) {
          pollThread(true);
        }
        setStatus('', 'success');
      }
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        setPanelOpen(false);
      });
    }

    document.querySelectorAll('[data-cmn-livechat-open]').forEach(function (trigger) {
      trigger.addEventListener('click', function (event) {
        event.preventDefault();
        setPanelOpen(true);
        setStatus('', 'success');
        if (state.threadToken) {
          pollThread(true);
          var threadInput = sendForm.querySelector('textarea[name="message"]');
          if (threadInput && !threadInput.disabled) {
            threadInput.focus();
          }
          return;
        }
        var nameInput = startForm.querySelector('input[name="name"]');
        if (nameInput && !nameInput.disabled) {
          nameInput.focus();
        }
      });
    });

    var storedToken = readThreadToken();
    applyIdentityDefaults();
    if (storedToken) {
      openWithThread(storedToken, [], '', 'open');
      setPanelOpen(false);
      pollThread(true).then(function () {
        state.initialised = true;
      });
    } else {
      setThreadMode(false);
      state.initialised = true;
    }

    window.addEventListener('beforeunload', function () {
      stopPolling();
    });
  });
})();
