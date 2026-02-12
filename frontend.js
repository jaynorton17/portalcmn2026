document.addEventListener('DOMContentLoaded', function () {
  var links = document.querySelectorAll('.cmn-register-link');
  if (!links.length) {
    // Still wire up register form toggles when not on login.
  }
  links.forEach(function (link) {
    link.addEventListener('click', function (event) {
      var href = link.getAttribute('href');
      var container = document.querySelector('.cmn-login-page');
      if (!href || !container) {
        return;
      }
      event.preventDefault();
      container.classList.add('cmn-transitioning');
      window.setTimeout(function () {
        window.location.href = href;
      }, 220);
    });
  });

  var portal = document.querySelector('.cmn-portal-light');
  var themeButtons = document.querySelectorAll('[data-theme]');
  var cmnThemeClasses = ['cmn-theme-default', 'cmn-theme-contrast', 'cmn-theme-light', 'cmn-theme-teal'];
  var cmnApplyThemeClass = function (theme) {
    var chosen = theme || 'default';
    cmnThemeClasses.forEach(function (cls) {
      document.body.classList.remove(cls);
    });
    document.body.classList.add('cmn-theme-' + chosen);
  };
  if (portal && themeButtons.length) {
    var savedTheme = localStorage.getItem('cmnTheme');
    if (savedTheme) {
      portal.setAttribute('data-theme', savedTheme);
    }
    themeButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var theme = btn.getAttribute('data-theme');
        if (theme) {
          portal.setAttribute('data-theme', theme);
          localStorage.setItem('cmnTheme', theme);
        }
      });
    });
  }

  var staffNav = document.querySelector('[data-staff-nav]');
  if (staffNav) {
    var staffNavUserId = staffNav.getAttribute('data-user-id') || '0';
    var staffNavStorageKey = 'cmn_staff_nav_state_v1_' + staffNavUserId;
    var readServerStaffNavState = function () {
      var raw = staffNav.getAttribute('data-nav-state') || '';
      if (!raw) {
        return {};
      }
      try {
        var parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' ? parsed : {};
      } catch (e) {
        return {};
      }
    };
    var readStaffNavState = function () {
      try {
        var raw = window.localStorage.getItem(staffNavStorageKey);
        if (!raw) {
          return {};
        }
        var parsed = JSON.parse(raw);
        return parsed && typeof parsed === 'object' ? parsed : {};
      } catch (e) {
        return {};
      }
    };
    var writeStaffNavState = function (state) {
      try {
        window.localStorage.setItem(staffNavStorageKey, JSON.stringify(state || {}));
      } catch (e) {
        // Ignore localStorage failures.
      }
    };
    var persistStaffNavState = function (state) {
      writeStaffNavState(state);
      if (!(window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.staffNavNonce)) {
        return;
      }
      var fd = new FormData();
      fd.append('action', 'cmn_save_staff_nav_state');
      fd.append('nonce', window.cmnPortal.staffNavNonce);
      fd.append('state', JSON.stringify(state || {}));
      fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      }).catch(function () {
        // Keep local state even if server sync fails.
      });
    };
    var setStaffGroupState = function (groupEl, isOpen) {
      if (!groupEl) {
        return;
      }
      groupEl.classList.toggle('is-open', !!isOpen);
      var toggle = groupEl.querySelector('[data-staff-nav-toggle]');
      if (toggle) {
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      }
    };
    var navState = readStaffNavState();
    var serverNavState = readServerStaffNavState();
    Object.keys(serverNavState).forEach(function (key) {
      if (!Object.prototype.hasOwnProperty.call(navState, key)) {
        navState[key] = !!serverNavState[key] ? 1 : 0;
      }
    });
    staffNav.querySelectorAll('[data-staff-nav-group]').forEach(function (groupEl) {
      var key = groupEl.getAttribute('data-staff-nav-group') || '';
      var hasActive = !!groupEl.querySelector('.cmn-school-nav-link.is-active');
      if (hasActive) {
        setStaffGroupState(groupEl, true);
        navState[key] = 1;
        return;
      }
      if (Object.prototype.hasOwnProperty.call(navState, key)) {
        setStaffGroupState(groupEl, !!navState[key]);
      } else {
        setStaffGroupState(groupEl, true);
      }
    });
    persistStaffNavState(navState);
    staffNav.querySelectorAll('[data-staff-nav-toggle]').forEach(function (toggleBtn) {
      toggleBtn.addEventListener('click', function () {
        var key = toggleBtn.getAttribute('data-staff-nav-toggle') || '';
        var groupEl = staffNav.querySelector('[data-staff-nav-group="' + key + '"]');
        if (!groupEl) {
          return;
        }
        var willOpen = !groupEl.classList.contains('is-open');
        setStaffGroupState(groupEl, willOpen);
        navState[key] = willOpen ? 1 : 0;
        persistStaffNavState(navState);
      });
    });
  }

  var actionMenus = document.querySelectorAll('[data-action-menu]');
  if (actionMenus.length) {
    var closeMenus = function () {
      actionMenus.forEach(function (menu) {
        menu.classList.remove('is-open');
      });
    };
    document.addEventListener('click', function () {
      closeMenus();
    });
    actionMenus.forEach(function (menu) {
      menu.addEventListener('click', function (event) {
        event.stopPropagation();
        menu.classList.toggle('is-open');
      });
      var dropdownButtons = menu.querySelectorAll('[data-action-target]');
      dropdownButtons.forEach(function (btn) {
        btn.addEventListener('click', function (event) {
          event.stopPropagation();
          var target = btn.getAttribute('data-action-target');
          if (!target) {
            return;
          }
          var panels = document.querySelectorAll('.cmn-action-panel');
          panels.forEach(function (panel) {
            panel.classList.toggle('is-active', panel.getAttribute('data-panel') === target);
          });
          closeMenus();
        });
      });
    });
    // Panels stay hidden until a menu option is selected (unless server marked one active).
  }

  var filterToggle = document.querySelector('[data-filter-toggle]');
  var filterPanel = document.querySelector('[data-filter-panel]');
  if (filterToggle && filterPanel) {
    filterToggle.addEventListener('click', function (event) {
      event.preventDefault();
      filterPanel.classList.toggle('is-open');
    });
  }

  var otherToggle = document.querySelector('[data-other-toggle]');
  var otherField = document.querySelector('.cmn-other-field');
  if (otherToggle && otherField) {
    var syncOther = function () {
      if (otherToggle.checked) {
        otherField.hidden = false;
      } else {
        otherField.hidden = true;
        var input = otherField.querySelector('input');
        if (input) {
          input.value = '';
        }
      }
    };
    otherToggle.addEventListener('change', syncOther);
    syncOther();
  }

  var dbsToggle = document.querySelector('[data-dbs-toggle]');
  var dbsInput = document.querySelector('.cmn-dbs-upload input[type=\"file\"]');
  var dbsNote = document.querySelector('.cmn-dbs-note');
  if (dbsToggle && dbsInput) {
    var syncDbs = function () {
      if (dbsToggle.checked) {
        dbsInput.value = '';
        dbsInput.disabled = true;
        if (dbsNote) {
          dbsNote.hidden = false;
        }
      } else {
        dbsInput.disabled = false;
        if (dbsNote) {
          dbsNote.hidden = true;
        }
      }
    };
    dbsToggle.addEventListener('change', syncDbs);
    syncDbs();
  }

  var selectAll = document.getElementById('cmn-select-all-days');
  var dayChecks = Array.prototype.slice.call(document.querySelectorAll('[data-day]'));
  if (selectAll && dayChecks.length) {
    var syncSelectAll = function () {
      var allChecked = dayChecks.every(function (el) {
        return el.checked;
      });
      selectAll.checked = allChecked;
    };
    selectAll.addEventListener('change', function () {
      dayChecks.forEach(function (el) {
        el.checked = selectAll.checked;
      });
    });
    dayChecks.forEach(function (el) {
      el.addEventListener('change', syncSelectAll);
    });
    syncSelectAll();
  }

  var selectAllSchools = document.getElementById('cmn-select-all-schools');
  if (selectAllSchools) {
    var schoolChecks = Array.prototype.slice.call(document.querySelectorAll('.cmn-school-select'));
    var syncSelectAllSchools = function () {
      if (!schoolChecks.length) {
        selectAllSchools.checked = false;
        return;
      }
      selectAllSchools.checked = schoolChecks.every(function (el) {
        return el.checked;
      });
    };
    selectAllSchools.addEventListener('change', function () {
      schoolChecks.forEach(function (el) {
        el.checked = selectAllSchools.checked;
      });
    });
    schoolChecks.forEach(function (el) {
      el.addEventListener('change', syncSelectAllSchools);
    });
    syncSelectAllSchools();
  }

  var bulkForm = document.querySelector('.cmn-bulk-form');
  if (bulkForm) {
    bulkForm.addEventListener('submit', function (event) {
      var actionSelect = bulkForm.querySelector('select[name="cmn_bulk_action"]');
      if (!actionSelect || !actionSelect.value) {
        event.preventDefault();
        alert('Select a bulk action first.');
        return;
      }
      if (actionSelect.value === 'delete') {
        var selected = bulkForm.querySelectorAll('.cmn-school-select:checked');
        if (!selected.length) {
          event.preventDefault();
          alert('Select at least one school to delete.');
          return;
        }
        if (!window.confirm('Delete selected schools? This cannot be undone.')) {
          event.preventDefault();
        }
      }
    });
  }

  var confirmForms = document.querySelectorAll('form[data-confirm]');
  if (confirmForms.length) {
    confirmForms.forEach(function (form) {
      form.addEventListener('submit', function (event) {
        var message = form.getAttribute('data-confirm');
        if (message && !window.confirm(message)) {
          event.preventDefault();
        }
      });
    });
  }

  var cleanupStaleTourElements = function () {
    var activeTourRoot = document.querySelector('[data-candidate-tour="1"]');
    if (activeTourRoot) {
      return;
    }
    document.querySelectorAll('.cmn-tour-overlay, .cmn-tour-popover, .cmn-tour-highlight').forEach(function (el) {
      if (el.classList && el.classList.contains('cmn-tour-highlight')) {
        el.classList.remove('cmn-tour-highlight');
        return;
      }
      if (el && el.parentNode) {
        el.parentNode.removeChild(el);
      }
    });
  };
  cleanupStaleTourElements();

  var availabilityButton = document.querySelector('[data-availability-button]');
  if (availabilityButton) {
    var availabilityAjaxUrl = (window.cmnPortal && window.cmnPortal.ajaxUrl) || availabilityButton.getAttribute('data-availability-ajax-url') || '';
    var availabilityNonce = (window.cmnPortal && window.cmnPortal.availabilityNonce) || availabilityButton.getAttribute('data-availability-nonce') || '';
    if (!availabilityAjaxUrl || !availabilityNonce) {
      if (window.console && typeof window.console.warn === 'function') {
        window.console.warn('CMN availability button is missing ajax url/nonce config.');
      }
    } else {
      var availabilityMessage = document.querySelector('[data-availability-message]');
      var availabilityCard = document.querySelector('[data-availability-card]');
      var availabilityHelper = document.querySelector('[data-availability-helper]');
      var calendarBlocked = availabilityButton.getAttribute('data-calendar-blocked') === '1';
      var unlockAtRaw = availabilityButton.getAttribute('data-availability-unlock-at') || '';
      var unlockAtTs = unlockAtRaw ? Date.parse(unlockAtRaw) : NaN;
      var availabilityUnlockTimer = null;
      availabilityButton.style.pointerEvents = 'auto';
      var maybeUnlockAvailabilityButton = function () {
        if (!availabilityButton.disabled || calendarBlocked || Number.isNaN(unlockAtTs)) {
          return;
        }
        if (Date.now() < unlockAtTs) {
          return;
        }
        availabilityButton.disabled = false;
        availabilityButton.removeAttribute('data-availability-unlock-at');
        unlockAtTs = NaN;
        if (availabilityHelper) {
          availabilityHelper.textContent = '';
        }
        if (availabilityMessage && availabilityMessage.textContent.trim().toLowerCase() === 'not confirmed yet.') {
          availabilityMessage.textContent = 'Not confirmed yet.';
        }
        if (availabilityUnlockTimer) {
          window.clearInterval(availabilityUnlockTimer);
          availabilityUnlockTimer = null;
        }
      };
      maybeUnlockAvailabilityButton();
      if (availabilityButton.disabled && !calendarBlocked) {
        var now = new Date();
        var nowHour = now.getHours();
        var nowMinute = now.getMinutes();
        // Fail-open in UI when stale disabled state is rendered; server still enforces availability rules.
        if (nowHour >= 19 || nowHour < 8 || (nowHour === 8 && nowMinute === 0)) {
          availabilityButton.disabled = false;
        } else if (availabilityHelper && !availabilityHelper.textContent.trim()) {
          availabilityHelper.textContent = 'You can confirm availability from 7pm until 8:00am.';
        }
      }
      if (availabilityButton.disabled && !Number.isNaN(unlockAtTs)) {
        availabilityUnlockTimer = window.setInterval(maybeUnlockAvailabilityButton, 30000);
      }
      var setAvailabilityVisualState = function (isAvailable) {
        availabilityButton.setAttribute('data-available', isAvailable ? '1' : '0');
        availabilityButton.textContent = isAvailable ? 'I’m NOT available tomorrow morning' : 'I’m available tomorrow morning';
        if (availabilityCard) {
          availabilityCard.classList.toggle('is-confirmed', !!isAvailable);
          if (!isAvailable) {
            availabilityCard.classList.remove('is-blocked');
          }
        }
      };
      availabilityButton.addEventListener('click', function () {
        if (availabilityButton.disabled) {
          if (availabilityHelper && availabilityHelper.textContent.trim()) {
            availabilityMessage.textContent = availabilityHelper.textContent.trim();
          }
          return;
        }
        availabilityButton.disabled = true;
        var formData = new FormData();
        formData.append('action', 'cmn_mark_available');
        formData.append('nonce', availabilityNonce);
        fetch(availabilityAjaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: formData,
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (data) {
            if (data && data.success) {
              if (availabilityMessage) {
                var successMsg = data.data && data.data.message ? data.data.message : 'Availability updated.';
                availabilityMessage.textContent = successMsg;
              }
              if (data.data && typeof data.data.status_text === 'string' && availabilityMessage) {
                availabilityMessage.textContent = data.data.status_text;
              }
              if (data.data && typeof data.data.available !== 'undefined') {
                setAvailabilityVisualState(!!data.data.available);
              } else {
                setAvailabilityVisualState(true);
              }
              if (data.data && typeof data.data.button_text === 'string') {
                availabilityButton.textContent = data.data.button_text;
              }
              if (data.data && typeof data.data.button_enabled !== 'undefined') {
                availabilityButton.disabled = !data.data.button_enabled;
              } else {
                availabilityButton.disabled = false;
              }
              if (!availabilityButton.disabled && availabilityUnlockTimer) {
                window.clearInterval(availabilityUnlockTimer);
                availabilityUnlockTimer = null;
              }
              if (availabilityHelper) {
                availabilityHelper.textContent = '';
              }
            } else {
              availabilityButton.disabled = false;
              if (availabilityMessage) {
                availabilityMessage.textContent = data && data.data && data.data.message ? data.data.message : 'Unable to save availability.';
              }
              if (data && data.data && typeof data.data.button_enabled !== 'undefined') {
                availabilityButton.disabled = !data.data.button_enabled;
              }
              if (data && data.data && typeof data.data.button_text === 'string') {
                availabilityButton.textContent = data.data.button_text;
              }
              if (availabilityHelper && data && data.data && data.data.message) {
                availabilityHelper.textContent = data.data.message;
              }
              if (availabilityCard && data && data.data && data.data.message && data.data.message.toLowerCase().indexOf('unavailable') !== -1) {
                availabilityCard.classList.add('is-blocked');
              }
            }
          })
          .catch(function () {
            availabilityButton.disabled = false;
            if (availabilityMessage) {
              availabilityMessage.textContent = 'Unable to save availability.';
            }
          });
      });
    }
  }

  var warRoomRoot = document.querySelector('[data-war-room-root]');
  if (warRoomRoot) {
    var refreshEvery = parseInt(warRoomRoot.getAttribute('data-refresh-seconds') || '10', 10);
    if (!refreshEvery || refreshEvery < 5) {
      refreshEvery = 10;
    }
    var countdownEls = Array.prototype.slice.call(warRoomRoot.querySelectorAll('[data-war-room-countdown]'));
    var renderCountdown = function () {
      var nowTs = Math.floor(Date.now() / 1000);
      countdownEls.forEach(function (el) {
        var expiresTs = parseInt(el.getAttribute('data-expires-ts') || '0', 10);
        if (!expiresTs) {
          el.textContent = 'No timer';
          return;
        }
        var remaining = expiresTs - nowTs;
        if (remaining <= 0) {
          el.textContent = 'Request expired';
          el.classList.add('is-expired');
          return;
        }
        var hours = Math.floor(remaining / 3600);
        var mins = Math.floor((remaining % 3600) / 60);
        var secs = remaining % 60;
        if (hours > 0) {
          el.textContent = 'Time remaining: ' + hours + 'h ' + mins + 'm';
        } else {
          el.textContent = 'Time remaining: ' + mins + 'm ' + (secs < 10 ? '0' : '') + secs + 's';
        }
      });
    };
    renderCountdown();
    window.setInterval(renderCountdown, 1000);
    window.setInterval(function () {
      if (document.visibilityState === 'visible') {
        window.location.reload();
      }
    }, refreshEvery * 1000);
  }

  var requestButtons = document.querySelectorAll('[data-request-candidate]');
  if (requestButtons.length && window.cmnPortal && window.cmnPortal.ajaxUrl) {
    requestButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (btn.disabled) {
          return;
        }
        btn.disabled = true;
        var card = btn.closest('.cmn-available-card') || btn.parentElement;
        var messageEl = card ? card.querySelector('[data-request-message]') : null;
        if (messageEl) {
          messageEl.textContent = 'Sending request...';
        }
        var formData = new FormData();
        formData.append('action', 'cmn_request_candidate');
        formData.append('nonce', window.cmnPortal.requestCandidateNonce || '');
        formData.append('candidate_id', btn.getAttribute('data-candidate-id') || '');
        var readyResponseSelect = card ? card.querySelector('[data-request-ready-response]') : null;
        if (readyResponseSelect) {
          formData.append('ready_response_id', readyResponseSelect.value || '');
        }
        fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: formData,
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (data) {
            if (data && data.success) {
              if (messageEl) {
                messageEl.textContent = data.data && data.data.message ? data.data.message : 'Your request has been sent.';
              }
            } else {
              btn.disabled = false;
              if (messageEl) {
                messageEl.textContent = data && data.data && data.data.message ? data.data.message : 'Unable to send request.';
              }
            }
          })
          .catch(function () {
            btn.disabled = false;
            if (messageEl) {
              messageEl.textContent = 'Unable to send request.';
            }
          });
      });
    });
  }

  var readyResponseRoot = document.querySelector('[data-ready-response-root]');
  if (readyResponseRoot) {
    var readyForm = readyResponseRoot.querySelector('[data-ready-response-form]');
    var readyIdInput = readyResponseRoot.querySelector('[data-ready-response-id]');
    var readyTitleInput = readyResponseRoot.querySelector('[data-ready-response-title]');
    var readyTemplateInput = readyResponseRoot.querySelector('[data-ready-response-template]');
    var readyDefaultInput = readyResponseRoot.querySelector('[data-ready-response-default]');
    var readySubmitBtn = readyResponseRoot.querySelector('[data-ready-response-submit]');
    var readyResetBtn = readyResponseRoot.querySelector('[data-ready-response-reset]');
    var readyPreviewBtn = readyResponseRoot.querySelector('[data-ready-response-preview]');
    var readyPreviewOut = readyResponseRoot.querySelector('[data-ready-response-preview-output]');

    var decodeTemplate = function (encodedValue) {
      try {
        return window.atob(String(encodedValue || ''));
      } catch (error) {
        return '';
      }
    };

    var resetReadyForm = function () {
      if (!readyForm) {
        return;
      }
      readyForm.reset();
      if (readyIdInput) {
        readyIdInput.value = '';
      }
      if (readySubmitBtn) {
        readySubmitBtn.textContent = 'Save template';
      }
      if (readyPreviewOut) {
        readyPreviewOut.hidden = true;
        readyPreviewOut.textContent = '';
      }
    };

    readyResponseRoot.querySelectorAll('[data-ready-response-edit]').forEach(function (editBtn) {
      editBtn.addEventListener('click', function () {
        if (!readyForm) {
          return;
        }
        if (readyIdInput) {
          readyIdInput.value = editBtn.getAttribute('data-ready-response-id') || '';
        }
        if (readyTitleInput) {
          readyTitleInput.value = editBtn.getAttribute('data-ready-response-title') || '';
        }
        if (readyTemplateInput) {
          readyTemplateInput.value = decodeTemplate(editBtn.getAttribute('data-ready-response-template') || '');
        }
        if (readyDefaultInput) {
          readyDefaultInput.checked = (editBtn.getAttribute('data-ready-response-default') || '0') === '1';
        }
        if (readySubmitBtn) {
          readySubmitBtn.textContent = 'Update template';
        }
        if (readyPreviewOut) {
          readyPreviewOut.hidden = true;
          readyPreviewOut.textContent = '';
        }
        readyForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      });
    });

    if (readyResetBtn) {
      readyResetBtn.addEventListener('click', function () {
        resetReadyForm();
      });
    }

    if (readyPreviewBtn && readyPreviewOut && readyTemplateInput) {
      readyPreviewBtn.addEventListener('click', function () {
        var rawTemplate = String(readyTemplateInput.value || '').trim();
        if (!rawTemplate) {
          readyPreviewOut.hidden = false;
          readyPreviewOut.textContent = 'Add template content to preview.';
          return;
        }
        var sample = {
          '{candidate_name}': 'Alex Morgan',
          '{school_name}': 'Riverside Academy',
          '{booking_date}': 'Mar 20, 2026',
          '{start_time}': '08:00',
          '{end_time}': '15:30',
          '{location_name}': 'Main Reception',
          '{location_address}': '1 School Lane, City, AB1 2CD',
          '{reception_instructions}': 'Sign in at front desk.',
          '{parking_info}': 'Visitor spaces by gate B.',
          '{teacher_name}': 'Ms Patel',
          '{contact_name}': 'Sam Green',
          '{contact_phone}': '07400 123456',
          '{notes}': 'Please bring photo ID.',
        };
        var preview = rawTemplate;
        Object.keys(sample).forEach(function (token) {
          preview = preview.split(token).join(sample[token]);
        });
        readyPreviewOut.hidden = false;
        readyPreviewOut.textContent = preview;
      });
    }
  }

  var requestToggles = document.querySelectorAll('[data-request-toggle]');
  if (requestToggles.length) {
    requestToggles.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var key = btn.getAttribute('data-request-toggle');
        if (!key) {
          return;
        }
        var panels = document.querySelectorAll('[data-request-panel]');
        panels.forEach(function (panel) {
          if (panel.getAttribute('data-request-panel') === key) {
            panel.classList.toggle('is-open');
          } else {
            panel.classList.remove('is-open');
          }
        });
      });
    });
  }

  var bellContainers = document.querySelectorAll('[data-bell]');
  if (bellContainers.length) {
    var refreshBellActionState = function (bell, payload) {
      if (!bell) {
        return;
      }
      var markBtn = bell.querySelector('[data-bell-mark]');
      var clearBtn = bell.querySelector('[data-bell-clear]');
      if (!markBtn && !clearBtn) {
        return;
      }
      var unread = 0;
      var itemCount = 0;
      if (payload && typeof payload.unread !== 'undefined') {
        unread = parseInt(payload.unread || 0, 10);
      } else {
        unread = bell.querySelectorAll('.cmn-bell-item.is-unread').length;
      }
      if (payload && Array.isArray(payload.items)) {
        itemCount = payload.items.length;
      } else {
        itemCount = bell.querySelectorAll('.cmn-bell-item').length;
      }
      if (markBtn) {
        markBtn.disabled = unread < 1;
      }
      if (clearBtn) {
        clearBtn.disabled = itemCount < 1;
      }
    };
    var refreshBellState = function (bell, payload) {
      if (!bell || !payload) {
        return;
      }
      var count = bell.querySelector('.cmn-bell-count');
      var unread = parseInt(payload.unread || 0, 10);
      if (unread > 0) {
        if (!count) {
          count = document.createElement('span');
          count.className = 'cmn-bell-count';
          var toggleBtn = bell.querySelector('[data-bell-toggle]');
          if (toggleBtn) {
            toggleBtn.appendChild(count);
          }
        }
        count.textContent = String(unread);
      } else if (count) {
        count.remove();
      }
      var unreadItems = bell.querySelectorAll('.cmn-bell-item.is-unread');
      unreadItems.forEach(function (item) {
        item.classList.remove('is-unread');
      });
      refreshBellActionState(bell, payload);
    };
    var closeBells = function () {
      bellContainers.forEach(function (bell) {
        bell.classList.remove('is-open');
        var panel = bell.querySelector('[data-bell-panel]');
        if (panel) {
          panel.classList.remove('is-open');
        }
      });
    };
    document.addEventListener('click', function () {
      closeBells();
    });
    bellContainers.forEach(function (bell) {
      var toggle = bell.querySelector('[data-bell-toggle]');
      var panel = bell.querySelector('[data-bell-panel]');
      var markBtn = bell.querySelector('[data-bell-mark]');
      var clearBtn = bell.querySelector('[data-bell-clear]');
      refreshBellActionState(bell, null);
      if (toggle && panel) {
        toggle.addEventListener('click', function (event) {
          event.stopPropagation();
          var isOpen = bell.classList.contains('is-open');
          closeBells();
          if (!isOpen) {
            bell.classList.add('is-open');
            panel.classList.add('is-open');
          }
        });
        panel.addEventListener('click', function (event) {
          event.stopPropagation();
        });
      }
      if (markBtn && window.cmnPortal && window.cmnPortal.ajaxUrl) {
        markBtn.addEventListener('click', function (event) {
          event.preventDefault();
          var formData = new FormData();
          formData.append('action', 'cmn_notifications_mark_all_read');
          formData.append('nonce', window.cmnPortal.notificationNonce || '');
          fetch(window.cmnPortal.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
          })
            .then(function (response) {
              return response.json();
            })
            .then(function (data) {
              if (data && data.success) {
                refreshBellState(bell, data.data || {});
              }
            });
        });
      }
      if (clearBtn && window.cmnPortal && window.cmnPortal.ajaxUrl) {
        clearBtn.addEventListener('click', function (event) {
          event.preventDefault();
          var formData = new FormData();
          formData.append('action', 'cmn_notifications_clear_all');
          formData.append('nonce', window.cmnPortal.notificationNonce || '');
          fetch(window.cmnPortal.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
          })
            .then(function (response) {
              return response.json();
            })
            .then(function (data) {
              if (data && data.success) {
                refreshBellState(bell, data.data || {});
                var list = bell.querySelector('.cmn-bell-list');
                if (list) {
                  list.innerHTML = '';
                }
                var empty = bell.querySelector('.cmn-empty');
                if (!empty && panel) {
                  empty = document.createElement('div');
                  empty.className = 'cmn-empty';
                  empty.textContent = 'No notifications yet.';
                  panel.appendChild(empty);
                }
                refreshBellActionState(bell, data.data || { unread: 0, items: [] });
              }
            });
        });
      }
      bell.querySelectorAll('[data-notification-link]').forEach(function (link) {
        link.addEventListener('click', function (event) {
          var notificationId = parseInt(link.getAttribute('data-notification-id') || '0', 10);
          if (!notificationId) {
            return;
          }
          event.preventDefault();
          var href = link.getAttribute('href') || '';
          var formData = new FormData();
          formData.append('action', 'cmn_notifications_mark_read');
          formData.append('nonce', window.cmnPortal.notificationNonce || '');
          formData.append('notification_id', notificationId);
          fetch(window.cmnPortal.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
          })
            .then(function (response) {
              return response.json();
            })
            .then(function (data) {
              if (data && data.success) {
                refreshBellState(bell, data.data || {});
              }
            })
            .finally(function () {
              if (href) {
                window.location.href = href;
              }
            });
        });
      });
    });
  }

  var countdown = document.querySelector('.cmn-availability-lock');
  if (countdown) {
    var seconds = parseInt(countdown.getAttribute('data-countdown') || '0', 10);
    var label = countdown.querySelector('.cmn-countdown');
    if (!isNaN(seconds) && label) {
      var tick = function () {
        var s = Math.max(0, seconds);
        var h = Math.floor(s / 3600);
        var m = Math.floor((s % 3600) / 60);
        var sec = s % 60;
        label.textContent = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
        if (seconds > 0) {
          seconds -= 1;
          window.setTimeout(tick, 1000);
        }
      };
      tick();
    }
  }

  var calendarForm = document.querySelector('.cmn-calendar-form');
  if (calendarForm) {
    var dataInput = calendarForm.querySelector('input[name="cmn_calendar_data"]');
    var monthInput = calendarForm.querySelector('input[name="cmn_calendar_month"]');
    var cells = calendarForm.querySelectorAll('.cmn-calendar-cell[data-date]');
    var data = {};
    try {
      data = JSON.parse(dataInput.value || '{}');
    } catch (e) {
      data = {};
    }

    var setStatus = function (date, status) {
      if (status) {
        data[date] = status;
      } else {
        delete data[date];
      }
      dataInput.value = JSON.stringify(data);
    };

    cells.forEach(function (cell) {
      cell.addEventListener('click', function () {
        var date = cell.getAttribute('data-date');
        var current = data[date] || '';
        var next = current === '' ? 'available' : current === 'available' ? 'unavailable' : '';
        cell.classList.remove('is-available', 'is-unavailable');
        if (next) {
          cell.classList.add(next === 'available' ? 'is-available' : 'is-unavailable');
        }
        setStatus(date, next);
      });
    });

    var controls = calendarForm.querySelectorAll('[data-calendar-set]');
    controls.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var mode = btn.getAttribute('data-calendar-set');
        if (!monthInput) {
          return;
        }
        var month = monthInput.value;
        var parts = month.split('-');
        if (parts.length !== 2) {
          return;
        }
        var year = parseInt(parts[0], 10);
        var monthIndex = parseInt(parts[1], 10) - 1;
        var d = new Date(year, monthIndex, 1);
        if (mode === 'clear') {
          cells.forEach(function (cell) {
            var date = cell.getAttribute('data-date');
            cell.classList.remove('is-available', 'is-unavailable');
            setStatus(date, '');
          });
          return;
        }
        while (d.getMonth() === monthIndex) {
          var day = d.getDay(); // 0 Sun, 6 Sat
          var isWeekday = day >= 1 && day <= 5;
          if (isWeekday) {
            var dateStr = d.toISOString().slice(0, 10);
            var cell = calendarForm.querySelector('.cmn-calendar-cell[data-date=\"' + dateStr + '\"]');
            if (cell) {
              cell.classList.remove('is-available', 'is-unavailable');
              if (mode === 'weekday-available') {
                cell.classList.add('is-available');
                setStatus(dateStr, 'available');
              } else if (mode === 'weekday-unavailable') {
                cell.classList.add('is-unavailable');
                setStatus(dateStr, 'unavailable');
              }
            }
          }
          d.setDate(d.getDate() + 1);
        }
      });
    });
  }

  var licenceRadios = document.querySelectorAll('[data-licence-toggle], [data-licence]');
  var carField = document.querySelector('.cmn-car-field');
  if (licenceRadios.length && carField) {
    var syncCar = function () {
      var hasLicence = false;
      licenceRadios.forEach(function (radio) {
        var isYes = radio.value === 'yes' || radio.getAttribute('data-licence') === 'yes';
        if (radio.checked && isYes) {
          hasLicence = true;
        }
      });
      if (hasLicence) {
        carField.hidden = false;
      } else {
        carField.hidden = true;
        var carRadios = carField.querySelectorAll('input[type=\"radio\"]');
        carRadios.forEach(function (radio) {
          radio.checked = false;
        });
      }
    };
    licenceRadios.forEach(function (radio) {
      radio.addEventListener('change', syncCar);
    });
    syncCar();
  }

  var emailConsole = document.querySelector('[data-email-test-console]');
  if (emailConsole && window.cmnPortal && window.cmnPortal.ajaxUrl) {
    var sendBtn = emailConsole.querySelector('[data-email-test-submit]');
    var typeSelect = emailConsole.querySelector('[data-email-test-type]');
    var emailInput = emailConsole.querySelector('[data-email-test-address]');
    var resultsBox = emailConsole.querySelector('[data-email-test-results]');
    if (sendBtn && typeSelect && emailInput && resultsBox) {
      sendBtn.addEventListener('click', function () {
        var email = emailInput.value.trim();
        if (!email) {
          resultsBox.textContent = 'Enter a test email address first.';
          return;
        }
        resultsBox.textContent = 'Sending test emails...';
        sendBtn.disabled = true;
        var formData = new FormData();
        formData.append('action', 'cmn_send_test_emails');
        formData.append('nonce', window.cmnPortal.testEmailNonce || '');
        formData.append('email_type', typeSelect.value || 'candidate');
        formData.append('email', email);
        fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: formData,
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (data) {
            sendBtn.disabled = false;
            if (!data || !data.success) {
              resultsBox.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to send test emails.';
              return;
            }
            var results = data.data && data.data.results ? data.data.results : [];
            if (!results.length) {
              resultsBox.textContent = 'No templates returned.';
              return;
            }
            var list = document.createElement('ul');
            list.className = 'cmn-email-test-list';
            results.forEach(function (item) {
              var li = document.createElement('li');
              var statusLabel = item.status === 'sent' ? '✔' : '✖';
              li.textContent = statusLabel + ' ' + item.type.replace(/_/g, ' ') + ' ' + item.status;
              li.className = item.status === 'sent' ? 'is-sent' : 'is-failed';
              list.appendChild(li);
            });
            resultsBox.innerHTML = '';
            if (data.data && data.data.from_email) {
              var fromLine = document.createElement('div');
              var fromName = data.data.from_name ? ' (' + data.data.from_name + ')' : '';
              fromLine.className = 'cmn-email-test-from';
              fromLine.textContent = 'From address used: ' + data.data.from_email + fromName;
              resultsBox.appendChild(fromLine);
            }
            resultsBox.appendChild(list);
          })
          .catch(function () {
            sendBtn.disabled = false;
            resultsBox.textContent = 'Unable to send test emails.';
          });
      });
    }
  }

  var staffForm = document.querySelector('[data-staff-add-form]');
  if (staffForm && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.staffNonce) {
    var staffMsg = staffForm.querySelector('[data-staff-form-msg]');
    staffForm.addEventListener('submit', function (event) {
      event.preventDefault();
      var name = staffForm.querySelector('input[name="cmn_staff_name"]');
      var email = staffForm.querySelector('input[name="cmn_staff_email"]');
      var username = staffForm.querySelector('input[name="cmn_staff_username"]');
      var role = staffForm.querySelector('select[name="cmn_staff_role"]');
      if (!name || !email || !role) {
        return;
      }
      if (staffMsg) {
        staffMsg.textContent = 'Adding staff...';
      }
      var formData = new FormData();
      formData.append('action', 'cmn_add_staff_user');
      formData.append('nonce', window.cmnPortal.staffNonce);
      formData.append('name', name.value.trim());
      formData.append('email', email.value.trim());
      formData.append('username', username ? username.value.trim() : '');
      formData.append('role', role.value || 'cmn_staff');
      fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (!data || !data.success) {
            if (staffMsg) {
              staffMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to add staff.';
            }
            return;
          }
          if (staffMsg) {
            staffMsg.textContent = 'Staff added. Reloading...';
          }
          window.location.reload();
        })
        .catch(function () {
          if (staffMsg) {
            staffMsg.textContent = 'Unable to add staff.';
          }
        });
    });
  }

  var staffModal = document.querySelector('[data-staff-modal]');
  if (staffModal && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.staffNonce) {
    var modalClose = staffModal.querySelector('[data-staff-modal-close]');
    var editForm = staffModal.querySelector('[data-staff-edit-form]');
    var editMsg = staffModal.querySelector('[data-staff-edit-msg]');
    var currentRow = null;

    var roleLabel = function (role) {
      if (role === 'cmn_admin') {
        return 'cmn_admin';
      }
      if (role === 'cmn_account_manager') {
        return 'cmn_account_manager';
      }
      return 'cmn_staff';
    };

    document.querySelectorAll('[data-staff-edit]').forEach(function (button) {
      button.addEventListener('click', function () {
        currentRow = button.closest('tr');
        if (!editForm) {
          return;
        }
        editForm.querySelector('input[name="staff_id"]').value = button.getAttribute('data-staff-id') || '';
        editForm.querySelector('input[name="staff_name"]').value = button.getAttribute('data-staff-name') || '';
        editForm.querySelector('input[name="staff_username"]').value = button.getAttribute('data-staff-username') || '';
        editForm.querySelector('input[name="staff_email"]').value = button.getAttribute('data-staff-email') || '';
        editForm.querySelector('select[name="staff_role"]').value = roleLabel(button.getAttribute('data-staff-role') || 'cmn_staff');
        if (editMsg) {
          editMsg.textContent = '';
        }
        staffModal.classList.add('is-open');
      });
    });

    if (modalClose) {
      modalClose.addEventListener('click', function () {
        staffModal.classList.remove('is-open');
      });
    }

    if (editForm) {
      editForm.addEventListener('submit', function (event) {
        event.preventDefault();
        var formData = new FormData();
        formData.append('action', 'cmn_update_staff_user');
        formData.append('nonce', window.cmnPortal.staffNonce);
        formData.append('user_id', editForm.querySelector('input[name="staff_id"]').value);
        formData.append('name', editForm.querySelector('input[name="staff_name"]').value.trim());
        formData.append('email', editForm.querySelector('input[name="staff_email"]').value.trim());
        formData.append('role', editForm.querySelector('select[name="staff_role"]').value);
        if (editMsg) {
          editMsg.textContent = 'Saving...';
        }
        fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: formData,
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (data) {
            if (!data || !data.success) {
              if (editMsg) {
                editMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to update staff.';
              }
              return;
            }
            if (currentRow && data.data && data.data.user) {
              var user = data.data.user;
              var nameCell = currentRow.querySelector('.cmn-staff-name');
              var emailCell = currentRow.querySelector('.cmn-staff-email');
              var roleCell = currentRow.querySelector('.cmn-staff-role');
              if (nameCell) {
                nameCell.textContent = user.name;
              }
              if (emailCell) {
                emailCell.textContent = user.email;
              }
              if (roleCell) {
                roleCell.textContent = user.role;
              }
              var editBtn = currentRow.querySelector('[data-staff-edit]');
              if (editBtn) {
                editBtn.setAttribute('data-staff-name', user.name);
                editBtn.setAttribute('data-staff-email', user.email);
                editBtn.setAttribute('data-staff-role', user.role);
              }
            }
            if (editMsg) {
              editMsg.textContent = 'Saved.';
            }
            staffModal.classList.remove('is-open');
          })
          .catch(function () {
            if (editMsg) {
              editMsg.textContent = 'Unable to update staff.';
            }
          });
      });
    }

    document.querySelectorAll('[data-staff-reset]').forEach(function (button) {
      button.addEventListener('click', function () {
        var userId = button.getAttribute('data-staff-id');
        if (!userId) {
          return;
        }
        if (!confirm('Send a password reset email?')) {
          return;
        }
        var formData = new FormData();
        formData.append('action', 'cmn_send_staff_reset_password');
        formData.append('nonce', window.cmnPortal.staffNonce);
        formData.append('user_id', userId);
        fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: formData,
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (data) {
            if (!data || !data.success) {
              alert((data && data.data && data.data.message) ? data.data.message : 'Unable to send reset email.');
              return;
            }
            alert('Reset email sent.');
          })
          .catch(function () {
            alert('Unable to send reset email.');
          });
      });
    });

    document.querySelectorAll('[data-staff-toggle]').forEach(function (button) {
      button.addEventListener('click', function () {
        var userId = button.getAttribute('data-staff-id');
        if (!userId) {
          return;
        }
        var formData = new FormData();
        formData.append('action', 'cmn_toggle_staff_deactivated');
        formData.append('nonce', window.cmnPortal.staffNonce);
        formData.append('user_id', userId);
        fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: formData,
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (data) {
            if (!data || !data.success) {
              alert((data && data.data && data.data.message) ? data.data.message : 'Unable to update status.');
              return;
            }
            var row = button.closest('tr');
            var statusCell = row ? row.querySelector('.cmn-staff-status') : null;
            var isDeactivated = data.data && data.data.status === 'deactivated';
            if (statusCell) {
              statusCell.innerHTML = '<span class="cmn-status-chip ' + (isDeactivated ? 'is-declined' : 'is-approved') + '">' + (isDeactivated ? 'Deactivated' : 'Active') + '</span>';
            }
            button.textContent = isDeactivated ? 'Reactivate' : 'Deactivate';
            button.setAttribute('data-staff-active', isDeactivated ? '0' : '1');
          })
          .catch(function () {
            alert('Unable to update status.');
          });
      });
    });
  }

  var supportRoots = document.querySelectorAll('[data-support-root]');
  if (supportRoots.length && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.supportNonce) {
    supportRoots.forEach(function (root) {
      var mode = root.getAttribute('data-support-mode') || 'user';
      var listEl = root.querySelector('[data-support-list]');
      var threadEl = root.querySelector('[data-support-thread]');
      var messagesEl = root.querySelector('[data-support-messages]');
      var replyForm = root.querySelector('[data-support-reply]');
      var openBtn = root.querySelector('[data-support-open]');
      var modal = root.parentElement.querySelector('[data-support-modal]') || document.querySelector('[data-support-modal]');
      var modalClose = modal ? modal.querySelector('[data-support-modal-close]') : null;
      var modalForm = modal ? modal.querySelector('[data-support-form]') : null;
      var modalMsg = modal ? modal.querySelector('[data-support-form-msg]') : null;
      var supportRole = root.getAttribute('data-support-role') || 'user';
      var activeTicketId = null;
      var activeTicket = null;
      var initialSupportFilter = (new URLSearchParams(window.location.search).get('support_filter') || '').toLowerCase();
      var filter = initialSupportFilter || (mode === 'admin' ? 'active' : 'all');
      var dashboard = root.querySelector('[data-support-dashboard]');
      var feedbackModal = root.parentElement.querySelector('[data-support-feedback-modal]') || root.querySelector('[data-support-feedback-modal]');
      var feedbackModalForm = feedbackModal ? feedbackModal.querySelector('[data-support-feedback-modal-form]') : null;
      var feedbackModalMsg = feedbackModal ? feedbackModal.querySelector('[data-support-feedback-modal-msg]') : null;
      var feedbackSubmitBtn = feedbackModalForm ? feedbackModalForm.querySelector('button[type="submit"]') : null;
      var insightsModal = root.parentElement.querySelector('[data-support-insights-modal]') || root.querySelector('[data-support-insights-modal]');
      var insightsList = insightsModal ? insightsModal.querySelector('[data-support-insights-list]') : null;
      var isFeedbackModalOpen = false;
      var isInsightsModalOpen = false;
      var feedbackSubmitInFlight = false;
      var feedbackSubmittedTicketIds = {};
      var feedbackCacheByTicket = {};
      var feedbackSuppressUntilByTicket = {};
      var dashboardData = { counts: {}, recent_feedback: [], recent_feedback_avg: 0 };
      var insightsFilter = 'recent';

      var getDeepTicketParam = function () {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('ticket_id') || urlParams.get('ticket') || '';
      };
      var isDebugMode = function () {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('cmn_debug') === '1';
      };

      var setDeepTicketParam = function (ticketId) {
        if (!ticketId) {
          return;
        }
        var url = new URL(window.location.href);
        url.searchParams.set('ticket_id', String(ticketId));
        if (url.searchParams.has('ticket')) {
          url.searchParams.delete('ticket');
        }
        window.history.replaceState({}, '', url.toString());
      };

      var syncSelectedTicketRow = function () {
        if (!listEl) {
          return;
        }
        listEl.querySelectorAll('.cmn-support-ticket').forEach(function (row) {
          var rowId = parseInt(row.getAttribute('data-ticket-id') || '0', 10);
          row.classList.toggle('is-selected', !!activeTicketId && rowId === activeTicketId);
        });
      };

      var supportFetch = function (action, payload) {
        var formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', window.cmnPortal.supportNonce);
        Object.keys(payload || {}).forEach(function (key) {
          formData.append(key, payload[key]);
        });
        return fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: formData,
        }).then(function (response) { return response.json(); });
      };

      var forceCloseFeedbackModals = function () {
        document.querySelectorAll('[data-support-feedback-modal]').forEach(function (modalEl) {
          modalEl.hidden = true;
          modalEl.classList.remove('is-open');
        });
        document.querySelectorAll('[data-support-root]').forEach(function (rootEl) {
          rootEl.classList.remove('is-feedback-modal-open');
        });
        if (feedbackModalMsg) {
          feedbackModalMsg.textContent = '';
        }
        if (typeof resetFeedbackModal === 'function') {
          resetFeedbackModal();
        }
        if (typeof setFeedbackModalOpen === 'function') {
          setFeedbackModalOpen(false);
        }
        isFeedbackModalOpen = false;
        syncSupportModalLock();
      };

      var syncSupportModalLock = function () {
        var shouldLock = isFeedbackModalOpen || isInsightsModalOpen;
        document.body.classList.toggle('cmn-support-modal-lock', shouldLock);
      };

      var setFeedbackModalOpen = function (open) {
        if (!feedbackModal) {
          return;
        }
        isFeedbackModalOpen = !!open;
        feedbackModal.hidden = !isFeedbackModalOpen;
        root.classList.toggle('is-feedback-modal-open', isFeedbackModalOpen);
        syncSupportModalLock();
      };

      var setInsightsModalOpen = function (open) {
        if (!insightsModal) {
          return;
        }
        isInsightsModalOpen = !!open;
        insightsModal.hidden = !isInsightsModalOpen;
        syncSupportModalLock();
      };

      var updateStarPickerVisuals = function (picker, selectedValue, hoverValue) {
        if (!picker) {
          return;
        }
        var stars = picker.querySelectorAll('button[data-star-value]');
        stars.forEach(function (starBtn) {
          var starValue = parseInt(starBtn.getAttribute('data-star-value') || '0', 10);
          starBtn.classList.toggle('is-hover', hoverValue > 0 && starValue <= hoverValue);
          starBtn.classList.toggle('is-active', hoverValue === 0 && selectedValue > 0 && starValue <= selectedValue);
        });
      };

      var initStarPicker = function (fieldName) {
        if (!feedbackModalForm) {
          return;
        }
        var picker = feedbackModalForm.querySelector('[data-feedback-stars="' + fieldName + '"]');
        var hiddenInput = feedbackModalForm.querySelector('input[name="' + fieldName + '"]');
        var scoreEl = feedbackModalForm.querySelector('[data-feedback-score="' + fieldName + '"]');
        if (!picker || !hiddenInput || picker.childElementCount > 0) {
          return;
        }
        var selectedValue = 0;
        var hoverValue = 0;
        for (var i = 1; i <= 5; i += 1) {
          var star = document.createElement('button');
          star.type = 'button';
          star.className = 'cmn-star-btn';
          star.setAttribute('data-star-value', String(i));
          star.setAttribute('aria-label', i + ' stars');
          star.textContent = '★';
          star.addEventListener('mouseenter', function (event) {
            hoverValue = parseInt(event.currentTarget.getAttribute('data-star-value') || '0', 10);
            updateStarPickerVisuals(picker, selectedValue, hoverValue);
          });
          star.addEventListener('click', function (event) {
            selectedValue = parseInt(event.currentTarget.getAttribute('data-star-value') || '0', 10);
            hiddenInput.value = String(selectedValue);
            if (scoreEl) {
              scoreEl.textContent = selectedValue + '/5';
            }
            hoverValue = 0;
            updateStarPickerVisuals(picker, selectedValue, hoverValue);
          });
          picker.appendChild(star);
        }
        picker.addEventListener('mouseleave', function () {
          hoverValue = 0;
          updateStarPickerVisuals(picker, selectedValue, hoverValue);
        });
        updateStarPickerVisuals(picker, selectedValue, hoverValue);
      };

      var resetFeedbackModal = function () {
        if (!feedbackModalForm) {
          return;
        }
        feedbackModalForm.reset();
        feedbackSubmitInFlight = false;
        if (feedbackSubmitBtn) {
          feedbackSubmitBtn.disabled = false;
        }
        ['support_rating', 'response_time_rating', 'overall_satisfaction'].forEach(function (field) {
          var picker = feedbackModalForm.querySelector('[data-feedback-stars="' + field + '"]');
          var scoreEl = feedbackModalForm.querySelector('[data-feedback-score="' + field + '"]');
          if (picker) {
            updateStarPickerVisuals(picker, 0, 0);
          }
          if (scoreEl) {
            scoreEl.textContent = '0/5';
          }
        });
        var toggleButtons = feedbackModalForm.querySelectorAll('[data-feedback-resolved]');
        toggleButtons.forEach(function (btn) {
          btn.classList.remove('is-active');
        });
        var issueInput = feedbackModalForm.querySelector('input[name="issue_resolved"]');
        if (issueInput) {
          issueInput.value = '';
        }
        if (feedbackModalMsg) {
          feedbackModalMsg.textContent = '';
        }
      };

      var maybeOpenFeedbackModal = function (ticket, feedback) {
        if (mode === 'admin' || !feedbackModal || !ticket || (supportRole !== 'candidate' && supportRole !== 'school')) {
          return;
        }
        if (ticket.status !== 'closed') {
          setFeedbackModalOpen(false);
          return;
        }
        var ticketKey = ticket && ticket.id ? String(ticket.id) : '';
        if (ticketKey && feedbackSuppressUntilByTicket[ticketKey] && Date.now() < feedbackSuppressUntilByTicket[ticketKey]) {
          setFeedbackModalOpen(false);
          return;
        }
        if (feedback || (ticketKey && feedbackSubmittedTicketIds[ticketKey]) || (ticketKey && feedbackCacheByTicket[ticketKey])) {
          setFeedbackModalOpen(false);
          return;
        }
        if (feedbackSubmitInFlight) {
          return;
        }
        initStarPicker('support_rating');
        initStarPicker('response_time_rating');
        initStarPicker('overall_satisfaction');
        resetFeedbackModal();
        setFeedbackModalOpen(true);
      };

      var getTicketVisualState = function (ticket) {
        var normalized = String(ticket.status || 'open').toLowerCase();
        var state = {
          icon: '◍',
          iconClass: 'is-open',
          statusLabel: 'Open',
        };
        if (normalized === 'closed') {
          state.icon = '✓';
          state.iconClass = 'is-closed';
          state.statusLabel = 'Closed';
          return state;
        }
        if (normalized === 'new' || (mode === 'admin' && parseInt(ticket.is_new_for_admin || 0, 10) === 1)) {
          state.icon = '●';
          state.iconClass = 'is-new';
          state.statusLabel = 'New';
        }
        return state;
      };

      var renderDashboard = function (dashboardPayload) {
        dashboardData = dashboardPayload || { counts: {}, recent_feedback: [], recent_feedback_avg: 0 };
        if (!dashboard) {
          return;
        }
        dashboard.querySelectorAll('[data-support-count]').forEach(function (countEl) {
          var key = countEl.getAttribute('data-support-count') || '';
          if (key === 'feedback_avg') {
            var avg = Number(dashboardData.recent_feedback_avg || 0);
            countEl.textContent = (avg > 0 ? avg.toFixed(1) : '0.0') + '/5';
            return;
          }
          var value = dashboardData.counts && Object.prototype.hasOwnProperty.call(dashboardData.counts, key) ? dashboardData.counts[key] : 0;
          countEl.textContent = String(value);
        });
      };

      var normalizeSupportFilterForMode = function (nextFilter) {
        var value = String(nextFilter || '').toLowerCase();
        if (mode === 'admin') {
          if (value === '' || value === 'open') {
            return 'active';
          }
          if (['active', 'new', 'open', 'closed', 'new_open', 'all', 'needs_feedback'].indexOf(value) === -1) {
            return 'active';
          }
          return value;
        }
        if (value === '' || value === 'active' || value === 'new_open' || value === 'new') {
          return 'all';
        }
        if (['all', 'open', 'closed', 'needs_feedback'].indexOf(value) === -1) {
          return 'all';
        }
        if (value === '') {
          return mode === 'admin' ? 'active' : 'all';
        }
        return value;
      };

      var syncFilterUiState = function () {
        var activeFilter = normalizeSupportFilterForMode(filter);
        root.querySelectorAll('[data-support-filter]').forEach(function (btn) {
          btn.classList.toggle('is-active', btn.getAttribute('data-support-filter') === activeFilter);
        });
        root.querySelectorAll('[data-support-tile]').forEach(function (tileBtn) {
          var tileKey = tileBtn.getAttribute('data-support-tile') || '';
          if (tileKey === 'feedback_insights') {
            tileBtn.classList.remove('is-active');
            return;
          }
          var expected = mode === 'admin' && tileKey === 'open' ? 'active' : tileKey;
          tileBtn.classList.toggle('is-active', expected === activeFilter);
        });
      };

      var renderInsightsList = function () {
        if (!insightsList) {
          return;
        }
        var rows = Array.isArray(dashboardData.recent_feedback) ? dashboardData.recent_feedback.slice() : [];
        if (insightsFilter === 'lowest') {
          rows.sort(function (a, b) {
            return (a.overall_satisfaction || 0) - (b.overall_satisfaction || 0);
          });
        } else if (insightsFilter === 'unresolved') {
          rows = rows.filter(function (row) { return String(row.issue_resolved || '0') === '0'; });
        } else if (insightsFilter === 'overall_lte_3') {
          rows = rows.filter(function (row) { return parseInt(row.overall_satisfaction || 0, 10) <= 3; });
        } else {
          rows.sort(function (a, b) {
            var ta = Date.parse(a.created_at || '') || 0;
            var tb = Date.parse(b.created_at || '') || 0;
            return tb - ta;
          });
        }
        if (!rows.length) {
          insightsList.innerHTML = '<div class="cmn-empty">No feedback entries for this view.</div>';
          return;
        }
        var out = document.createElement('div');
        out.className = 'cmn-support-insights-rows';
        rows.slice(0, 30).forEach(function (row) {
          var item = document.createElement('button');
          item.type = 'button';
          item.className = 'cmn-support-insight-row';
          item.innerHTML = '<strong>' + (row.ticket_ref || ('#' + String(row.ticket_id || ''))) + '</strong><span>' + (row.subject || 'Support ticket') + '</span><em>Overall ' + (row.overall_satisfaction || 0) + '/5 · Resolved: ' + (String(row.issue_resolved || '0') === '1' ? 'Yes' : 'No') + ' · ' + (row.created_at || '') + '</em>';
          item.addEventListener('click', function () {
            setInsightsModalOpen(false);
            if (row.ticket_id) {
              setDeepTicketParam(row.ticket_id);
              loadTicket(row.ticket_id);
            } else if (row.ticket_ref) {
              setDeepTicketParam(row.ticket_ref);
              loadTicket(row.ticket_ref);
            }
          });
          out.appendChild(item);
        });
        insightsList.innerHTML = '';
        insightsList.appendChild(out);
      };

      var buildTicketListItem = function (ticket) {
        var visual = getTicketVisualState(ticket);
        var item = document.createElement('button');
        item.type = 'button';
        item.className = 'cmn-support-ticket';
        item.setAttribute('data-ticket-id', ticket.id);
        var feedbackCount = parseInt(ticket.feedback_count || 0, 10);
        var feedbackBadge = '';
        if (feedbackCount > 0) {
          feedbackBadge = '<span class="cmn-support-ticket-badge">Feedback received</span>';
        } else if (String(ticket.status || '') === 'closed') {
          feedbackBadge = '<span class="cmn-support-ticket-badge is-warning">Needs feedback</span>';
        }
        item.innerHTML = '<strong><span class="cmn-ticket-status-icon ' + visual.iconClass + '">' + visual.icon + '</span>' + ticket.ref + '</strong><span class="cmn-ticket-subject">' + ticket.subject + '</span><em>' + visual.statusLabel + ' · ' + (ticket.updated_at || '') + '</em>' + feedbackBadge;
        item.addEventListener('click', function () {
          root.querySelectorAll('.cmn-support-ticket').forEach(function (row) { row.classList.remove('is-selected'); });
          item.classList.add('is-selected');
          setDeepTicketParam(ticket.id);
          loadTicket(ticket.id);
        });
        if (activeTicketId && parseInt(ticket.id, 10) === parseInt(activeTicketId, 10)) {
          item.classList.add('is-selected');
        }
        return item;
      };

      var renderList = function (tickets, hasPendingSelection) {
        if (!listEl) {
          return;
        }
        if (!tickets.length) {
          listEl.innerHTML = hasPendingSelection ? '<div class="cmn-empty">Loading selected ticket...</div>' : '<div class="cmn-empty">No tickets in this filter.</div>';
          return;
        }
        var ul = document.createElement('div');
        ul.className = 'cmn-support-ticket-list';
        tickets.forEach(function (ticket) {
          ul.appendChild(buildTicketListItem(ticket));
        });
        listEl.innerHTML = '';
        listEl.appendChild(ul);
        syncSelectedTicketRow();
      };

      var renderMessages = function (ticket, messages) {
        if (!messagesEl) {
          return;
        }
        messagesEl.innerHTML = '';
        if (!messages.length) {
          messagesEl.innerHTML = '<div class="cmn-empty">No messages yet.</div>';
          return;
        }
        messages.forEach(function (msg) {
          var bubble = document.createElement('div');
          bubble.className = 'cmn-support-bubble ' + (msg.sender_type === 'admin' ? 'is-admin' : 'is-user');
          var meta = document.createElement('div');
          meta.className = 'cmn-support-meta';
          meta.textContent = msg.sender_type === 'admin' ? 'Support' : 'You';
          var text = document.createElement('div');
          text.className = 'cmn-support-text';
          text.textContent = msg.message;
          bubble.appendChild(meta);
          bubble.appendChild(text);
          if (Array.isArray(msg.attachments) && msg.attachments.length) {
            var chips = document.createElement('div');
            chips.className = 'cmn-support-attachments';
            msg.attachments.forEach(function (attachment) {
              var chip = document.createElement('a');
              chip.className = 'cmn-support-attachment-chip';
              chip.href = attachment.url;
              chip.target = '_blank';
              chip.rel = 'noopener noreferrer';
              chip.textContent = attachment.filename || 'Attachment';
              chips.appendChild(chip);
            });
            bubble.appendChild(chips);
          }
          messagesEl.appendChild(bubble);
        });
        messagesEl.scrollTop = messagesEl.scrollHeight;
      };

      var updateThreadHeader = function (ticket) {
        if (!threadEl) {
          return;
        }
        var titleEl = threadEl.querySelector('[data-support-thread-title]');
        var refEl = threadEl.querySelector('[data-support-thread-ref]');
        if (titleEl) {
          titleEl.textContent = ticket ? ticket.subject : 'Support';
        }
        if (refEl) {
          refEl.textContent = ticket ? (ticket.ticket_ref || ticket.ref || '') : 'Select a ticket to view the conversation.';
        }
      };

      var toggleAdminButtons = function (ticket) {
        var closeBtn = root.querySelector('[data-support-close-ticket]');
        var reopenBtn = root.querySelector('[data-support-reopen-ticket]');
        var saveTranscriptBtn = root.querySelector('[data-support-save-transcript]');
        var emailTranscriptBtn = root.querySelector('[data-support-email-transcript]');
        if (!closeBtn || !reopenBtn) {
          closeBtn = null;
          reopenBtn = null;
        }
        if (saveTranscriptBtn) {
          saveTranscriptBtn.disabled = !ticket;
        }
        if (emailTranscriptBtn) {
          emailTranscriptBtn.disabled = !ticket;
        }
        if (!ticket) {
          if (closeBtn) {
            closeBtn.disabled = true;
          }
          if (reopenBtn) {
            reopenBtn.disabled = true;
          }
          return;
        }
        if (closeBtn) {
          closeBtn.disabled = ticket.status === 'closed';
        }
        if (reopenBtn) {
          reopenBtn.disabled = ticket.status !== 'closed';
        }
      };

      var renderFeedback = function (ticket, feedback) {
        var feedbackRoot = root.querySelector('[data-support-feedback]');
        var feedbackBadge = root.querySelector('[data-support-feedback-badge]');
        if (ticket && feedback && ticket.id) {
          feedbackSubmittedTicketIds[String(ticket.id)] = true;
          feedbackCacheByTicket[String(ticket.id)] = feedback;
        }
        if (feedbackBadge) {
          feedbackBadge.hidden = !(feedback && ticket && ticket.status === 'closed');
        }
        if (!feedbackRoot) {
          return;
        }
        feedbackRoot.innerHTML = '';
        if (!ticket || ticket.status !== 'closed') {
          forceCloseFeedbackModals();
          return;
        }
        if (feedback) {
          var summary = document.createElement('div');
          summary.className = 'cmn-support-feedback-summary';
          summary.innerHTML = '<strong>Feedback submitted</strong><span>Support: ' + feedback.support_rating + '/5 · Response: ' + feedback.response_time_rating + '/5 · Overall: ' + feedback.overall_satisfaction + '/5</span><span>Resolved: ' + (String(feedback.issue_resolved) === '1' ? 'Yes' : 'No') + '</span>';
          if (feedback.comments) {
            var comment = document.createElement('p');
            comment.textContent = feedback.comments;
            summary.appendChild(comment);
          }
          feedbackRoot.appendChild(summary);
          forceCloseFeedbackModals();
          return;
        }
        if (mode === 'admin') {
            return;
        }
        if (supportRole === 'candidate' || supportRole === 'school') {
          feedbackRoot.innerHTML = '<div class="cmn-muted">Please submit feedback for this closed ticket.</div>';
          maybeOpenFeedbackModal(ticket, null);
          return;
        }
      };

      if (feedbackModalForm && (supportRole === 'candidate' || supportRole === 'school')) {
        initStarPicker('support_rating');
        initStarPicker('response_time_rating');
        initStarPicker('overall_satisfaction');

        var resolvedButtons = feedbackModalForm.querySelectorAll('[data-feedback-resolved]');
        resolvedButtons.forEach(function (btn) {
          btn.addEventListener('click', function () {
            resolvedButtons.forEach(function (otherBtn) {
              otherBtn.classList.remove('is-active');
            });
            btn.classList.add('is-active');
            var issueInput = feedbackModalForm.querySelector('input[name="issue_resolved"]');
            if (issueInput) {
              issueInput.value = btn.getAttribute('data-feedback-resolved') || '';
            }
          });
        });

        feedbackModalForm.addEventListener('submit', function (event) {
          event.preventDefault();
          if (!activeTicketId) {
            return;
          }
          if (feedbackSubmitInFlight) {
            return;
          }
          var supportRating = parseInt((feedbackModalForm.querySelector('input[name="support_rating"]') || {}).value || '0', 10);
          var responseRating = parseInt((feedbackModalForm.querySelector('input[name="response_time_rating"]') || {}).value || '0', 10);
          var overallRating = parseInt((feedbackModalForm.querySelector('input[name="overall_satisfaction"]') || {}).value || '0', 10);
          var resolvedValue = (feedbackModalForm.querySelector('input[name="issue_resolved"]') || {}).value || '';
          if (supportRating < 1 || responseRating < 1 || overallRating < 1 || (resolvedValue !== '1' && resolvedValue !== '0')) {
            if (feedbackModalMsg) {
              feedbackModalMsg.textContent = 'Please complete all required feedback fields.';
            }
            return;
          }
          var fd = new FormData(feedbackModalForm);
          fd.append('action', 'cmn_support_submit_feedback');
          fd.append('nonce', window.cmnPortal.supportNonce);
          fd.append('ticket_id', activeTicketId);
          var submittingTicketId = parseInt(activeTicketId, 10) || 0;
          feedbackSubmitInFlight = true;
          if (feedbackSubmitBtn) {
            feedbackSubmitBtn.disabled = true;
          }
          if (feedbackModalMsg) {
            feedbackModalMsg.textContent = 'Submitting...';
          }
          fetch(window.cmnPortal.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: fd,
          }).then(function (response) {
            return response.json();
          }).then(function (data) {
            if (!data || !data.success) {
              if (feedbackModalMsg) {
                feedbackModalMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to submit feedback.';
              }
              feedbackSubmitInFlight = false;
              if (feedbackSubmitBtn) {
                feedbackSubmitBtn.disabled = false;
              }
              return;
            }
            var returnedFeedback = data.data && data.data.feedback ? data.data.feedback : null;
            var feedbackTicketId = returnedFeedback && returnedFeedback.ticket_id ? parseInt(returnedFeedback.ticket_id, 10) : submittingTicketId;
            var localFeedback = returnedFeedback || {
              ticket_id: feedbackTicketId,
              support_rating: supportRating,
              response_time_rating: responseRating,
              overall_satisfaction: overallRating,
              issue_resolved: resolvedValue === '1' ? 1 : 0,
              comments: String((feedbackModalForm.querySelector('textarea[name="comments"]') || {}).value || '')
            };
            if (feedbackTicketId > 0) {
              feedbackSubmittedTicketIds[String(feedbackTicketId)] = true;
              feedbackCacheByTicket[String(feedbackTicketId)] = localFeedback;
              feedbackSuppressUntilByTicket[String(feedbackTicketId)] = Date.now() + 15000;
            }
            feedbackSubmitInFlight = false;
            if (feedbackSubmitBtn) {
              feedbackSubmitBtn.disabled = true;
            }
            setFeedbackModalOpen(false);
            forceCloseFeedbackModals();
            if (feedbackModalMsg) {
              feedbackModalMsg.textContent = '';
            }
            if (activeTicket && feedbackTicketId > 0 && parseInt(activeTicket.id || '0', 10) === feedbackTicketId) {
              activeTicket.feedback_count = 1;
            }
            renderFeedback(activeTicket, localFeedback);
            loadTickets();
            if (feedbackTicketId > 0) {
              loadTicket(feedbackTicketId);
            } else if (activeTicketId) {
              loadTicket(activeTicketId);
            }
          }).catch(function () {
            if (feedbackModalMsg) {
              feedbackModalMsg.textContent = 'Unable to submit feedback.';
            }
            feedbackSubmitInFlight = false;
            if (feedbackSubmitBtn) {
              feedbackSubmitBtn.disabled = false;
            }
          });
        });
      }

      var loadTicket = function (ticketRefOrId) {
        if (!ticketRefOrId) {
          return;
        }
        var payload = {};
        var ticketValue = String(ticketRefOrId).trim();
        var parsed = parseInt(ticketValue, 10);
        if (/^\d+$/.test(ticketValue) && !Number.isNaN(parsed)) {
          activeTicketId = parsed;
          payload.ticket_id = parsed;
        } else {
          activeTicketId = null;
          payload.ticket_ref = ticketValue;
        }
        supportFetch('cmn_support_get_ticket', payload).then(function (data) {
          if (!data || !data.success) {
            forceCloseFeedbackModals();
            updateThreadHeader(null);
            if (messagesEl) {
              messagesEl.innerHTML = '<div class="cmn-empty">' + ((data && data.data && data.data.message) ? data.data.message : 'Ticket not found.') + '</div>';
            }
            return;
          }
          var ticket = data.data.ticket;
          activeTicket = ticket;
          activeTicketId = ticket && ticket.id ? ticket.id : activeTicketId;
          setDeepTicketParam(activeTicketId);
          syncSelectedTicketRow();
          updateThreadHeader(ticket);
          toggleAdminButtons(ticket);
          if (replyForm) {
            var ta = replyForm.querySelector('textarea[name="message"]');
            var sendBtn = replyForm.querySelector('button[type="submit"]');
            var fileInput = replyForm.querySelector('input[type="file"]');
            var isClosed = ticket && ticket.status === 'closed';
            if (ta) {
              ta.disabled = !!isClosed;
            }
            if (sendBtn) {
              sendBtn.disabled = !!isClosed;
            }
            if (fileInput) {
              fileInput.disabled = !!isClosed;
            }
          }
          renderMessages(ticket, data.data.messages || []);
          var ticketFeedback = data.data.feedback || (ticket && ticket.id ? feedbackCacheByTicket[String(ticket.id)] : null) || null;
          renderFeedback(ticket, ticketFeedback);
          if (listEl && activeTicketId) {
            var selectedRow = listEl.querySelector('.cmn-support-ticket[data-ticket-id="' + String(activeTicketId) + '"]');
            if (!selectedRow) {
              var fallbackTicket = {
                id: activeTicketId,
                ref: ticket.ticket_ref || ticket.ref || ('#' + String(activeTicketId)),
                subject: ticket.subject || 'Support ticket',
                status: ticket.status || 'open',
                updated_at: ticket.updated_at || '',
                is_new_for_admin: ticket.is_new_for_admin || 0,
                feedback_count: 0
              };
              listEl.innerHTML = '';
              var ul = document.createElement('div');
              ul.className = 'cmn-support-ticket-list';
              ul.appendChild(buildTicketListItem(fallbackTicket));
              listEl.appendChild(ul);
              syncSelectedTicketRow();
            }
          }
        });
      };

      var loadTickets = function () {
        var payload = {};
        if (filter) {
          payload.status = filter;
        }
        var deepTicket = getDeepTicketParam();
        if (deepTicket) {
          if (/^\d+$/.test(String(deepTicket))) {
            payload.ticket_id = parseInt(deepTicket, 10);
          } else {
            payload.ticket_ref = String(deepTicket);
          }
        }
        supportFetch('cmn_support_list_tickets', payload).then(function (data) {
          if (!data || !data.success) {
            if (listEl) {
              listEl.innerHTML = '<div class="cmn-empty">Unable to load tickets.</div>';
            }
            return;
          }
          if (isDebugMode() && data.data && data.data.debug) {
            console.log('CMN Support Debug:', data.data.debug);
          }
          renderDashboard((data.data && data.data.dashboard) ? data.data.dashboard : null);
          renderInsightsList();
          if (data.data && data.data.forced_filter) {
            filter = normalizeSupportFilterForMode(data.data.forced_filter);
          }
          syncFilterUiState();
          if (data.data && data.data.selected_ticket_id) {
            activeTicketId = parseInt(data.data.selected_ticket_id, 10) || activeTicketId;
          }
          var ticketsPayload = Array.isArray(data.data.tickets) ? data.data.tickets : [];
          var allCount = (data.data && data.data.dashboard && data.data.dashboard.counts) ? parseInt(data.data.dashboard.counts.all || '0', 10) : 0;
          if (!deepTicket && mode !== 'admin' && ticketsPayload.length === 0 && allCount > 0 && normalizeSupportFilterForMode(filter) !== 'all') {
            filter = 'all';
            syncFilterUiState();
            loadTickets();
            return;
          }
          renderList(ticketsPayload, !!deepTicket);
          if (deepTicket) {
            loadTicket(deepTicket);
          } else if (mode === 'admin' && ticketsPayload.length) {
            loadTicket(ticketsPayload[0].id);
          } else if (mode !== 'admin' && ticketsPayload.length) {
            loadTicket(ticketsPayload[0].id);
          } else {
            forceCloseFeedbackModals();
            activeTicketId = null;
            activeTicket = null;
            updateThreadHeader(null);
            if (messagesEl) {
              messagesEl.innerHTML = '<div class="cmn-empty">Select a ticket to view messages.</div>';
            }
          }
        });
      };

      root.querySelectorAll('[data-support-filter]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          filter = normalizeSupportFilterForMode(btn.getAttribute('data-support-filter') || '');
          syncFilterUiState();
          loadTickets();
        });
      });

      root.querySelectorAll('[data-support-tile]').forEach(function (tileBtn) {
        tileBtn.addEventListener('click', function () {
          var tile = tileBtn.getAttribute('data-support-tile') || '';
          if (tile === 'feedback_insights') {
            insightsFilter = 'recent';
            if (insightsModal) {
              insightsModal.querySelectorAll('[data-support-insights-filter]').forEach(function (btn, idx) {
                btn.classList.toggle('is-active', idx === 0);
              });
            }
            renderInsightsList();
            setInsightsModalOpen(true);
            return;
          }
          filter = normalizeSupportFilterForMode(tile);
          syncFilterUiState();
          loadTickets();
        });
      });

      if (insightsModal) {
        insightsModal.querySelectorAll('[data-support-insights-close]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            setInsightsModalOpen(false);
          });
        });
        insightsModal.querySelectorAll('[data-support-insights-filter]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            insightsFilter = btn.getAttribute('data-support-insights-filter') || 'recent';
            insightsModal.querySelectorAll('[data-support-insights-filter]').forEach(function (other) {
              other.classList.remove('is-active');
            });
            btn.classList.add('is-active');
            renderInsightsList();
          });
        });
      }

      if (mode === 'admin') {
        var closeBtn = root.querySelector('[data-support-close-ticket]');
        var reopenBtn = root.querySelector('[data-support-reopen-ticket]');
        if (closeBtn) {
          closeBtn.addEventListener('click', function () {
            if (!activeTicketId) {
              return;
            }
            supportFetch('cmn_support_close_ticket', { ticket_id: activeTicketId, status: 'closed' }).then(function () {
              loadTickets();
              loadTicket(activeTicketId);
            });
          });
        }
      }

      var reopenBtnGlobal = root.querySelector('[data-support-reopen-ticket]');
      if (reopenBtnGlobal) {
        reopenBtnGlobal.addEventListener('click', function () {
          if (!activeTicketId) {
            return;
          }
          supportFetch('cmn_support_close_ticket', { ticket_id: activeTicketId, status: 'open' }).then(function () {
            loadTickets();
            loadTicket(activeTicketId);
          });
        });
      }

      var saveTranscriptBtn = root.querySelector('[data-support-save-transcript]');
      if (saveTranscriptBtn) {
        saveTranscriptBtn.addEventListener('click', function () {
          if (!activeTicketId) {
            return;
          }
          supportFetch('cmn_support_save_transcript', { ticket_id: activeTicketId }).then(function (data) {
            if (data && data.success) {
              alert('Transcript saved.');
            }
          });
        });
      }

      var emailTranscriptBtn = root.querySelector('[data-support-email-transcript]');
      if (emailTranscriptBtn) {
        emailTranscriptBtn.addEventListener('click', function () {
          if (!activeTicketId) {
            return;
          }
          supportFetch('cmn_support_email_transcript', { ticket_id: activeTicketId }).then(function (data) {
            if (data && data.success) {
              alert('Transcript emailed to ticket owner.');
            }
          });
        });
      }

      if (replyForm) {
        replyForm.addEventListener('submit', function (event) {
          event.preventDefault();
          if (!activeTicketId) {
            return;
          }
          var textarea = replyForm.querySelector('textarea[name="message"]');
          if (!textarea || !textarea.value.trim()) {
            return;
          }
          var formData = new FormData(replyForm);
          formData.append('action', 'cmn_support_post_message');
          formData.append('nonce', window.cmnPortal.supportNonce);
          formData.append('ticket_id', activeTicketId);
          var textValue = textarea.value.trim();
          formData.set('message', textValue);
          fetch(window.cmnPortal.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
          }).then(function (response) {
            return response.json();
          }).then(function (data) {
            if (!data || !data.success) {
              return;
            }
            textarea.value = '';
            loadTicket(activeTicketId);
            loadTickets();
          });
        });
      }

      if (openBtn && modal) {
        openBtn.addEventListener('click', function () {
          modal.classList.add('is-open');
        });
      }
      if (modalClose && modal) {
        modalClose.addEventListener('click', function () {
          modal.classList.remove('is-open');
        });
      }
      if (modalForm) {
        modalForm.addEventListener('submit', function (event) {
          event.preventDefault();
          var subjectInput = modalForm.querySelector('input[name="subject"]');
          var messageInput = modalForm.querySelector('textarea[name="message"]');
          var categoryInput = modalForm.querySelector('select[name="category"]');
          if (!subjectInput || !messageInput) {
            return;
          }
          if (modalMsg) {
            modalMsg.textContent = 'Submitting...';
          }
          var fd = new FormData(modalForm);
          fd.append('action', 'cmn_support_create_ticket');
          fd.append('nonce', window.cmnPortal.supportNonce);
          fd.set('subject', subjectInput.value.trim());
          fd.set('message', messageInput.value.trim());
          fd.set('category', categoryInput ? categoryInput.value : '');
          fetch(window.cmnPortal.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: fd,
          }).then(function (response) { return response.json(); }).then(function (data) {
            if (!data || !data.success) {
              if (modalMsg) {
                modalMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to submit ticket.';
              }
              return;
            }
            if (modalMsg) {
              modalMsg.textContent = 'Ticket created.';
            }
            modalForm.reset();
            modal.classList.remove('is-open');
            loadTickets();
            if (data.data && data.data.ticket) {
              loadTicket(data.data.ticket.id);
            }
          });
        });
      }

      if (mode !== 'admin' && (supportRole === 'candidate' || supportRole === 'school')) {
        window.setInterval(function () {
          if (!activeTicketId || isFeedbackModalOpen || document.hidden) {
            return;
          }
          supportFetch('cmn_support_get_ticket', { ticket_id: activeTicketId }).then(function (data) {
            if (!data || !data.success || !data.data || !data.data.ticket) {
              return;
            }
            var latestTicket = data.data.ticket;
            var previousStatus = activeTicket && activeTicket.status ? activeTicket.status : '';
            activeTicket = latestTicket;
            if (latestTicket.status !== previousStatus || (latestTicket.status === 'closed' && !(data.data.feedback || null))) {
              updateThreadHeader(latestTicket);
              toggleAdminButtons(latestTicket);
              renderFeedback(latestTicket, data.data.feedback || null);
              loadTickets();
            }
          });
        }, 5000);
      }

      syncFilterUiState();
      loadTickets();
    });
  }

  var staffLoungeRoots = document.querySelectorAll('[data-staff-lounge]');
  if (staffLoungeRoots.length && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.staffLoungeNonce) {
    staffLoungeRoots.forEach(function (root) {
      var threadType = root.getAttribute('data-thread-type') || 'staff_lounge';
      var listEl = root.querySelector('[data-staff-lounge-messages]');
      var formEl = root.querySelector('[data-staff-lounge-form]');
      var msgEl = root.querySelector('[data-staff-lounge-message]');
      var inFlight = false;

      var setMsg = function (text, isError) {
        if (!msgEl) {
          return;
        }
        msgEl.textContent = text || '';
        msgEl.style.color = isError ? '#ef4444' : '';
      };

      var renderRows = function (rows) {
        if (!listEl) {
          return;
        }
        listEl.innerHTML = '';
        if (!Array.isArray(rows) || !rows.length) {
          listEl.innerHTML = '<div class="cmn-empty">No messages yet.</div>';
          return;
        }
        rows.forEach(function (row) {
          var senderRole = String(row.sender_role || 'staff').toLowerCase();
          var bubbleClass = 'is-admin';
          if (senderRole === 'account_manager') {
            bubbleClass = 'is-user is-school';
          } else if (senderRole === 'staff') {
            bubbleClass = 'is-user is-candidate';
          }
          var bubble = document.createElement('div');
          bubble.className = 'cmn-support-bubble cmn-staff-lounge-bubble ' + bubbleClass;
          var meta = document.createElement('div');
          meta.className = 'cmn-support-meta';
          meta.textContent = (row.sender_name || 'Staff') + ' · ' + (row.created_at || '');
          var text = document.createElement('div');
          text.className = 'cmn-support-text';
          text.textContent = row.message || '';
          bubble.appendChild(meta);
          bubble.appendChild(text);
          listEl.appendChild(bubble);
        });
        listEl.scrollTop = listEl.scrollHeight;
      };

      var fetchRows = function () {
        var fd = new FormData();
        fd.append('action', 'cmn_staff_lounge_fetch');
        fd.append('nonce', window.cmnPortal.staffLoungeNonce || '');
        fd.append('thread_type', threadType);
        return fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd
        }).then(function (response) {
          return response.json();
        }).then(function (data) {
          if (!data || !data.success) {
            return;
          }
          renderRows((data.data && data.data.messages) || []);
        }).catch(function () {
          return null;
        });
      };

      if (formEl) {
        formEl.addEventListener('submit', function (event) {
          event.preventDefault();
          if (inFlight) {
            return;
          }
          var input = formEl.querySelector('textarea[name="message"]');
          var text = input ? input.value.trim() : '';
          if (!text) {
            setMsg('Message is required.', true);
            return;
          }
          inFlight = true;
          var submitBtn = formEl.querySelector('button[type="submit"]');
          if (submitBtn) {
            submitBtn.disabled = true;
          }
          setMsg('Sending...', false);
          var fd = new FormData();
          fd.append('action', 'cmn_staff_lounge_post');
          fd.append('nonce', window.cmnPortal.staffLoungeNonce || '');
          fd.append('thread_type', threadType);
          fd.append('message', text);
          fetch(window.cmnPortal.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
          }).then(function (response) {
            return response.json();
          }).then(function (data) {
            if (!data || !data.success) {
              setMsg((data && data.data && data.data.message) ? data.data.message : 'Unable to send message.', true);
              return;
            }
            renderRows((data.data && data.data.messages) || []);
            if (input) {
              input.value = '';
            }
            setMsg((data.data && data.data.message) ? data.data.message : 'Message sent.', false);
          }).catch(function () {
            setMsg('Unable to send message.', true);
          }).finally(function () {
            inFlight = false;
            if (submitBtn) {
              submitBtn.disabled = false;
            }
          });
        });
      }

      fetchRows();
      window.setInterval(fetchRows, 5000);
    });
  }

  var marketingRoot = document.querySelector('[data-marketing-root]');
  if (marketingRoot && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.staffNonce) {
    var marketingState = {
      rows: [],
      selectedIds: {},
      activeCampaignId: 0
    };
    var activeTab = 'lead_finder';
    var resultBody = marketingRoot.querySelector('[data-marketing-results]');
    var listBody = marketingRoot.querySelector('[data-marketing-lists]');
    var campaignsBody = marketingRoot.querySelector('[data-marketing-campaigns]');
    var queueBody = marketingRoot.querySelector('[data-marketing-queue]');
    var repliesBody = marketingRoot.querySelector('[data-marketing-replies]');
    var leadMsg = marketingRoot.querySelector('[data-marketing-message]');
    var listsMsg = marketingRoot.querySelector('[data-marketing-lists-message]');
    var campaignMsg = marketingRoot.querySelector('[data-marketing-campaign-message]');
    var queueSummary = marketingRoot.querySelector('[data-marketing-queue-summary]');
    var repliesSummary = marketingRoot.querySelector('[data-marketing-replies-summary]');
    var previewSubject = marketingRoot.querySelector('[data-marketing-preview-subject]');
    var previewBody = marketingRoot.querySelector('[data-marketing-preview-body]');
    var previewMissing = marketingRoot.querySelector('[data-marketing-preview-missing]');

    var mFetch = function (action, payload) {
      var fd = new FormData();
      fd.append('action', action);
      fd.append('nonce', window.cmnPortal.staffNonce || '');
      Object.keys(payload || {}).forEach(function (key) {
        var value = payload[key];
        if (Array.isArray(value)) {
          value.forEach(function (item) {
            fd.append(key + '[]', item);
          });
          return;
        }
        if (typeof value !== 'undefined' && value !== null) {
          fd.append(key, value);
        }
      });
      return fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      }).then(function (response) {
        return response.json();
      });
    };

    var setTab = function (tab) {
      activeTab = tab;
      marketingRoot.querySelectorAll('[data-marketing-tab]').forEach(function (btn) {
        btn.classList.toggle('is-active', btn.getAttribute('data-marketing-tab') === tab);
      });
      marketingRoot.querySelectorAll('[data-marketing-panel]').forEach(function (panel) {
        panel.hidden = panel.getAttribute('data-marketing-panel') !== tab;
      });
    };

    marketingRoot.querySelectorAll('[data-marketing-tab]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        setTab(btn.getAttribute('data-marketing-tab') || 'lead_finder');
      });
    });

    var readLeadFilters = function () {
      var get = function (key, fallback) {
        var el = marketingRoot.querySelector('[data-marketing-filter="' + key + '"]');
        return el ? el.value : (fallback || '');
      };
      return {
        status: get('status', 'lead'),
        stage: get('stage', ''),
        contacting: get('contacting', ''),
        days: parseInt(get('days', '14'), 10) || 14,
        location: get('location', ''),
        radius_center: get('radius_center', ''),
        radius_miles: parseInt(get('radius_miles', '0'), 10) || 0,
        manager: get('manager', ''),
        completeness: get('completeness', ''),
        exclude_campaign_id: parseInt(get('exclude_campaign_id', '0'), 10) || 0,
        q: get('q', '')
      };
    };

    var renderLeadRows = function (rows) {
      if (!resultBody) {
        return;
      }
      resultBody.innerHTML = '';
      if (!rows.length) {
        resultBody.innerHTML = '<tr><td colspan="10">No schools matched.</td></tr>';
        return;
      }
      rows.forEach(function (row) {
        var tr = document.createElement('tr');
        tr.innerHTML = '' +
          '<td><input type="checkbox" data-marketing-row-select value="' + row.school_id + '"' + (marketingState.selectedIds[row.school_id] ? ' checked' : '') + '></td>' +
          '<td>' + (row.school_name || '') + '</td>' +
          '<td>' + (row.location || '') + '</td>' +
          '<td>' + (row.school_email || '') + '</td>' +
          '<td>' + (row.status || '') + '</td>' +
          '<td>' + (row.pipeline_stage || '') + '</td>' +
          '<td>' + (row.account_manager_name || '—') + '</td>' +
          '<td>' + (row.last_contacted || '—') + '</td>' +
          '<td>' + (row.last_replied || '—') + '</td>' +
          '<td>' + (row.distance_miles || '—') + '</td>';
        resultBody.appendChild(tr);
      });
      resultBody.querySelectorAll('[data-marketing-row-select]').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
          var id = parseInt(checkbox.value || '0', 10);
          if (!id) {
            return;
          }
          if (checkbox.checked) {
            marketingState.selectedIds[id] = 1;
          } else {
            delete marketingState.selectedIds[id];
          }
        });
      });
    };

    var refreshLeadRows = function () {
      if (leadMsg) {
        leadMsg.textContent = 'Loading...';
      }
      return mFetch('cmn_marketing_lead_finder', readLeadFilters()).then(function (data) {
        if (!data || !data.success) {
          if (leadMsg) {
            leadMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to load leads.';
          }
          renderLeadRows([]);
          return;
        }
        marketingState.rows = data.data.rows || [];
        renderLeadRows(marketingState.rows);
        if (leadMsg) {
          var base = String(data.data.count || marketingState.rows.length) + ' school(s) found.';
          var warning = data.data.warning ? (' ' + data.data.warning) : '';
          leadMsg.textContent = base + warning;
        }
      }).catch(function () {
        if (leadMsg) {
          leadMsg.textContent = 'Unable to load leads.';
        }
      });
    };

    var syncCampaignListOptions = function (lists) {
      var select = marketingRoot.querySelector('[data-marketing-campaign="list_id"]');
      if (!select) {
        return;
      }
      var previous = select.value;
      select.innerHTML = '<option value="">Select list</option>';
      (lists || []).forEach(function (list) {
        var option = document.createElement('option');
        option.value = String(list.id || 0);
        option.textContent = list.name || ('List ' + String(list.id || 0));
        select.appendChild(option);
      });
      if (previous && select.querySelector('option[value="' + previous + '"]')) {
        select.value = previous;
      }
    };

    var listAction = function (action, listId, confirmText) {
      if (!listId) {
        return;
      }
      if (confirmText && !window.confirm(confirmText)) {
        return;
      }
      if (listsMsg) {
        listsMsg.textContent = 'Working...';
      }
      mFetch(action, { list_id: listId }).then(function (data) {
        if (!data || !data.success || !data.data) {
          if (listsMsg) {
            listsMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to update list.';
          }
          return;
        }
        if (listsMsg) {
          listsMsg.textContent = data.data.message || 'Saved.';
        }
        var lists = data.data.lists || [];
        renderLists(lists);
        syncCampaignListOptions(lists);
      }).catch(function () {
        if (listsMsg) {
          listsMsg.textContent = 'Unable to update list.';
        }
      });
    };

    var renderLists = function (lists) {
      if (!listBody) {
        return;
      }
      listBody.innerHTML = '';
      if (!lists.length) {
        listBody.innerHTML = '<tr><td colspan="5">No lists yet.</td></tr>';
        return;
      }
      lists.forEach(function (list) {
        var tr = document.createElement('tr');
        tr.setAttribute('data-marketing-list-id', String(list.id || 0));
        tr.innerHTML = '' +
          '<td>' + (list.name || '') + '</td>' +
          '<td>' + (list.type || 'dynamic') + '</td>' +
          '<td>' + String(list.member_count || 0) + '</td>' +
          '<td>' + (list.updated_at || '') + '</td>' +
          '<td class="cmn-marketing-list-actions">' +
            '<button type="button" class="cmn-ghost cmn-btn-mini" data-marketing-use-list="' + String(list.id || 0) + '">Use in campaign</button>' +
            '<button type="button" class="cmn-ghost cmn-btn-mini" data-marketing-refresh-list="' + String(list.id || 0) + '">Refresh</button>' +
            '<button type="button" class="cmn-ghost cmn-btn-mini" data-marketing-duplicate-list="' + String(list.id || 0) + '">Duplicate</button>' +
            '<button type="button" class="cmn-ghost cmn-btn-mini" data-marketing-delete-list="' + String(list.id || 0) + '">Delete</button>' +
          '</td>';
        listBody.appendChild(tr);
      });
      listBody.querySelectorAll('[data-marketing-use-list]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var listId = btn.getAttribute('data-marketing-use-list') || '';
          var select = marketingRoot.querySelector('[data-marketing-campaign="list_id"]');
          if (select) {
            select.value = listId;
          }
          setTab('campaigns');
        });
      });
      listBody.querySelectorAll('[data-marketing-refresh-list]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var listId = parseInt(btn.getAttribute('data-marketing-refresh-list') || '0', 10);
          listAction('cmn_marketing_refresh_list', listId);
        });
      });
      listBody.querySelectorAll('[data-marketing-duplicate-list]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var listId = parseInt(btn.getAttribute('data-marketing-duplicate-list') || '0', 10);
          listAction('cmn_marketing_duplicate_list', listId);
        });
      });
      listBody.querySelectorAll('[data-marketing-delete-list]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var listId = parseInt(btn.getAttribute('data-marketing-delete-list') || '0', 10);
          listAction('cmn_marketing_delete_list', listId, 'Delete this list? This cannot be undone.');
        });
      });
    };

    var refreshLists = function () {
      return mFetch('cmn_marketing_get_lists', {}).then(function (data) {
        if (data && data.success && data.data) {
          var lists = data.data.lists || [];
          renderLists(lists);
          syncCampaignListOptions(lists);
        }
      });
    };

    var renderCampaigns = function (campaigns) {
      if (!campaignsBody) {
        return;
      }
      campaignsBody.innerHTML = '';
      if (!campaigns.length) {
        campaignsBody.innerHTML = '<tr><td colspan="5">No campaigns yet.</td></tr>';
        return;
      }
      campaigns.forEach(function (campaign) {
        var tr = document.createElement('tr');
        tr.setAttribute('data-campaign-id', String(campaign.id || 0));
        var campaignStatus = campaign.status || 'draft';
        var actionButton = campaignStatus === 'paused'
          ? '<button type="button" class="cmn-ghost cmn-btn-mini" data-marketing-campaign-action="resume" data-marketing-campaign-id="' + String(campaign.id || 0) + '">Resume</button>'
          : '<button type="button" class="cmn-ghost cmn-btn-mini" data-marketing-campaign-action="pause" data-marketing-campaign-id="' + String(campaign.id || 0) + '">Pause</button>';
        tr.innerHTML = '' +
          '<td>' + (campaign.name || '') + '</td>' +
          '<td>' + campaignStatus + '</td>' +
          '<td>' + (campaign.subject || '') + '</td>' +
          '<td>' + (campaign.created_at || '') + '</td>' +
          '<td>' + actionButton + '</td>';
        tr.addEventListener('click', function () {
          marketingState.activeCampaignId = parseInt(campaign.id || '0', 10) || 0;
        });
        campaignsBody.appendChild(tr);
      });
      campaignsBody.querySelectorAll('[data-marketing-campaign-action]').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
          event.stopPropagation();
          var campaignId = parseInt(btn.getAttribute('data-marketing-campaign-id') || '0', 10);
          var action = btn.getAttribute('data-marketing-campaign-action') || '';
          if (!campaignId || (action !== 'pause' && action !== 'resume')) {
            return;
          }
          var ajaxAction = action === 'pause' ? 'cmn_marketing_pause_campaign' : 'cmn_marketing_resume_campaign';
          if (campaignMsg) {
            campaignMsg.textContent = action === 'pause' ? 'Pausing campaign...' : 'Resuming campaign...';
          }
          mFetch(ajaxAction, { campaign_id: campaignId }).then(function (data) {
            if (!data || !data.success || !data.data) {
              if (campaignMsg) {
                campaignMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to update campaign.';
              }
              return;
            }
            if (campaignMsg) {
              campaignMsg.textContent = data.data.message || 'Campaign updated.';
            }
            renderCampaigns(data.data.campaigns || []);
            if (data.data.queue) {
              renderQueue(data.data.queue);
            } else {
              refreshQueue();
            }
          }).catch(function () {
            if (campaignMsg) {
              campaignMsg.textContent = 'Unable to update campaign.';
            }
          });
        });
      });
    };

    var refreshCampaigns = function () {
      return mFetch('cmn_marketing_get_campaigns', {}).then(function (data) {
        if (data && data.success && data.data) {
          renderCampaigns(data.data.campaigns || []);
        }
      });
    };

    var renderQueue = function (queue) {
      if (!queueBody) {
        return;
      }
      if (queueSummary) {
        queueSummary.textContent = 'Queued: ' + String(queue.queued || 0) + ' · Sent: ' + String(queue.sent || 0) + ' · Failed: ' + String(queue.failed || 0);
      }
      queueBody.innerHTML = '';
      var rows = queue.rows || [];
      if (!rows.length) {
        queueBody.innerHTML = '<tr><td colspan="6">Queue is empty.</td></tr>';
        return;
      }
      rows.forEach(function (row) {
        var tr = document.createElement('tr');
        tr.innerHTML = '' +
          '<td>' + (row.campaign_name || '') + '</td>' +
          '<td>' + (row.school_name || '') + '</td>' +
          '<td>' + (row.to_email || '') + '</td>' +
          '<td>' + (row.status || 'queued') + '</td>' +
          '<td>' + (row.sent_at || '') + '</td>' +
          '<td>' + (row.failure_reason || '') + '</td>';
        queueBody.appendChild(tr);
      });
    };

    var refreshQueue = function () {
      return mFetch('cmn_marketing_get_queue', {}).then(function (data) {
        if (data && data.success && data.data && data.data.queue) {
          renderQueue(data.data.queue);
        }
      });
    };

    var renderReplies = function (payload) {
      if (!repliesBody) {
        return;
      }
      var rows = payload && payload.rows ? payload.rows : [];
      var counts = payload && payload.counts ? payload.counts : { total: 0, matched: 0, unmatched: 0 };
      if (repliesSummary) {
        repliesSummary.textContent = 'Replies: ' + String(counts.total || 0) + ' · Matched: ' + String(counts.matched || 0) + ' · Unmatched: ' + String(counts.unmatched || 0);
      }
      repliesBody.innerHTML = '';
      if (!rows.length) {
        repliesBody.innerHTML = '<tr><td colspan="6">No replies yet.</td></tr>';
        return;
      }
      rows.forEach(function (row) {
        var tr = document.createElement('tr');
        tr.innerHTML = '' +
          '<td>' + (row.received_at_label || row.received_at || '—') + '</td>' +
          '<td>' + (row.school_name || 'Unmatched') + '</td>' +
          '<td>' + (row.from_email || '—') + '</td>' +
          '<td>' + (row.subject || '—') + '</td>' +
          '<td>' + (row.snippet || '—') + '</td>' +
          '<td>' + (row.campaign_id ? ('#' + row.campaign_id) : '—') + '</td>';
        repliesBody.appendChild(tr);
      });
    };

    var refreshReplies = function () {
      return mFetch('cmn_marketing_get_replies', { limit: 120 }).then(function (data) {
        if (!data || !data.success || !data.data) {
          if (repliesSummary) {
            repliesSummary.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to load replies.';
          }
          renderReplies({ rows: [], counts: { total: 0, matched: 0, unmatched: 0 } });
          return;
        }
        renderReplies(data.data.replies || { rows: [], counts: { total: 0, matched: 0, unmatched: 0 } });
      }).catch(function () {
        if (repliesSummary) {
          repliesSummary.textContent = 'Unable to load replies.';
        }
      });
    };

    marketingRoot.querySelectorAll('[data-marketing-action]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var action = btn.getAttribute('data-marketing-action') || '';
        if (action === 'search') {
          refreshLeadRows();
          return;
        }
        if (action === 'save-dynamic') {
          var listName = window.prompt('List name');
          if (!listName) {
            return;
          }
          var payload = {
            name: listName,
            type: 'dynamic',
            criteria_json: JSON.stringify(readLeadFilters())
          };
          mFetch('cmn_marketing_save_list', payload).then(function (data) {
            if (!data || !data.success) {
              if (leadMsg) {
                leadMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to save list.';
              }
              return;
            }
            if (leadMsg) {
              leadMsg.textContent = data.data.message || 'List saved.';
            }
            var lists = (data.data && data.data.lists) || [];
            renderLists(lists);
            syncCampaignListOptions(lists);
          });
          return;
        }
        if (action === 'save-campaign') {
          var getCampaignField = function (key) {
            var el = marketingRoot.querySelector('[data-marketing-campaign="' + key + '"]');
            return el ? el.value : '';
          };
          var payloadCampaign = {
            campaign_id: marketingState.activeCampaignId || 0,
            name: getCampaignField('name'),
            from_context: getCampaignField('from_context'),
            subject: getCampaignField('subject'),
            html_body: getCampaignField('html_body'),
            text_body: ''
          };
          mFetch('cmn_marketing_save_campaign', payloadCampaign).then(function (data) {
            if (!data || !data.success) {
              if (campaignMsg) {
                campaignMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to save campaign.';
              }
              return;
            }
            marketingState.activeCampaignId = parseInt((data.data && data.data.campaign_id) || '0', 10) || marketingState.activeCampaignId;
            if (campaignMsg) {
              campaignMsg.textContent = data.data.message || 'Campaign saved.';
            }
            renderCampaigns((data.data && data.data.campaigns) || []);
          });
          return;
        }
        if (action === 'insert-tag') {
          var tagSelect = marketingRoot.querySelector('[data-marketing-tag-select]');
          var tagValue = tagSelect ? (tagSelect.value || '') : '';
          var bodyArea = marketingRoot.querySelector('[data-marketing-campaign="html_body"]');
          if (!tagValue || !bodyArea) {
            return;
          }
          var startPos = bodyArea.selectionStart || 0;
          var endPos = bodyArea.selectionEnd || 0;
          var current = bodyArea.value || '';
          bodyArea.value = current.slice(0, startPos) + tagValue + current.slice(endPos);
          bodyArea.focus();
          var nextPos = startPos + tagValue.length;
          bodyArea.setSelectionRange(nextPos, nextPos);
          return;
        }
        if (action === 'preview-campaign') {
          var previewSchoolSelect = marketingRoot.querySelector('[data-marketing-preview-school]');
          var previewSchoolId = previewSchoolSelect ? parseInt(previewSchoolSelect.value || '0', 10) : 0;
          var previewSubjectInput = marketingRoot.querySelector('[data-marketing-campaign="subject"]');
          var previewBodyInput = marketingRoot.querySelector('[data-marketing-campaign="html_body"]');
          if (!previewSchoolId) {
            if (campaignMsg) {
              campaignMsg.textContent = 'Choose a school to preview.';
            }
            return;
          }
          if (previewSubject) {
            previewSubject.textContent = 'Rendering preview...';
          }
          if (previewBody) {
            previewBody.textContent = '';
          }
          if (previewMissing) {
            previewMissing.textContent = '';
          }
          mFetch('cmn_marketing_preview_campaign', {
            school_id: previewSchoolId,
            subject: previewSubjectInput ? previewSubjectInput.value : '',
            html_body: previewBodyInput ? previewBodyInput.value : '',
            text_body: ''
          }).then(function (data) {
            if (!data || !data.success || !data.data) {
              if (campaignMsg) {
                campaignMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to render preview.';
              }
              return;
            }
            if (previewSubject) {
              previewSubject.textContent = data.data.subject || '(No subject)';
            }
            if (previewBody) {
              previewBody.innerHTML = data.data.body_html || '<em>No body content.</em>';
            }
            if (previewMissing) {
              var missing = data.data.missing_counts || {};
              var keys = Object.keys(missing);
              if (keys.length) {
                previewMissing.textContent = 'Missing values: ' + keys.map(function (key) {
                  return key + ' (' + missing[key] + ')';
                }).join(', ');
              } else {
                previewMissing.textContent = 'All merge fields resolved for this preview.';
              }
            }
          }).catch(function () {
            if (campaignMsg) {
              campaignMsg.textContent = 'Unable to render preview.';
            }
          });
          return;
        }
        if (action === 'queue-campaign') {
          var listSelect = marketingRoot.querySelector('[data-marketing-campaign="list_id"]');
          var listId = listSelect ? parseInt(listSelect.value || '0', 10) : 0;
          if (!marketingState.activeCampaignId) {
            if (campaignMsg) {
              campaignMsg.textContent = 'Save/select a campaign first.';
            }
            return;
          }
          if (!listId) {
            if (campaignMsg) {
              campaignMsg.textContent = 'Select a target list.';
            }
            return;
          }
          mFetch('cmn_marketing_queue_campaign', {
            campaign_id: marketingState.activeCampaignId,
            list_id: listId
          }).then(function (data) {
            if (!data || !data.success) {
              if (campaignMsg) {
                campaignMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to queue campaign.';
              }
              return;
            }
            if (campaignMsg) {
              campaignMsg.textContent = data.data.message || 'Campaign queued.';
            }
            if (data.data && data.data.queue) {
              renderQueue(data.data.queue);
            }
            setTab('queue');
          });
          return;
        }
        if (action === 'process-queue') {
          mFetch('cmn_marketing_process_queue', {
            campaign_id: marketingState.activeCampaignId || 0,
            limit: 30
          }).then(function (data) {
            if (!data || !data.success) {
              if (campaignMsg) {
                campaignMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to process queue.';
              }
              return;
            }
            if (campaignMsg) {
              campaignMsg.textContent = (data.data && data.data.message) ? data.data.message : 'Queue processed.';
            }
            if (data.data && data.data.queue) {
              renderQueue(data.data.queue);
            } else {
              refreshQueue();
            }
            setTab('queue');
          });
          return;
        }
        if (action === 'refresh-replies') {
          if (repliesSummary) {
            repliesSummary.textContent = 'Refreshing...';
          }
          refreshReplies();
          return;
        }
        if (action === 'poll-replies') {
          if (repliesSummary) {
            repliesSummary.textContent = 'Polling inbox...';
          }
          mFetch('cmn_marketing_poll_replies', { limit: 40 }).then(function (data) {
            if (!data || !data.success || !data.data) {
              if (repliesSummary) {
                repliesSummary.textContent = (data && data.data && data.data.message) ? data.data.message : 'Inbox poll failed.';
              }
              return;
            }
            if (repliesSummary && data.data.message) {
              repliesSummary.textContent = data.data.message;
            }
            renderReplies(data.data.replies || { rows: [], counts: { total: 0, matched: 0, unmatched: 0 } });
          }).catch(function () {
            if (repliesSummary) {
              repliesSummary.textContent = 'Inbox poll failed.';
            }
          });
        }
      });
    });

    var selectAll = marketingRoot.querySelector('[data-marketing-select-all]');
    if (selectAll) {
      selectAll.addEventListener('change', function () {
        var checked = !!selectAll.checked;
        resultBody.querySelectorAll('[data-marketing-row-select]').forEach(function (checkbox) {
          checkbox.checked = checked;
          var id = parseInt(checkbox.value || '0', 10);
          if (!id) {
            return;
          }
          if (checked) {
            marketingState.selectedIds[id] = 1;
          } else {
            delete marketingState.selectedIds[id];
          }
        });
      });
    }

    refreshLeadRows();
    refreshLists();
    refreshCampaigns();
    refreshQueue();
    refreshReplies();
  }

  var bookingChatRoots = document.querySelectorAll('[data-booking-chat]');
  if (bookingChatRoots.length && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.bookingChatNonce) {
    bookingChatRoots.forEach(function (chatRoot) {
      var threadId = parseInt(chatRoot.getAttribute('data-thread-id') || '0', 10);
      if (!threadId) {
        return;
      }
      var bookingId = parseInt(chatRoot.getAttribute('data-booking-id') || '0', 10);
      var feedbackRole = chatRoot.getAttribute('data-feedback-role') || '';
      var card = chatRoot.closest('.cmn-dashboard-card');
      var summaryEl = card ? card.querySelector('[data-booking-feedback-summary]') : null;
      var feedbackModal = null;
      if (card && card.nextElementSibling && card.nextElementSibling.hasAttribute('data-booking-feedback-modal')) {
        feedbackModal = card.nextElementSibling;
      } else if (card && card.parentElement) {
        feedbackModal = card.parentElement.querySelector('[data-booking-feedback-modal]');
      } else {
        feedbackModal = document.querySelector('[data-booking-feedback-modal]');
      }
      var feedbackForm = feedbackModal ? feedbackModal.querySelector('[data-booking-feedback-form]') : null;
      var feedbackSubtitle = feedbackModal ? feedbackModal.querySelector('[data-booking-feedback-subtitle]') : null;
      var feedbackMsg = feedbackModal ? feedbackModal.querySelector('[data-booking-feedback-msg]') : null;
      var feedbackTagsWrap = feedbackModal ? feedbackModal.querySelector('[data-booking-feedback-tags]') : null;
      var wouldRebookWrap = feedbackModal ? feedbackModal.querySelector('[data-booking-feedback-would-rebook]') : null;
      var forcePrompt = (new URLSearchParams(window.location.search).get('cmn_feedback_prompt') || '') === '1';
      var isFeedbackModalOpen = false;
      var feedbackSubmitInFlight = false;
      var currentThreadType = 'booking_details';

      var renderBookingChatMessages = function (messages) {
        if (!Array.isArray(messages)) {
          return;
        }
        chatRoot.innerHTML = '';
        if (!messages.length) {
          chatRoot.innerHTML = '<div class="cmn-empty">No messages yet.</div>';
          return;
        }
        messages.forEach(function (msg) {
          var senderRole = String(msg.sender_role_type || 'system').toLowerCase();
          var bubbleClass = 'is-system';
          if (senderRole === 'candidate' || senderRole === 'school') {
            bubbleClass = 'is-user is-' + senderRole;
          } else if (senderRole === 'account_manager' || senderRole === 'admin') {
            bubbleClass = 'is-admin is-' + senderRole;
          }
          var bubble = document.createElement('div');
          bubble.className = 'cmn-support-bubble cmn-booking-bubble ' + bubbleClass;
          var meta = document.createElement('div');
          meta.className = 'cmn-support-meta';
          var label = msg.sender_name || senderRole.replace('_', ' ');
          meta.textContent = label + ' · ' + (msg.created_at || '');
          var text = document.createElement('div');
          text.className = 'cmn-support-text';
          text.textContent = msg.message || '';
          bubble.appendChild(meta);
          bubble.appendChild(text);
          if (Array.isArray(msg.attachments) && msg.attachments.length) {
            var chipWrap = document.createElement('div');
            chipWrap.className = 'cmn-support-attachments';
            msg.attachments.forEach(function (attachment) {
              var chip = document.createElement('a');
              chip.className = 'cmn-support-attachment-chip';
              chip.href = attachment.url || '#';
              chip.target = '_blank';
              chip.rel = 'noopener noreferrer';
              chip.textContent = attachment.filename || 'Attachment';
              chipWrap.appendChild(chip);
            });
            bubble.appendChild(chipWrap);
          }
          chatRoot.appendChild(bubble);
        });
        chatRoot.scrollTop = chatRoot.scrollHeight;
      };

      var setBookingFeedbackModalOpen = function (open) {
        if (!feedbackModal) {
          return;
        }
        isFeedbackModalOpen = !!open;
        feedbackModal.hidden = !isFeedbackModalOpen;
        document.body.classList.toggle('cmn-booking-feedback-lock', isFeedbackModalOpen);
      };

      var updateBookingStarVisuals = function (picker, selectedValue, hoverValue) {
        if (!picker) {
          return;
        }
        picker.querySelectorAll('button[data-booking-star-value]').forEach(function (starBtn) {
          var starValue = parseInt(starBtn.getAttribute('data-booking-star-value') || '0', 10);
          starBtn.classList.toggle('is-hover', hoverValue > 0 && starValue <= hoverValue);
          starBtn.classList.toggle('is-active', hoverValue === 0 && selectedValue > 0 && starValue <= selectedValue);
        });
      };

      var initBookingStarPicker = function (fieldName) {
        if (!feedbackForm) {
          return;
        }
        var picker = feedbackForm.querySelector('[data-booking-feedback-stars="' + fieldName + '"]');
        var hiddenInput = feedbackForm.querySelector('input[name="' + fieldName + '"]');
        var scoreEl = feedbackForm.querySelector('[data-booking-feedback-score="' + fieldName + '"]');
        if (!picker || !hiddenInput) {
          return;
        }
        if (picker.childElementCount < 1) {
          for (var i = 1; i <= 5; i += 1) {
            var star = document.createElement('button');
            star.type = 'button';
            star.className = 'cmn-star-btn';
            star.setAttribute('data-booking-star-value', String(i));
            star.setAttribute('aria-label', i + ' stars');
            star.textContent = '★';
            picker.appendChild(star);
          }
        }
        var selectedValue = parseInt(hiddenInput.value || '0', 10);
        var hoverValue = 0;
        picker.querySelectorAll('button[data-booking-star-value]').forEach(function (starBtn) {
          starBtn.onmouseenter = function () {
            hoverValue = parseInt(starBtn.getAttribute('data-booking-star-value') || '0', 10);
            updateBookingStarVisuals(picker, selectedValue, hoverValue);
          };
          starBtn.onclick = function () {
            selectedValue = parseInt(starBtn.getAttribute('data-booking-star-value') || '0', 10);
            hiddenInput.value = String(selectedValue);
            if (scoreEl) {
              scoreEl.textContent = selectedValue + '/5';
            }
            hoverValue = 0;
            updateBookingStarVisuals(picker, selectedValue, hoverValue);
          };
        });
        picker.onmouseleave = function () {
          hoverValue = 0;
          updateBookingStarVisuals(picker, selectedValue, hoverValue);
        };
        if (scoreEl) {
          scoreEl.textContent = selectedValue > 0 ? selectedValue + '/5' : '0/5';
        }
        updateBookingStarVisuals(picker, selectedValue, hoverValue);
      };

      var resetBookingFeedbackForm = function () {
        if (!feedbackForm) {
          return;
        }
        feedbackForm.reset();
        ['stars_1', 'stars_2', 'stars_3', 'stars_overall'].forEach(function (fieldName) {
          var hiddenInput = feedbackForm.querySelector('input[name="' + fieldName + '"]');
          if (hiddenInput) {
            hiddenInput.value = '';
          }
          var scoreEl = feedbackForm.querySelector('[data-booking-feedback-score="' + fieldName + '"]');
          if (scoreEl) {
            scoreEl.textContent = '0/5';
          }
          var picker = feedbackForm.querySelector('[data-booking-feedback-stars="' + fieldName + '"]');
          updateBookingStarVisuals(picker, 0, 0);
        });
        feedbackForm.querySelectorAll('[data-booking-feedback-toggle-value]').forEach(function (btn) {
          btn.classList.remove('is-active');
        });
        var hiddenWould = feedbackForm.querySelector('input[name="would_rebook"]');
        if (hiddenWould) {
          hiddenWould.value = '';
        }
        if (feedbackMsg) {
          feedbackMsg.textContent = '';
        }
      };

      var renderFeedbackTags = function (tags) {
        if (!feedbackTagsWrap) {
          return;
        }
        feedbackTagsWrap.innerHTML = '';
        if (!Array.isArray(tags) || !tags.length) {
          return;
        }
        tags.forEach(function (tag) {
          var label = document.createElement('label');
          label.className = 'cmn-inline-check';
          var input = document.createElement('input');
          input.type = 'checkbox';
          input.name = 'tags[]';
          input.value = tag;
          label.appendChild(input);
          label.appendChild(document.createTextNode(' ' + tag));
          feedbackTagsWrap.appendChild(label);
        });
      };

      var renderBookingFeedbackSummary = function (payload) {
        if (!summaryEl) {
          return;
        }
        summaryEl.innerHTML = '';
        if (!payload || currentThreadType !== 'booking_details') {
          return;
        }
        var hasSummary = false;
        if (payload.viewer_feedback) {
          var own = payload.viewer_feedback;
          var summary = document.createElement('div');
          summary.className = 'cmn-support-feedback-summary';
          summary.innerHTML = '<strong>Feedback submitted</strong><span>Overall: ' + (own.stars_overall || 0) + '/5</span><span>Tags: ' + (Array.isArray(own.tags) && own.tags.length ? own.tags.join(', ') : 'None') + '</span>';
          if (own.comment) {
            var ownComment = document.createElement('p');
            ownComment.textContent = own.comment;
            summary.appendChild(ownComment);
          }
          if (feedbackRole === 'school') {
            var cta = document.createElement('a');
            cta.className = 'cmn-ghost cmn-btn-mini';
            cta.href = '?school=cover';
            cta.textContent = 'Book again';
            summary.appendChild(cta);
          }
          summaryEl.appendChild(summary);
          hasSummary = true;
        }

        if (payload.counterparty_feedback) {
          var other = payload.counterparty_feedback;
          var received = document.createElement('div');
          received.className = 'cmn-support-feedback-summary';
          received.innerHTML = '<strong>Feedback received</strong><span>Overall: ' + (other.stars_overall || 0) + '/5</span><span>Tags: ' + (Array.isArray(other.tags) && other.tags.length ? other.tags.join(', ') : 'None') + '</span>';
          summaryEl.appendChild(received);
          hasSummary = true;
        }

        if (payload.requires_feedback && !hasSummary) {
          var pending = document.createElement('div');
          pending.className = 'cmn-support-feedback-summary';
          pending.innerHTML = '<strong>Feedback pending</strong><span>Please submit your booking feedback.</span>';
          var leaveBtn = document.createElement('button');
          leaveBtn.type = 'button';
          leaveBtn.className = 'cmn-ghost cmn-btn-mini';
          leaveBtn.textContent = 'Leave feedback';
          leaveBtn.addEventListener('click', function () {
            setBookingFeedbackModalOpen(true);
          });
          pending.appendChild(leaveBtn);
          summaryEl.appendChild(pending);
        }
      };

      var applyBookingFeedbackConfig = function (payload) {
        if (!feedbackForm || !payload || !payload.form) {
          return;
        }
        var formConfig = payload.form || {};
        var labels = formConfig.star_labels || {};
        ['stars_1', 'stars_2', 'stars_3', 'stars_overall'].forEach(function (fieldName) {
          var labelEl = feedbackForm.querySelector('[data-booking-feedback-label="' + fieldName + '"]');
          if (labelEl && labels[fieldName]) {
            labelEl.textContent = labels[fieldName];
          }
          initBookingStarPicker(fieldName);
        });
        var showWouldRebook = !!formConfig.show_would_rebook;
        if (wouldRebookWrap) {
          wouldRebookWrap.hidden = !showWouldRebook;
        }
        renderFeedbackTags(formConfig.tags || []);
        if (feedbackSubtitle) {
          feedbackSubtitle.textContent = payload.viewer_role === 'candidate'
            ? 'Rate your experience with ' + (payload.school_name || 'the school') + '.'
            : 'Rate candidate ' + (payload.candidate_name || '') + '.';
        }
      };

      var fetchBookingFeedback = function () {
        if (!bookingId || !window.cmnPortal.bookingFeedbackNonce || !feedbackRole || currentThreadType !== 'booking_details') {
          setBookingFeedbackModalOpen(false);
          if (summaryEl) {
            summaryEl.innerHTML = '';
          }
          return Promise.resolve();
        }
        var fd = new FormData();
        fd.append('action', 'cmn_booking_feedback_fetch');
        fd.append('nonce', window.cmnPortal.bookingFeedbackNonce || '');
        fd.append('booking_id', String(bookingId));
        return fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
        }).then(function (response) {
          return response.json();
        }).then(function (data) {
          if (!data || !data.success || !data.data) {
            return;
          }
          var payload = data.data;
          applyBookingFeedbackConfig(payload);
          renderBookingFeedbackSummary(payload);
          if (feedbackForm) {
            var bookingInput = feedbackForm.querySelector('input[name="booking_id"]');
            if (bookingInput) {
              bookingInput.value = String(payload.booking_id || bookingId || 0);
            }
          }
          if (payload.requires_feedback) {
            if ((!isFeedbackModalOpen && !feedbackSubmitInFlight) || forcePrompt) {
              resetBookingFeedbackForm();
            }
            setBookingFeedbackModalOpen(true);
          } else {
            setBookingFeedbackModalOpen(false);
          }
        }).catch(function () {
          return null;
        });
      };

      var fetchBookingChat = function () {
        var fd = new FormData();
        fd.append('action', 'cmn_booking_chat_fetch');
        fd.append('nonce', window.cmnPortal.bookingChatNonce || '');
        fd.append('thread_id', String(threadId));
        return fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
        }).then(function (response) {
          return response.json();
        }).then(function (data) {
          if (!data || !data.success || !data.data) {
            return;
          }
          if (data.data.thread) {
            bookingId = parseInt(data.data.thread.booking_id || bookingId || 0, 10);
            currentThreadType = data.data.thread.thread_type || 'booking_details';
          }
          renderBookingChatMessages(data.data.messages || []);
          return fetchBookingFeedback();
        }).catch(function () {
          return null;
        });
      };

      if (feedbackForm) {
        feedbackForm.querySelectorAll('[data-booking-feedback-toggle-value]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            feedbackForm.querySelectorAll('[data-booking-feedback-toggle-value]').forEach(function (other) {
              other.classList.remove('is-active');
            });
            btn.classList.add('is-active');
            var hiddenInput = feedbackForm.querySelector('input[name="would_rebook"]');
            if (hiddenInput) {
              hiddenInput.value = btn.getAttribute('data-booking-feedback-toggle-value') || '';
            }
          });
        });

        feedbackForm.addEventListener('submit', function (event) {
          event.preventDefault();
          if (!bookingId || feedbackSubmitInFlight) {
            return;
          }
          var requiredStars = ['stars_1', 'stars_2', 'stars_3', 'stars_overall'];
          for (var i = 0; i < requiredStars.length; i += 1) {
            var field = requiredStars[i];
            var value = parseInt((feedbackForm.querySelector('input[name="' + field + '"]') || {}).value || '0', 10);
            if (value < 1) {
              if (feedbackMsg) {
                feedbackMsg.textContent = 'Please complete all star ratings.';
              }
              return;
            }
          }
          if (wouldRebookWrap && !wouldRebookWrap.hidden) {
            var wouldValue = (feedbackForm.querySelector('input[name="would_rebook"]') || {}).value || '';
            if (wouldValue !== '1' && wouldValue !== '0') {
              if (feedbackMsg) {
                feedbackMsg.textContent = 'Please confirm whether you would work here again.';
              }
              return;
            }
          }
          var fd = new FormData(feedbackForm);
          fd.append('action', 'cmn_booking_feedback_submit');
          fd.append('nonce', window.cmnPortal.bookingFeedbackNonce || '');
          fd.set('booking_id', String(bookingId));
          feedbackSubmitInFlight = true;
          if (feedbackMsg) {
            feedbackMsg.textContent = 'Submitting...';
          }
          fetch(window.cmnPortal.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: fd,
          }).then(function (response) {
            return response.json();
          }).then(function (data) {
            if (!data || !data.success) {
              if (feedbackMsg) {
                feedbackMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to submit feedback.';
              }
              feedbackSubmitInFlight = false;
              return;
            }
            if (feedbackMsg) {
              feedbackMsg.textContent = '';
            }
            var payload = data.data && data.data.feedback ? data.data.feedback : null;
            applyBookingFeedbackConfig(payload);
            renderBookingFeedbackSummary(payload);
            setBookingFeedbackModalOpen(false);
            feedbackSubmitInFlight = false;
            forcePrompt = false;
            fetchBookingChat();
          }).catch(function () {
            if (feedbackMsg) {
              feedbackMsg.textContent = 'Unable to submit feedback.';
            }
            feedbackSubmitInFlight = false;
          });
        });
      }

      fetchBookingChat();
      window.setInterval(function () {
        if (document.hidden) {
          return;
        }
        fetchBookingChat();
      }, 5000);
    });
  }

  var planner = document.querySelector('[data-candidate-calendar]');
  if (planner && window.cmnPortal && window.cmnPortal.ajaxUrl) {
    var grid = planner.querySelector('[data-calendar-grid]');
    var label = planner.querySelector('[data-calendar-label]');
    var feedback = planner.querySelector('[data-calendar-feedback]');
    var prevBtn = planner.querySelector('[data-calendar-prev]');
    var nextBtn = planner.querySelector('[data-calendar-next]');
    var rangeStart = planner.querySelector('[data-calendar-range-start]');
    var rangeEnd = planner.querySelector('[data-calendar-range-end]');
    var bulkButtons = planner.querySelectorAll('[data-calendar-bulk]');
    var clearButtons = planner.querySelectorAll('[data-calendar-clear]');
    var summaryNext = document.querySelector('[data-summary-next-date]');
    var summaryAvailable = document.querySelector('[data-summary-available]');
    var summaryUnavailable = document.querySelector('[data-summary-unavailable]');
    var data = {};
    try {
      data = JSON.parse(planner.getAttribute('data-calendar-data') || '{}');
    } catch (e) {
      data = {};
    }
    var currentMonth = planner.getAttribute('data-calendar-month') || '';
    var minMonth = planner.getAttribute('data-calendar-min') || currentMonth;
    var maxMonth = planner.getAttribute('data-calendar-max') || currentMonth;
    var now = new Date();
    var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    var isoDate = function (dateObj) {
      return dateObj.getFullYear() + '-' + String(dateObj.getMonth() + 1).padStart(2, '0') + '-' + String(dateObj.getDate()).padStart(2, '0');
    };
    var todayStr = isoDate(today);
    var limitDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    limitDate.setDate(limitDate.getDate() + 30);
    var limitStr = isoDate(limitDate);

    var monthToLabel = function (ym) {
      var parts = ym.split('-');
      if (parts.length !== 2) {
        return ym;
      }
      var year = parseInt(parts[0], 10);
      var month = parseInt(parts[1], 10) - 1;
      var date = new Date(year, month, 1);
      return date.toLocaleString(undefined, { month: 'long', year: 'numeric' });
    };

    var shiftMonth = function (ym, delta) {
      var parts = ym.split('-');
      if (parts.length !== 2) {
        return ym;
      }
      var year = parseInt(parts[0], 10);
      var month = parseInt(parts[1], 10) - 1;
      var date = new Date(year, month + delta, 1);
      return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
    };

    var inRange = function (ym) {
      if (minMonth && ym < minMonth) {
        return false;
      }
      if (maxMonth && ym > maxMonth) {
        return false;
      }
      return true;
    };

    var setFeedback = function (text, timeout) {
      if (!feedback) {
        return;
      }
      feedback.textContent = text || '';
      if (timeout) {
        window.setTimeout(function () {
          feedback.textContent = '';
        }, timeout);
      }
    };

    var updateSummary = function (summary) {
      if (!summary) {
        return;
      }
      if (summaryNext) {
        summaryNext.textContent = summary.next_available_label || 'Not set';
      }
      if (summaryAvailable) {
        summaryAvailable.textContent = String(summary.available_count || 0);
      }
      if (summaryUnavailable) {
        summaryUnavailable.textContent = String(summary.unavailable_count || 0);
      }
    };

    var replaceCalendarData = function (calendarMap) {
      if (!calendarMap || typeof calendarMap !== 'object') {
        return;
      }
      data = calendarMap;
      renderMonth();
    };

    var requestCalendar = function (payload, onSuccess) {
      fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: payload,
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (res) {
          if (!res || !res.success) {
            setFeedback(res && res.data && res.data.message ? res.data.message : 'Unable to save.');
            return;
          }
          if (onSuccess) {
            onSuccess(res.data || {});
          }
        })
        .catch(function () {
          setFeedback('Unable to save.');
        });
    };

    var renderMonth = function () {
      if (!grid) {
        return;
      }
      grid.innerHTML = '';
      var parts = currentMonth.split('-');
      if (parts.length !== 2) {
        return;
      }
      var year = parseInt(parts[0], 10);
      var monthIndex = parseInt(parts[1], 10) - 1;
      if (label) {
        label.textContent = monthToLabel(currentMonth);
      }
      ['S', 'M', 'T', 'W', 'T', 'F', 'S'].forEach(function (day) {
        var cell = document.createElement('div');
        cell.className = 'cmn-calendar-day';
        cell.textContent = day;
        grid.appendChild(cell);
      });
      var firstDay = new Date(year, monthIndex, 1);
      var startWeekday = firstDay.getDay();
      for (var i = 0; i < startWeekday; i++) {
        var empty = document.createElement('div');
        empty.className = 'cmn-calendar-cell is-empty';
        grid.appendChild(empty);
      }
      var daysInMonth = new Date(year, monthIndex + 1, 0).getDate();
      for (var d = 1; d <= daysInMonth; d++) {
        var dateObj = new Date(year, monthIndex, d);
        var dateStr = isoDate(dateObj);
        var status = data[dateStr] || '';
        var day = dateObj.getDay();
        var isWeekend = day === 0 || day === 6;
        var isPast = dateStr < todayStr;
        var isBeyondLimit = dateStr > limitStr;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'cmn-calendar-cell';
        if (status === 'available') {
          btn.classList.add('is-available');
        } else if (status === 'unavailable') {
          btn.classList.add('is-unavailable');
        }
        if (isWeekend) {
          btn.classList.add('is-weekend');
        }
        if (isPast || isWeekend || isBeyondLimit) {
          btn.classList.add('is-disabled');
          btn.disabled = true;
        }
        btn.setAttribute('data-date', dateStr);
        var span = document.createElement('span');
        span.textContent = d;
        btn.appendChild(span);
        grid.appendChild(btn);
      }
    };

    var saveStatus = function (dateStr, status) {
      var formData = new FormData();
      formData.append('action', 'cmn_update_calendar_day');
      formData.append('nonce', window.cmnPortal.calendarNonce || '');
      formData.append('date', dateStr);
      formData.append('status', status);
      setFeedback('Saving...');
      requestCalendar(formData, function (payload) {
        updateSummary(payload.summary);
        setFeedback('Saved', 1400);
      });
    };

    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        var nextMonth = shiftMonth(currentMonth, -1);
        if (inRange(nextMonth)) {
          currentMonth = nextMonth;
          renderMonth();
        }
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        var nextMonth = shiftMonth(currentMonth, 1);
        if (inRange(nextMonth)) {
          currentMonth = nextMonth;
          renderMonth();
        }
      });
    }

    planner.addEventListener('click', function (event) {
      var target = event.target;
      if (!target) {
        return;
      }
      var cell = target.closest('.cmn-calendar-cell');
      if (!cell || cell.classList.contains('is-empty') || cell.classList.contains('is-disabled')) {
        return;
      }
      var dateStr = cell.getAttribute('data-date');
      if (!dateStr) {
        return;
      }
      var current = data[dateStr] || '';
      var next = current === '' ? 'available' : current === 'available' ? 'unavailable' : '';
      if (next) {
        data[dateStr] = next;
      } else {
        delete data[dateStr];
      }
      cell.classList.remove('is-available', 'is-unavailable');
      if (next === 'available') {
        cell.classList.add('is-available');
      } else if (next === 'unavailable') {
        cell.classList.add('is-unavailable');
      }
      saveStatus(dateStr, next || 'neutral');
    });

    var getRange = function () {
      var start = rangeStart ? rangeStart.value : '';
      var end = rangeEnd ? rangeEnd.value : '';
      if (!start || !end) {
        return null;
      }
      if (end < start) {
        return null;
      }
      return { start: start, end: end };
    };

    if (bulkButtons.length) {
      bulkButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
          var range = getRange();
          var status = btn.getAttribute('data-calendar-bulk') || '';
          if (!range) {
            setFeedback('Select a valid start and end date.');
            return;
          }
          var formData = new FormData();
          formData.append('action', 'cmn_bulk_update_calendar');
          formData.append('nonce', window.cmnPortal.calendarBulkNonce || '');
          formData.append('start_date', range.start);
          formData.append('end_date', range.end);
          formData.append('status', status);
          setFeedback('Saving...');
          requestCalendar(formData, function (payload) {
            replaceCalendarData(payload.calendar || {});
            updateSummary(payload.summary);
            setFeedback(payload.message || 'Saved', 1500);
          });
        });
      });
    }

    if (clearButtons.length) {
      clearButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
          var mode = btn.getAttribute('data-calendar-clear') || 'next30';
          var formData = new FormData();
          formData.append('action', 'cmn_clear_calendar');
          formData.append('nonce', window.cmnPortal.calendarClearNonce || '');
          formData.append('mode', mode === 'range' ? 'range' : 'next30');
          if (mode === 'range') {
            var range = getRange();
            if (!range) {
              setFeedback('Select a valid date range to clear.');
              return;
            }
            formData.append('start_date', range.start);
            formData.append('end_date', range.end);
          }
          setFeedback('Clearing...');
          requestCalendar(formData, function (payload) {
            replaceCalendarData(payload.calendar || {});
            updateSummary(payload.summary);
            setFeedback(payload.message || 'Cleared', 1500);
          });
        });
      });
    }

    renderMonth();
  }

  var candidateSettingsRoot = document.querySelector('[data-candidate-settings]');
  if (candidateSettingsRoot && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.candidateSettingsNonce) {
    var themeSelect = candidateSettingsRoot.querySelector('[data-settings-theme]');
    var prefInputs = candidateSettingsRoot.querySelectorAll('[data-settings-pref]');
    var saveBtn = candidateSettingsRoot.querySelector('[data-settings-save]');
    var msgEl = candidateSettingsRoot.querySelector('[data-settings-message]');
    var deleteEmailInput = candidateSettingsRoot.querySelector('[data-delete-confirm-email]');
    var deleteRequestBtn = candidateSettingsRoot.querySelector('[data-delete-request-btn]');
    var deleteRequestMsg = candidateSettingsRoot.querySelector('[data-delete-request-msg]');
    var settingsFetch = function (action, extraPayload) {
      var formData = new FormData();
      formData.append('action', action);
      formData.append('nonce', window.cmnPortal.candidateSettingsNonce);
      Object.keys(extraPayload || {}).forEach(function (key) {
        formData.append(key, extraPayload[key]);
      });
      return fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
      }).then(function (response) { return response.json(); });
    };

    var collectPrefs = function () {
      var prefs = {};
      prefInputs.forEach(function (input) {
        var key = input.getAttribute('data-settings-pref');
        if (!key) {
          return;
        }
        prefs[key] = input.checked ? '1' : '0';
      });
      return prefs;
    };

    var saveSettings = function (silent) {
      var payload = {
        theme: themeSelect ? themeSelect.value : 'default',
        preferences: JSON.stringify(collectPrefs()),
      };
      var fd = new FormData();
      fd.append('action', 'cmn_save_candidate_settings');
      fd.append('nonce', window.cmnPortal.candidateSettingsNonce);
      fd.append('theme', payload.theme);
      var prefs = collectPrefs();
      Object.keys(prefs).forEach(function (key) {
        fd.append('preferences[' + key + ']', prefs[key]);
      });
      if (msgEl && !silent) {
        msgEl.textContent = 'Saving...';
      }
      return fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd,
      }).then(function (response) { return response.json(); }).then(function (data) {
        if (!data || !data.success) {
          if (msgEl) {
            msgEl.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to save settings.';
          }
          return;
        }
        cmnApplyThemeClass((data.data && data.data.theme) ? data.data.theme : payload.theme);
        if (msgEl && !silent) {
          msgEl.textContent = 'Settings saved.';
          setTimeout(function () {
            msgEl.textContent = '';
          }, 1500);
        }
      }).catch(function () {
        if (msgEl && !silent) {
          msgEl.textContent = 'Unable to save settings.';
        }
      });
    };

    settingsFetch('cmn_get_candidate_settings').then(function (data) {
      if (!data || !data.success || !data.data) {
        return;
      }
      var settings = data.data;
      if (themeSelect && settings.theme) {
        themeSelect.value = settings.theme;
      }
      cmnApplyThemeClass(settings.theme || 'default');
      var preferences = settings.preferences || {};
      prefInputs.forEach(function (input) {
        var key = input.getAttribute('data-settings-pref');
        if (!key) {
          return;
        }
        input.checked = String(preferences[key] || 0) === '1';
      });
      if (settings.deletion_requested && deleteRequestBtn) {
        deleteRequestBtn.disabled = true;
      }
      if (settings.deletion_requested && deleteEmailInput) {
        deleteEmailInput.disabled = true;
      }
      if (settings.deletion_requested && deleteRequestMsg) {
        deleteRequestMsg.textContent = 'Pending admin action.';
      }
    });

    if (themeSelect) {
      themeSelect.addEventListener('change', function () {
        cmnApplyThemeClass(themeSelect.value);
        saveSettings(true);
      });
    }

    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        saveSettings(false);
      });
    }

    if (deleteRequestBtn) {
      deleteRequestBtn.addEventListener('click', function () {
        if (deleteRequestBtn.disabled) {
          return;
        }
        var typedEmail = deleteEmailInput ? deleteEmailInput.value.trim() : '';
        if (!typedEmail) {
          if (deleteRequestMsg) {
            deleteRequestMsg.textContent = 'Type your email to confirm.';
          }
          return;
        }
        var fd = new FormData();
        fd.append('action', 'cmn_candidate_request_delete_account');
        fd.append('nonce', window.cmnPortal.candidateSettingsNonce);
        fd.append('typed_email', typedEmail);
        if (deleteRequestMsg) {
          deleteRequestMsg.textContent = 'Submitting...';
        }
        fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
        }).then(function (response) {
          return response.json();
        }).then(function (data) {
          if (!data || !data.success) {
            if (deleteRequestMsg) {
              deleteRequestMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Could not submit request.';
            }
            return;
          }
          if (deleteRequestMsg) {
            deleteRequestMsg.textContent = (data.data && data.data.message) ? data.data.message : 'Request sent to admin.';
          }
          deleteRequestBtn.disabled = true;
          if (deleteEmailInput) {
            deleteEmailInput.disabled = true;
          }
        }).catch(function () {
          if (deleteRequestMsg) {
            deleteRequestMsg.textContent = 'Could not submit request.';
          }
        });
      });
    }
  }

  var genericThemeRoots = document.querySelectorAll('[data-theme-settings]');
  if (genericThemeRoots.length && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.themeSettingsNonce) {
    genericThemeRoots.forEach(function (themeRoot) {
      if (candidateSettingsRoot && candidateSettingsRoot.contains(themeRoot)) {
        return;
      }
      var selectEl = themeRoot.querySelector('[data-theme-select]');
      var saveEl = themeRoot.querySelector('[data-theme-save]');
      var msgEl = themeRoot.querySelector('[data-theme-message]');
      if (!selectEl || !saveEl) {
        return;
      }

      var fetchTheme = function () {
        var fd = new FormData();
        fd.append('action', 'cmn_get_theme_settings');
        fd.append('nonce', window.cmnPortal.themeSettingsNonce);
        return fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
        }).then(function (response) { return response.json(); });
      };

      var saveTheme = function () {
        var fd = new FormData();
        fd.append('action', 'cmn_save_theme_settings');
        fd.append('nonce', window.cmnPortal.themeSettingsNonce);
        fd.append('theme', selectEl.value || 'default');
        if (msgEl) {
          msgEl.textContent = 'Saving...';
        }
        return fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
        }).then(function (response) { return response.json(); }).then(function (data) {
          if (!data || !data.success) {
            if (msgEl) {
              msgEl.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to save scheme.';
            }
            return;
          }
          var chosenTheme = data.data && data.data.theme ? data.data.theme : selectEl.value;
          selectEl.value = chosenTheme;
          cmnApplyThemeClass(chosenTheme);
          if (msgEl) {
            msgEl.textContent = 'Saved.';
            setTimeout(function () { msgEl.textContent = ''; }, 1600);
          }
        }).catch(function () {
          if (msgEl) {
            msgEl.textContent = 'Unable to save scheme.';
          }
        });
      };

      fetchTheme().then(function (data) {
        if (!data || !data.success || !data.data) {
          return;
        }
        var theme = data.data.theme || 'default';
        selectEl.value = theme;
        cmnApplyThemeClass(theme);
      });

      selectEl.addEventListener('change', function () {
        cmnApplyThemeClass(selectEl.value || 'default');
      });
      saveEl.addEventListener('click', function () {
        saveTheme();
      });
    });
  }

  if (window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.staffNonce) {
    document.querySelectorAll('[data-candidate-delete-btn]').forEach(function (button) {
      button.addEventListener('click', function () {
        var candidateId = button.getAttribute('data-candidate-id');
        if (!candidateId) {
          return;
        }
        var candidateName = button.getAttribute('data-candidate-name') || 'this candidate';
        var confirmText = window.prompt('Type DELETE to permanently delete ' + candidateName + ':');
        if (confirmText === null) {
          return;
        }
        var fd = new FormData();
        fd.append('action', 'cmn_admin_delete_candidate_account');
        fd.append('nonce', window.cmnPortal.staffNonce);
        fd.append('candidate_id', candidateId);
        fd.append('confirm_text', confirmText);
        button.disabled = true;
        fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
        }).then(function (response) {
          return response.json();
        }).then(function (data) {
          if (!data || !data.success) {
            alert((data && data.data && data.data.message) ? data.data.message : 'Unable to delete candidate.');
            button.disabled = false;
            return;
          }
          var row = button.closest('tr');
          if (row) {
            row.remove();
          }
        }).catch(function () {
          alert('Unable to delete candidate.');
          button.disabled = false;
        });
      });
    });

    var requestCvConverterToken = function (candidateId) {
      var fd = new FormData();
      fd.append('action', 'cmn_generate_cv_converter_token');
      fd.append('nonce', window.cmnPortal.staffNonce);
      fd.append('candidate_id', candidateId);
      return fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      }).then(function (response) {
        return response.json();
      });
    };
    var setCvRowMessage = function (candidateId, message, isError) {
      var msg = document.querySelector('[data-cv-row-message="' + candidateId + '"]');
      if (!msg) {
        return;
      }
      msg.textContent = message || '';
      msg.style.color = isError ? '#b00020' : '';
    };
    document.querySelectorAll('[data-open-cv-converter]').forEach(function (button) {
      button.addEventListener('click', function () {
        var candidateId = button.getAttribute('data-open-cv-converter');
        if (!candidateId) {
          return;
        }
        button.disabled = true;
        setCvRowMessage(candidateId, 'Opening converter...', false);
        requestCvConverterToken(candidateId).then(function (data) {
          if (!data || !data.success || !data.data) {
            setCvRowMessage(candidateId, (data && data.data && data.data.message) ? data.data.message : 'Unable to open converter.', true);
            button.disabled = false;
            return;
          }
          var targetUrl = data.data.portal_url || data.data.converter_url || '';
          if (!targetUrl) {
            setCvRowMessage(candidateId, 'Unable to open converter.', true);
            button.disabled = false;
            return;
          }
          window.location.href = targetUrl;
          setCvRowMessage(candidateId, '', false);
          button.disabled = false;
        }).catch(function () {
          setCvRowMessage(candidateId, 'Unable to open converter.', true);
          button.disabled = false;
        });
      });
    });
    document.querySelectorAll('[data-download-original-cv]').forEach(function (button) {
      button.addEventListener('click', function () {
        var candidateId = button.getAttribute('data-download-original-cv');
        if (!candidateId) {
          return;
        }
        button.disabled = true;
        setCvRowMessage(candidateId, 'Preparing download...', false);
        requestCvConverterToken(candidateId).then(function (data) {
          if (!data || !data.success || !data.data || !data.data.original_cv_url) {
            setCvRowMessage(candidateId, (data && data.data && data.data.message) ? data.data.message : 'Unable to download original CV.', true);
            button.disabled = false;
            return;
          }
          window.open(data.data.original_cv_url, '_blank', 'noopener,noreferrer');
          setCvRowMessage(candidateId, '', false);
          button.disabled = false;
        }).catch(function () {
          setCvRowMessage(candidateId, 'Unable to download original CV.', true);
          button.disabled = false;
        });
      });
    });
  }

  var converterFrame = document.querySelector('[data-cmn-converter-frame]');
  var converterFallback = document.querySelector('[data-cmn-converter-fallback]');
  if (converterFrame && converterFallback) {
    var converterLoaded = false;
    var showConverterFallback = function () {
      if (converterLoaded) {
        return;
      }
      converterFallback.hidden = false;
    };
    converterFrame.addEventListener('load', function () {
      converterLoaded = true;
      converterFallback.hidden = true;
    });
    converterFrame.addEventListener('error', showConverterFallback);
    window.setTimeout(showConverterFallback, 9000);
  }

  var candidateDocsRoot = document.querySelector('[data-candidate-docs]');
  var candidateProfileRoot = document.querySelector('[data-profile-root]');
  if (candidateProfileRoot && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.candidateProfileNonce) {
    var setProfileMessage = function (text) {
      var msg = document.querySelector('[data-doc-message]');
      if (msg) {
        msg.textContent = text || '';
      }
    };
    var toggleEdit = function (section, editing) {
      var view = document.querySelector('[data-profile-view="' + section + '"]');
      var form = document.querySelector('[data-profile-form="' + section + '"]');
      if (view) {
        view.hidden = !!editing;
      }
      if (form) {
        form.hidden = !editing;
      }
    };
    document.querySelectorAll('[data-profile-edit]').forEach(function (button) {
      button.addEventListener('click', function () {
        var section = button.getAttribute('data-profile-edit');
        if (!section) {
          return;
        }
        toggleEdit(section, true);
      });
    });
    document.querySelectorAll('[data-profile-cancel]').forEach(function (button) {
      button.addEventListener('click', function () {
        var section = button.getAttribute('data-profile-cancel');
        if (!section) {
          return;
        }
        toggleEdit(section, false);
      });
    });
    var saveProfileSection = function (sectionForm) {
      var fd = new FormData();
      fd.append('action', 'cmn_candidate_update_profile');
      fd.append('nonce', window.cmnPortal.candidateProfileNonce);
      var getFieldInput = function (field) {
        var fromPersonal = document.querySelector('[data-profile-form="personal"] [name="' + field + '"]');
        if (fromPersonal) {
          return fromPersonal;
        }
        return document.querySelector('[data-profile-form="role"] [name="' + field + '"]');
      };
      ['email', 'first_name', 'last_name', 'phone', 'role_type', 'roles_other', 'travel_radius', 'location', 'driving_licence', 'car_owner', 'no_dbs', 'dbs_update_service', 'house_number', 'address_line1', 'address_line2', 'address_line3', 'town', 'county', 'postcode', 'notes'].forEach(function (field) {
        var input = sectionForm.querySelector('[name="' + field + '"]') || getFieldInput(field);
        if (input) {
          fd.append(field, input.value.trim());
        }
      });
      var roleInputs = document.querySelectorAll('[data-profile-form="role"] input[name="roles[]"]:checked');
      roleInputs.forEach(function (input) {
        fd.append('roles[]', input.value);
      });
      var dayInputs = document.querySelectorAll('[data-profile-form="role"] input[name="availability_days[]"]:checked');
      dayInputs.forEach(function (input) {
        fd.append('availability_days[]', input.value);
      });
      setProfileMessage('Saving...');
      fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      }).then(function (response) {
        return response.json();
      }).then(function (data) {
        if (!data || !data.success) {
          setProfileMessage(data && data.data && data.data.message ? data.data.message : 'Unable to save profile.');
          return;
        }
        var profile = data.data && data.data.profile ? data.data.profile : {};
        var fullName = (profile.full_name || '').trim();
        var fullNameEl = document.querySelector('[data-profile-full-name]');
        var emailEl = document.querySelector('[data-profile-email]');
        var phoneEl = document.querySelector('[data-profile-phone]');
        var roleEl = document.querySelector('[data-profile-role]');
        var rolesEl = document.querySelector('[data-profile-roles]');
        var rolesOtherEl = document.querySelector('[data-profile-roles-other]');
        var travelEl = document.querySelector('[data-profile-travel]');
        var locationEl = document.querySelector('[data-profile-location]');
        var drivingEl = document.querySelector('[data-profile-driving]');
        var carEl = document.querySelector('[data-profile-car]');
        var hasDbsEl = document.querySelector('[data-profile-has-dbs]');
        var dbsUpdateEl = document.querySelector('[data-profile-dbs-update]');
        var daysEl = document.querySelector('[data-profile-days]');
        var addressEl = document.querySelector('[data-profile-address]');
        if (fullNameEl) {
          fullNameEl.textContent = fullName || 'Candidate';
        }
        if (emailEl) {
          emailEl.textContent = profile.email || 'Not set';
        }
        if (phoneEl) {
          phoneEl.textContent = profile.phone || 'Not set';
        }
        if (roleEl) {
          roleEl.textContent = profile.role_type || 'Not set';
        }
        if (rolesEl) {
          rolesEl.textContent = profile.roles_label || 'Not set';
        }
        if (rolesOtherEl) {
          rolesOtherEl.textContent = profile.roles_other || 'Not set';
        }
        if (travelEl) {
          travelEl.textContent = profile.travel_radius || 'Not set';
        }
        if (locationEl) {
          locationEl.textContent = profile.location || 'Not set';
        }
        if (drivingEl) {
          drivingEl.textContent = profile.driving_licence_label || 'Not set';
        }
        if (carEl) {
          carEl.textContent = profile.car_owner_label || 'Not set';
        }
        if (hasDbsEl) {
          hasDbsEl.textContent = profile.no_dbs_label || 'Not set';
        }
        if (dbsUpdateEl) {
          dbsUpdateEl.textContent = profile.dbs_update_service_label || 'Not set';
        }
        if (daysEl) {
          daysEl.textContent = profile.availability_days_label || 'Not set';
        }
        if (addressEl) {
          addressEl.textContent = profile.address_display || 'Not set';
        }
        var completionText = document.querySelector('[data-profile-completion-text]');
        var completionBar = document.querySelector('[data-profile-completion-bar]');
        var completionCopy = document.querySelector('[data-profile-completion-copy]');
        var pct = typeof data.data.completion === 'number' ? data.data.completion : null;
        if (pct !== null) {
          if (completionText) {
            completionText.textContent = pct + '% Complete';
          }
          if (completionBar) {
            completionBar.style.width = pct + '%';
          }
          if (completionCopy) {
            completionCopy.textContent = 'Profile ' + pct + '% complete';
          }
        }
        toggleEdit('personal', false);
        toggleEdit('role', false);
        setProfileMessage('Profile updated.');
      }).catch(function () {
        setProfileMessage('Unable to save profile.');
      });
    };
    document.querySelectorAll('[data-profile-form]').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        saveProfileSection(form);
      });
    });
  }

  if (candidateDocsRoot && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.candidateDocNonce) {
    var docMessage = candidateDocsRoot.querySelector('[data-doc-message]');
    var completionText = document.querySelector('[data-profile-completion-text]');
    var completionCopy = document.querySelector('[data-profile-completion-copy]');
    var completionBar = document.querySelector('[data-profile-completion-bar]');
    var complianceScoreText = document.querySelector('[data-compliance-score-text]');
    var complianceScoreBar = document.querySelector('[data-compliance-score-bar]');
    var complianceRiskBadge = document.querySelector('[data-compliance-risk-badge]');
    var complianceRiskText = document.querySelector('[data-compliance-risk-text]');
    var complianceBreakdown = document.querySelector('[data-compliance-breakdown]');
    var setDocMessage = function (text) {
      if (docMessage) {
        docMessage.textContent = text || '';
      }
    };
    var updateCompletion = function (pct) {
      if (typeof pct !== 'number') {
        return;
      }
      if (completionText) {
        completionText.textContent = pct + '% Complete';
      }
      if (completionCopy) {
        completionCopy.textContent = 'Profile ' + pct + '% complete';
      }
      if (completionBar) {
        completionBar.style.width = pct + '%';
      }
    };
    var updateComplianceSummary = function () {
      var states = ['dbs', 'id', 'cv'].map(function (type) {
        var badgeNode = candidateDocsRoot.querySelector('[data-doc-badge="' + type + '"]');
        if (!badgeNode) {
          return 'not_uploaded';
        }
        return badgeNode.getAttribute('data-doc-state') || 'not_uploaded';
      });
      var hasRejected = states.indexOf('rejected') > -1;
      var hasMissing = states.indexOf('not_uploaded') > -1;
      var allApproved = states.every(function (state) { return state === 'approved'; });
      var summaryClass = 'is-pending';
      var summaryLabel = 'Awaiting Review';
      var summaryCopy = 'Documents Awaiting Review';
      if (hasRejected) {
        summaryClass = 'is-declined';
        summaryLabel = 'Action Required';
        summaryCopy = 'Action Required - Document Rejected';
      } else if (hasMissing) {
        summaryClass = 'is-declined';
        summaryLabel = 'Incomplete';
        summaryCopy = 'Incomplete - Documents Required';
      } else if (allApproved) {
        summaryClass = 'is-verified';
        summaryLabel = 'Verified';
        summaryCopy = 'Verified';
      }
      var complianceBadge = document.querySelector('[data-compliance-status]');
      if (complianceBadge) {
        complianceBadge.classList.remove('is-pending', 'is-declined', 'is-verified');
        complianceBadge.classList.add(summaryClass);
        complianceBadge.textContent = summaryLabel;
      }
      var verificationBadge = document.querySelector('[data-admin-verification-status]');
      if (verificationBadge) {
        verificationBadge.classList.remove('is-pending', 'is-declined', 'is-verified');
        verificationBadge.classList.add(summaryClass);
        verificationBadge.textContent = summaryLabel;
      }
      var verificationCopy = document.querySelector('[data-admin-verification-copy]');
      if (verificationCopy) {
        verificationCopy.textContent = summaryCopy;
      }
      if (complianceScoreText && complianceScoreBar) {
        var score = 0;
        if (states[0] === 'approved') {
          score += 40;
        }
        if (states[1] === 'approved') {
          score += 30;
        }
        if (states[2] === 'approved') {
          score += 30;
        }
        complianceScoreText.textContent = score + '%';
        complianceScoreBar.style.width = score + '%';
      }
    };
    var applyCompliancePayload = function (payload) {
      if (!payload || typeof payload !== 'object') {
        updateComplianceSummary();
        return;
      }
      if (complianceScoreText && complianceScoreBar) {
        var score = parseInt(payload.score || '0', 10);
        if (Number.isNaN(score)) {
          score = 0;
        }
        score = Math.max(0, Math.min(100, score));
        complianceScoreText.textContent = score + '%';
        complianceScoreBar.style.width = score + '%';
      }
      if (complianceRiskBadge && complianceRiskText) {
        var riskLevel = String(payload.risk_level || 'Medium');
        complianceRiskText.textContent = riskLevel;
        complianceRiskBadge.classList.remove('is-pending', 'is-declined', 'is-verified');
        if (riskLevel === 'Low') {
          complianceRiskBadge.classList.add('is-verified');
        } else if (riskLevel === 'High') {
          complianceRiskBadge.classList.add('is-declined');
        } else {
          complianceRiskBadge.classList.add('is-pending');
        }
      }
      if (complianceBreakdown && Array.isArray(payload.breakdown)) {
        complianceBreakdown.innerHTML = '';
        payload.breakdown.forEach(function (item) {
          var li = document.createElement('li');
          var label = String((item && item.label) || 'Item');
          var value = String((item && item.value) || '');
          var points = parseInt((item && item.points) || 0, 10);
          var maxPoints = parseInt((item && item.max_points) || 0, 10);
          if (Number.isNaN(points)) {
            points = 0;
          }
          if (Number.isNaN(maxPoints)) {
            maxPoints = 0;
          }
          li.textContent = label + ': ' + value + ' (' + points + '/' + maxPoints + ')';
          complianceBreakdown.appendChild(li);
        });
      }
    };
    var applyDocStatus = function (docType, status) {
      if (!status) {
        return;
      }
      var statusEl = candidateDocsRoot.querySelector('[data-doc-status="' + docType + '"]');
      var dateEl = candidateDocsRoot.querySelector('[data-doc-date="' + docType + '"]');
      var sizeEl = candidateDocsRoot.querySelector('[data-doc-size="' + docType + '"]');
      var badgeEl = candidateDocsRoot.querySelector('[data-doc-badge="' + docType + '"]');
      var uploadBtn = candidateDocsRoot.querySelector('[data-doc-upload-trigger="' + docType + '"]');
      var viewBtn = candidateDocsRoot.querySelector('[data-doc-view="' + docType + '"]');
      var deleteBtn = candidateDocsRoot.querySelector('[data-doc-delete="' + docType + '"]');
      var reasonEl = candidateDocsRoot.querySelector('[data-doc-reason="' + docType + '"]');
      var docState = status.doc_status || (status.uploaded ? 'pending' : 'not_uploaded');
      var stateLabels = {
        not_uploaded: 'Not Uploaded',
        pending: 'Pending Review',
        approved: 'Approved',
        rejected: 'Rejected'
      };
      var stateBadgeClass = {
        not_uploaded: 'is-declined',
        pending: 'is-pending',
        approved: 'is-verified',
        rejected: 'is-declined'
      };
      if (statusEl) {
        statusEl.textContent = status.uploaded ? (status.filename || 'Uploaded') : 'Not uploaded';
      }
      if (dateEl) {
        dateEl.textContent = status.uploaded_at_label || '-';
      }
      if (sizeEl) {
        sizeEl.textContent = status.filesize_label || '-';
      }
      if (badgeEl) {
        badgeEl.classList.remove('is-verified', 'is-declined', 'is-pending');
        badgeEl.classList.add(stateBadgeClass[docState] || 'is-declined');
        badgeEl.textContent = stateLabels[docState] || 'Not Uploaded';
        badgeEl.setAttribute('data-doc-state', docState);
      }
      if (uploadBtn) {
        uploadBtn.textContent = status.uploaded ? 'Replace' : 'Upload';
        uploadBtn.classList.toggle('cmn-primary', !status.uploaded);
        uploadBtn.classList.toggle('cmn-ghost', !!status.uploaded);
      }
      if (viewBtn) {
        viewBtn.disabled = !status.uploaded;
      }
      if (deleteBtn) {
        deleteBtn.disabled = !status.uploaded;
      }
      var complianceRow = document.querySelector('[data-compliance-doc="' + docType + '"]');
      if (complianceRow) {
        complianceRow.classList.remove('is-ok', 'is-warn');
        complianceRow.classList.add(docState === 'approved' ? 'is-ok' : 'is-warn');
        var labelMap = { dbs: 'DBS', id: 'ID', cv: 'CV' };
        var label = (labelMap[docType] || docType.toUpperCase()) + ': ' + (stateLabels[docState] || 'Not Uploaded');
        complianceRow.textContent = label;
      }
      if (reasonEl) {
        if (docState === 'rejected' && status.review_reason) {
          reasonEl.textContent = 'Reason: ' + status.review_reason;
        } else {
          reasonEl.textContent = '';
        }
      }
      updateComplianceSummary();
    };
    var fetchDoc = function (docType, callback) {
      var formData = new FormData();
      formData.append('action', 'cmn_candidate_get_doc');
      formData.append('nonce', window.cmnPortal.candidateDocNonce);
      formData.append('doc_type', docType);
      fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      }).then(function (response) {
        return response.json();
      }).then(function (data) {
        if (!data || !data.success || !data.data || !(data.data.download_url || data.data.url)) {
          setDocMessage(data && data.data && data.data.message ? data.data.message : 'Document not found.');
          return;
        }
        if (typeof callback === 'function') {
          callback(data.data);
        }
      }).catch(function () {
        setDocMessage('Unable to load document.');
      });
    };

    candidateDocsRoot.querySelectorAll('[data-doc-upload-trigger]').forEach(function (button) {
      button.addEventListener('click', function () {
        var docType = button.getAttribute('data-doc-upload-trigger');
        var input = candidateDocsRoot.querySelector('[data-doc-input="' + docType + '"]');
        if (input) {
          input.click();
        }
      });
    });

    candidateDocsRoot.querySelectorAll('[data-doc-input]').forEach(function (input) {
      input.addEventListener('change', function () {
        if (!input.files || !input.files.length) {
          return;
        }
        var docType = input.getAttribute('data-doc-input');
        var file = input.files[0];
        var formData = new FormData();
        formData.append('action', 'cmn_candidate_upload_doc');
        formData.append('nonce', window.cmnPortal.candidateDocNonce);
        formData.append('doc_type', docType);
        formData.append('cmn_doc_file', file);
        var progressWrap = candidateDocsRoot.querySelector('[data-doc-progress="' + docType + '"]');
        var progressBar = progressWrap ? progressWrap.querySelector('span') : null;
        if (progressWrap) {
          progressWrap.hidden = false;
        }
        if (progressBar) {
          progressBar.style.width = '0%';
        }
        setDocMessage('Uploading...');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.cmnPortal.ajaxUrl, true);
        xhr.withCredentials = true;
        xhr.upload.addEventListener('progress', function (event) {
          if (!event.lengthComputable || !progressBar) {
            return;
          }
          var pct = Math.max(0, Math.min(100, Math.round((event.loaded / event.total) * 100)));
          progressBar.style.width = pct + '%';
        });
        xhr.onreadystatechange = function () {
          if (xhr.readyState !== 4) {
            return;
          }
          if (progressWrap) {
            progressWrap.hidden = true;
          }
          input.value = '';
          var data = null;
          try {
            data = JSON.parse(xhr.responseText || '{}');
          } catch (e) {
            data = null;
          }
          if (xhr.status < 200 || xhr.status >= 300 || !data || !data.success) {
            setDocMessage(data && data.data && data.data.message ? data.data.message : 'Upload failed.');
            return;
          }
          if (data.data && data.data.status) {
            applyDocStatus(docType, data.data.status);
          }
          if (data.data && data.data.compliance) {
            applyCompliancePayload(data.data.compliance);
          }
          if (data.data && typeof data.data.completion === 'number') {
            updateCompletion(data.data.completion);
          }
          setDocMessage('Document uploaded successfully.');
        };
        xhr.onerror = function () {
          if (progressWrap) {
            progressWrap.hidden = true;
          }
          input.value = '';
          setDocMessage('Upload failed.');
        };
        xhr.send(formData);
      });
    });

    candidateDocsRoot.querySelectorAll('[data-doc-delete]').forEach(function (button) {
      button.addEventListener('click', function () {
        var docType = button.getAttribute('data-doc-delete');
        var formData = new FormData();
        formData.append('action', 'cmn_candidate_remove_doc');
        formData.append('nonce', window.cmnPortal.candidateDocNonce);
        formData.append('doc_type', docType);
        setDocMessage('Removing...');
        fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: formData
        }).then(function (response) {
          return response.json();
        }).then(function (data) {
          if (!data || !data.success) {
            setDocMessage(data && data.data && data.data.message ? data.data.message : 'Unable to remove document.');
            return;
          }
          if (data.data && data.data.status) {
            applyDocStatus(docType, data.data.status);
          }
          if (data.data && data.data.compliance) {
            applyCompliancePayload(data.data.compliance);
          }
          if (data.data && typeof data.data.completion === 'number') {
            updateCompletion(data.data.completion);
          }
          setDocMessage('Document removed.');
        }).catch(function () {
          setDocMessage('Unable to remove document.');
        });
      });
    });

    candidateDocsRoot.querySelectorAll('[data-doc-view]').forEach(function (button) {
      button.addEventListener('click', function () {
        var docType = button.getAttribute('data-doc-view');
        fetchDoc(docType, function (payload) {
          window.open(payload.download_url, '_blank', 'noopener,noreferrer');
        });
      });
    });
    var complianceSyncData = new FormData();
    complianceSyncData.append('action', 'cmn_get_compliance_status');
    complianceSyncData.append('nonce', window.cmnPortal.candidateDocNonce);
    fetch(window.cmnPortal.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: complianceSyncData
    }).then(function (response) {
      return response.json();
    }).then(function (data) {
      if (data && data.success && data.data) {
        applyCompliancePayload(data.data);
      } else {
        updateComplianceSummary();
      }
    }).catch(function () {
      updateComplianceSummary();
    });
    updateComplianceSummary();
  }

  var learningSaveBtn = document.querySelector('[data-learning-save]');
  if (learningSaveBtn && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.candidateLearningNonce) {
    var learningToggle = document.querySelector('[data-learning-opt-in]');
    var learningMessage = document.querySelector('[data-learning-message]');
    learningSaveBtn.addEventListener('click', function () {
      var formData = new FormData();
      formData.append('action', 'cmn_candidate_learning_opt_in');
      formData.append('nonce', window.cmnPortal.candidateLearningNonce);
      formData.append('enabled', learningToggle && learningToggle.checked ? '1' : '0');
      if (learningMessage) {
        learningMessage.textContent = 'Saving...';
      }
      fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      }).then(function (response) {
        return response.json();
      }).then(function (data) {
        if (!data || !data.success) {
          if (learningMessage) {
            learningMessage.textContent = data && data.data && data.data.message ? data.data.message : 'Unable to save preference.';
          }
          return;
        }
        if (learningMessage) {
          learningMessage.textContent = data.data && data.data.message ? data.data.message : 'Saved.';
        }
      }).catch(function () {
        if (learningMessage) {
          learningMessage.textContent = 'Unable to save preference.';
        }
      });
    });
  }

  var requestCountdowns = document.querySelectorAll('[data-request-expires]');
  if (requestCountdowns.length) {
    var updateCountdowns = function () {
      var now = Date.now();
      requestCountdowns.forEach(function (el) {
        var raw = el.getAttribute('data-request-expires');
        if (!raw) {
          return;
        }
        var endTs = Date.parse(raw);
        if (Number.isNaN(endTs)) {
          return;
        }
        var diff = Math.max(0, endTs - now);
        if (diff <= 0) {
          el.textContent = 'Request expired';
          return;
        }
        var mins = Math.floor(diff / 60000);
        var secs = Math.floor((diff % 60000) / 1000);
        el.textContent = 'Time remaining: ' + mins + 'm ' + secs + 's';
      });
    };
    updateCountdowns();
    window.setInterval(updateCountdowns, 1000);
  }

  var healthRoot = document.querySelector('[data-system-health-root]');
  if (healthRoot && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.systemHealthNonce) {
    var healthState = {
      runId: healthRoot.getAttribute('data-last-run-id') || '',
      page: 1,
      perPage: 25,
      severity: '',
      entityType: '',
      issueCode: '',
      search: '',
      showIgnored: false,
      sortBy: 'created_at',
      sortDir: 'DESC'
    };
    var activeIssueId = 0;

    var tabButtons = Array.prototype.slice.call(healthRoot.querySelectorAll('[data-health-tab]'));
    var panels = Array.prototype.slice.call(healthRoot.querySelectorAll('[data-health-panel]'));
    var runButton = healthRoot.querySelector('[data-system-health-run]');
    var runMsg = healthRoot.querySelector('[data-system-health-run-msg]');
    var issuesBody = healthRoot.querySelector('[data-health-issues-body]');
    var runsBody = healthRoot.querySelector('[data-health-runs-body]');
    var fixesBody = healthRoot.querySelector('[data-health-fixes-body]');
    var pageLabel = healthRoot.querySelector('[data-health-page-label]');
    var pagePrev = healthRoot.querySelector('[data-health-page=\"prev\"]');
    var pageNext = healthRoot.querySelector('[data-health-page=\"next\"]');
    var severityFilter = healthRoot.querySelector('[data-health-filter=\"severity\"]');
    var entityFilter = healthRoot.querySelector('[data-health-filter=\"entity_type\"]');
    var issueCodeFilter = healthRoot.querySelector('[data-health-filter=\"issue_code\"]');
    var searchFilter = healthRoot.querySelector('[data-health-filter=\"search\"]');
    var showIgnoredFilter = healthRoot.querySelector('[data-health-filter=\"show_ignored\"]');
    var modal = healthRoot.querySelector('[data-health-issue-modal]');
    var modalClose = healthRoot.querySelector('[data-health-issue-modal-close]');
    var modalTitle = healthRoot.querySelector('[data-health-modal-title]');
    var modalDescription = healthRoot.querySelector('[data-health-modal-description]');
    var modalActionText = healthRoot.querySelector('[data-health-modal-action]');
    var modalMeta = healthRoot.querySelector('[data-health-modal-meta]');
    var modalActionButtons = Array.prototype.slice.call(healthRoot.querySelectorAll('[data-health-issue-action]'));
    var repairWrap = healthRoot.querySelector('[data-health-repair-wrap]');
    var repairSummary = healthRoot.querySelector('[data-health-repair-summary]');
    var repairPreview = healthRoot.querySelector('[data-health-repair-preview]');
    var repairDryRun = healthRoot.querySelector('[data-health-repair-dry-run]');
    var repairConfirmWrap = healthRoot.querySelector('[data-health-repair-confirm-wrap]');
    var repairConfirmInput = healthRoot.querySelector('[data-health-repair-confirm-input]');
    var repairPreviewBtn = healthRoot.querySelector('[data-health-repair-preview-btn]');
    var repairApplyBtn = healthRoot.querySelector('[data-health-repair-apply-btn]');
    var repairMessage = healthRoot.querySelector('[data-health-repair-message]');
    var activeIssueRepairPreview = null;
    var summaryFields = {
      lastRun: healthRoot.querySelector('[data-health-last-run]'),
      lastDuration: healthRoot.querySelector('[data-health-last-duration]'),
      total: healthRoot.querySelector('[data-health-last-total]'),
      critical: healthRoot.querySelector('[data-health-last-critical]'),
      warning: healthRoot.querySelector('[data-health-last-warning]'),
      info: healthRoot.querySelector('[data-health-last-info]'),
      criticalCount: healthRoot.querySelector('[data-health-critical-count]'),
      warningCount: healthRoot.querySelector('[data-health-warning-count]'),
      infoCount: healthRoot.querySelector('[data-health-info-count]'),
      criticalPct: healthRoot.querySelector('[data-health-critical-pct]'),
      warningPct: healthRoot.querySelector('[data-health-warning-pct]'),
      infoPct: healthRoot.querySelector('[data-health-info-pct]'),
      healthyCount: healthRoot.querySelector('[data-health-healthy-count]')
    };

    var api = function (action, payload) {
      var fd = new FormData();
      fd.append('action', action);
      fd.append('nonce', window.cmnPortal.systemHealthNonce);
      Object.keys(payload || {}).forEach(function (key) {
        if (payload[key] === undefined || payload[key] === null) {
          return;
        }
        fd.append(key, payload[key]);
      });
      return fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      }).then(function (res) { return res.json(); });
    };

    var formatNumber = function (num) {
      return String(parseInt(num || 0, 10));
    };

    var formatPct = function (count, total) {
      if (!total) {
        return '0%';
      }
      return Math.round((count / total) * 100) + '%';
    };

    var setMessage = function (text, isError) {
      if (!runMsg) {
        return;
      }
      runMsg.textContent = text || '';
      runMsg.classList.toggle('is-error', !!isError);
    };

    var setSummary = function (run) {
      if (!run) {
        return;
      }
      var total = parseInt(run.total_issues_found || 0, 10);
      var critical = parseInt(run.critical_count || 0, 10);
      var warning = parseInt(run.warning_count || 0, 10);
      var info = parseInt(run.info_count || 0, 10);
      if (summaryFields.lastRun) summaryFields.lastRun.textContent = run.started_at || '—';
      if (summaryFields.lastDuration) summaryFields.lastDuration.textContent = run.duration_ms ? (Math.round((run.duration_ms / 1000) * 100) / 100) + 's' : '—';
      if (summaryFields.total) summaryFields.total.textContent = formatNumber(total);
      if (summaryFields.critical) summaryFields.critical.textContent = formatNumber(critical);
      if (summaryFields.warning) summaryFields.warning.textContent = formatNumber(warning);
      if (summaryFields.info) summaryFields.info.textContent = formatNumber(info);
      if (summaryFields.criticalCount) summaryFields.criticalCount.textContent = formatNumber(critical);
      if (summaryFields.warningCount) summaryFields.warningCount.textContent = formatNumber(warning);
      if (summaryFields.infoCount) summaryFields.infoCount.textContent = formatNumber(info);
      if (summaryFields.criticalPct) summaryFields.criticalPct.textContent = formatPct(critical, total);
      if (summaryFields.warningPct) summaryFields.warningPct.textContent = formatPct(warning, total);
      if (summaryFields.infoPct) summaryFields.infoPct.textContent = formatPct(info, total);
      if (summaryFields.healthyCount) summaryFields.healthyCount.textContent = critical === 0 ? '1' : '0';
    };

    var renderIssueRows = function (issues) {
      if (!issuesBody) {
        return;
      }
      if (!issues || !issues.length) {
        issuesBody.innerHTML = '<tr><td colspan="7">No issues found for this filter.</td></tr>';
        return;
      }
      issuesBody.innerHTML = issues.map(function (issue) {
        var sev = String(issue.severity || 'info').toLowerCase();
        var entityId = issue.entity_id ? String(issue.entity_id) : '—';
        var entityCell = issue.entity_url ? '<a href="' + issue.entity_url + '" target="_blank" rel="noopener noreferrer">' + entityId + '</a>' : entityId;
        var flagBits = [];
        if (parseInt(issue.reviewed || 0, 10) === 1) {
          flagBits.push('<span class="cmn-status-chip is-verified">Reviewed</span>');
        }
        if (parseInt(issue.ignored || 0, 10) === 1) {
          flagBits.push('<span class="cmn-status-chip is-muted">Ignored</span>');
        }
        return [
          '<tr data-health-issue-row data-issue-id="' + issue.id + '">',
          '<td><span class="cmn-status-chip is-' + sev + '">' + sev.toUpperCase() + '</span></td>',
          '<td>' + (issue.entity_type || 'system') + '</td>',
          '<td>' + entityCell + '</td>',
          '<td>' + (issue.issue_code || '') + '</td>',
          '<td>' + (issue.description || '') + (flagBits.length ? '<div class="cmn-system-health-flags">' + flagBits.join(' ') + '</div>' : '') + '</td>',
          '<td>' + (issue.recommended_action || '') + '</td>',
          '<td>' + (issue.created_at || '') + '</td>',
          '</tr>'
        ].join('');
      }).join('');
    };

    var renderRunRows = function (runs) {
      if (!runsBody) {
        return;
      }
      if (!runs || !runs.length) {
        runsBody.innerHTML = '<tr><td colspan="9">No scan history available yet.</td></tr>';
        return;
      }
      runsBody.innerHTML = runs.map(function (run) {
        return [
          '<tr>',
          '<td>' + (run.run_id || '') + '</td>',
          '<td>' + (run.started_at || '') + '</td>',
          '<td>' + (run.duration_ms ? (Math.round((run.duration_ms / 1000) * 100) / 100) + 's' : '—') + '</td>',
          '<td>' + formatNumber(run.total_issues_found) + '</td>',
          '<td>' + formatNumber(run.critical_count) + '</td>',
          '<td>' + formatNumber(run.warning_count) + '</td>',
          '<td>' + formatNumber(run.info_count) + '</td>',
          '<td>' + (run.status || '') + '</td>',
          '<td><button type="button" class="cmn-ghost cmn-btn-mini" data-health-view-run="' + run.run_id + '">View Results</button></td>',
          '</tr>'
        ].join('');
      }).join('');
    };

    var renderFixRows = function (fixes) {
      if (!fixesBody) {
        return;
      }
      if (!fixes || !fixes.length) {
        fixesBody.innerHTML = '<tr><td colspan="9">No fix log entries yet.</td></tr>';
        return;
      }
      fixesBody.innerHTML = fixes.map(function (row) {
        return [
          '<tr>',
          '<td>' + (row.performed_at || '') + '</td>',
          '<td>' + (row.fix_code || '') + '</td>',
          '<td>' + (row.entity_type || '') + '</td>',
          '<td>' + (row.entity_id ? String(row.entity_id) : '—') + '</td>',
          '<td>' + (row.issue_id ? String(row.issue_id) : '—') + '</td>',
          '<td>' + (row.performed_by ? String(row.performed_by) : '—') + '</td>',
          '<td>' + (parseInt(row.dry_run || 0, 10) === 1 ? 'Yes' : 'No') + '</td>',
          '<td>' + (row.status || '') + '</td>',
          '<td>' + (row.notes || '—') + '</td>',
          '</tr>'
        ].join('');
      }).join('');
    };

    var setIssueOptions = function (selectEl, values, currentValue) {
      if (!selectEl) {
        return;
      }
      var baseLabel = selectEl.getAttribute('data-health-filter') === 'entity_type' ? 'All entities' : 'All issue codes';
      var html = ['<option value="">' + baseLabel + '</option>'];
      (values || []).forEach(function (val) {
        var selected = currentValue && currentValue === val ? ' selected' : '';
        html.push('<option value="' + val + '"' + selected + '>' + val + '</option>');
      });
      selectEl.innerHTML = html.join('');
    };

    var loadIssues = function () {
      return api('cmn_get_system_health_issues', {
        run_id: healthState.runId,
        page: healthState.page,
        per_page: healthState.perPage,
        severity: healthState.severity,
        entity_type: healthState.entityType,
        issue_code: healthState.issueCode,
        search: healthState.search,
        show_ignored: healthState.showIgnored ? 1 : 0,
        sort_by: healthState.sortBy,
        sort_dir: healthState.sortDir
      }).then(function (data) {
        if (!data || !data.success) {
          throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to load issues.');
        }
        var payload = data.data || {};
        if (payload.run_id) {
          healthState.runId = payload.run_id;
        }
        renderIssueRows(payload.issues || []);
        setSummary(payload.run || null);
        setIssueOptions(entityFilter, payload.entity_types || [], healthState.entityType);
        setIssueOptions(issueCodeFilter, payload.issue_codes || [], healthState.issueCode);
        var pagination = payload.pagination || {};
        var page = parseInt(pagination.page || 1, 10);
        var totalPages = parseInt(pagination.total_pages || 1, 10);
        if (pageLabel) {
          pageLabel.textContent = 'Page ' + page + ' of ' + totalPages;
        }
        if (pagePrev) {
          pagePrev.disabled = page <= 1;
        }
        if (pageNext) {
          pageNext.disabled = page >= totalPages;
        }
      }).catch(function (err) {
        setMessage(err.message || 'Unable to load system health issues.', true);
      });
    };

    var loadRuns = function () {
      return api('cmn_get_system_health_runs', { limit: 100 }).then(function (data) {
        if (!data || !data.success) {
          throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to load run history.');
        }
        var payload = data.data || {};
        renderRunRows(payload.runs || []);
        if (!healthState.runId && payload.latest && payload.latest.run_id) {
          healthState.runId = payload.latest.run_id;
        }
      }).catch(function () {
        // Keep UI stable if history fetch fails.
      });
    };

    var loadFixes = function (forIssueId) {
      var payload = { limit: 200 };
      if (forIssueId) {
        payload.issue_id = forIssueId;
      }
      return api('cmn_get_system_health_fixes', payload).then(function (data) {
        if (!data || !data.success) {
          throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to load fix log.');
        }
        renderFixRows((data.data && data.data.fixes) ? data.data.fixes : []);
      }).catch(function () {
        // Keep UI stable on fix-log failures.
      });
    };

    var resetRepairUi = function () {
      activeIssueRepairPreview = null;
      if (repairWrap) {
        repairWrap.hidden = true;
      }
      if (repairSummary) {
        repairSummary.textContent = '';
      }
      if (repairPreview) {
        repairPreview.textContent = '{}';
      }
      if (repairDryRun) {
        repairDryRun.checked = true;
      }
      if (repairConfirmWrap) {
        repairConfirmWrap.hidden = true;
      }
      if (repairConfirmInput) {
        repairConfirmInput.value = '';
      }
      if (repairApplyBtn) {
        repairApplyBtn.hidden = true;
      }
      if (repairMessage) {
        repairMessage.textContent = '';
      }
    };

    var openModal = function () {
      if (!modal) {
        return;
      }
      modal.hidden = false;
      modal.classList.add('is-open');
      document.body.classList.add('cmn-support-modal-lock');
    };
    var closeModal = function () {
      if (!modal) {
        return;
      }
      modal.hidden = true;
      modal.classList.remove('is-open');
      document.body.classList.remove('cmn-support-modal-lock');
      activeIssueId = 0;
    };

    var loadIssueDetail = function (issueId) {
      activeIssueId = issueId;
      resetRepairUi();
      return api('cmn_get_system_health_issue_detail', { issue_id: issueId }).then(function (data) {
        if (!data || !data.success || !data.data || !data.data.issue) {
          throw new Error((data && data.data && data.data.message) ? data.data.message : 'Issue detail unavailable.');
        }
        var issue = data.data.issue;
        if (modalTitle) {
          modalTitle.textContent = (issue.issue_code || 'Issue') + ' · ' + (issue.severity || '').toUpperCase();
        }
        if (modalDescription) {
          modalDescription.textContent = issue.description || '';
        }
        if (modalActionText) {
          modalActionText.textContent = issue.recommended_action || '';
        }
        if (modalMeta) {
          var pretty = '{}';
          try {
            pretty = JSON.stringify(issue.meta || {}, null, 2);
          } catch (e) {
            pretty = '{}';
          }
          modalMeta.textContent = pretty;
        }
        if (repairWrap && repairPreviewBtn) {
          repairWrap.hidden = false;
          repairPreviewBtn.hidden = false;
          repairApplyBtn.hidden = true;
        }
        openModal();
      }).catch(function (err) {
        setMessage(err.message || 'Unable to load issue detail.', true);
      });
    };

    tabButtons.forEach(function (tabBtn) {
      tabBtn.addEventListener('click', function () {
        var target = tabBtn.getAttribute('data-health-tab');
        tabButtons.forEach(function (btn) { btn.classList.toggle('is-active', btn === tabBtn); });
        panels.forEach(function (panel) {
          panel.classList.toggle('is-active', panel.getAttribute('data-health-panel') === target);
        });
        if (target === 'fix-log') {
          loadFixes();
        }
      });
    });

    if (runButton) {
      runButton.addEventListener('click', function () {
        runButton.disabled = true;
        setMessage('Running scan...', false);
        api('cmn_run_system_health', {}).then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to run system scan.');
          }
          var summary = (data.data && data.data.summary) ? data.data.summary : null;
          if (summary && summary.run_id) {
            healthState.runId = summary.run_id;
            healthState.page = 1;
          }
          setMessage('System health scan completed.', false);
          return Promise.all([loadRuns(), loadIssues()]);
        }).catch(function (err) {
          setMessage(err.message || 'System health scan failed.', true);
        }).finally(function () {
          runButton.disabled = false;
        });
      });
    }

    if (severityFilter) {
      severityFilter.addEventListener('change', function () {
        healthState.severity = severityFilter.value || '';
        healthState.page = 1;
        loadIssues();
      });
    }
    if (entityFilter) {
      entityFilter.addEventListener('change', function () {
        healthState.entityType = entityFilter.value || '';
        healthState.page = 1;
        loadIssues();
      });
    }
    if (issueCodeFilter) {
      issueCodeFilter.addEventListener('change', function () {
        healthState.issueCode = issueCodeFilter.value || '';
        healthState.page = 1;
        loadIssues();
      });
    }
    if (showIgnoredFilter) {
      showIgnoredFilter.addEventListener('change', function () {
        healthState.showIgnored = !!showIgnoredFilter.checked;
        healthState.page = 1;
        loadIssues();
      });
    }
    var searchTimer = null;
    if (searchFilter) {
      searchFilter.addEventListener('input', function () {
        if (searchTimer) {
          window.clearTimeout(searchTimer);
        }
        searchTimer = window.setTimeout(function () {
          healthState.search = (searchFilter.value || '').trim();
          healthState.page = 1;
          loadIssues();
        }, 280);
      });
    }
    Array.prototype.slice.call(healthRoot.querySelectorAll('[data-health-severity-filter]')).forEach(function (tileBtn) {
      tileBtn.addEventListener('click', function () {
        var target = tileBtn.getAttribute('data-health-severity-filter') || '';
        healthState.severity = target === 'all' ? '' : target;
        healthState.page = 1;
        if (severityFilter) {
          severityFilter.value = healthState.severity;
        }
        loadIssues();
      });
    });
    if (pagePrev) {
      pagePrev.addEventListener('click', function () {
        if (healthState.page > 1) {
          healthState.page -= 1;
          loadIssues();
        }
      });
    }
    if (pageNext) {
      pageNext.addEventListener('click', function () {
        healthState.page += 1;
        loadIssues();
      });
    }

    if (issuesBody) {
      issuesBody.addEventListener('click', function (event) {
        var row = event.target.closest('[data-health-issue-row]');
        if (!row) {
          return;
        }
        var issueId = parseInt(row.getAttribute('data-issue-id') || '0', 10);
        if (issueId > 0) {
          loadIssueDetail(issueId);
        }
      });
    }

    if (runsBody) {
      runsBody.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-health-view-run]');
        if (!btn) {
          return;
        }
        var runId = btn.getAttribute('data-health-view-run') || '';
        if (!runId) {
          return;
        }
        healthState.runId = runId;
        healthState.page = 1;
        tabButtons.forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-health-tab') === 'issues'); });
        panels.forEach(function (p) { p.classList.toggle('is-active', p.getAttribute('data-health-panel') === 'issues'); });
        loadIssues();
      });
    }

    if (modalClose) {
      modalClose.addEventListener('click', closeModal);
    }
    if (modal) {
      modal.addEventListener('click', function (event) {
        if (event.target === modal) {
          closeModal();
        }
      });
    }

    if (repairPreviewBtn) {
      repairPreviewBtn.addEventListener('click', function () {
        if (activeIssueId < 1) {
          return;
        }
        repairPreviewBtn.disabled = true;
        if (repairMessage) {
          repairMessage.textContent = 'Loading repair preview...';
        }
        api('cmn_system_health_preview_fix', { issue_id: activeIssueId }).then(function (data) {
          if (!data || !data.success || !data.data || !data.data.preview) {
            throw new Error((data && data.data && data.data.message) ? data.data.message : 'Repair preview unavailable.');
          }
          activeIssueRepairPreview = data.data.preview;
          if (repairSummary) {
            repairSummary.textContent = data.data.preview.impact_summary || '';
          }
          if (repairPreview) {
            repairPreview.textContent = JSON.stringify(data.data.preview.changes_preview || {}, null, 2);
          }
          if (repairConfirmWrap) {
            repairConfirmWrap.hidden = !(data.data.preview.requires_confirmation && !(repairDryRun && repairDryRun.checked));
          }
          if (repairApplyBtn) {
            repairApplyBtn.hidden = false;
          }
          if (repairMessage) {
            repairMessage.textContent = '';
          }
        }).catch(function (err) {
          if (repairMessage) {
            repairMessage.textContent = err.message || 'Unable to preview repair.';
          }
        }).finally(function () {
          repairPreviewBtn.disabled = false;
        });
      });
    }

    if (repairDryRun) {
      repairDryRun.addEventListener('change', function () {
        if (!repairConfirmWrap) {
          return;
        }
        var needsConfirm = !!(activeIssueRepairPreview && activeIssueRepairPreview.requires_confirmation);
        repairConfirmWrap.hidden = !needsConfirm || !!repairDryRun.checked;
      });
    }

    if (repairApplyBtn) {
      repairApplyBtn.addEventListener('click', function () {
        if (activeIssueId < 1 || !activeIssueRepairPreview) {
          return;
        }
        var wantsDryRun = !!(repairDryRun && repairDryRun.checked);
        var needsConfirm = !!activeIssueRepairPreview.requires_confirmation && !wantsDryRun;
        var confirmText = repairConfirmInput ? String(repairConfirmInput.value || '').trim() : '';
        if (needsConfirm && confirmText.toUpperCase() !== 'CONFIRM') {
          if (repairMessage) {
            repairMessage.textContent = 'Type CONFIRM to apply this repair.';
          }
          return;
        }
        repairApplyBtn.disabled = true;
        if (repairMessage) {
          repairMessage.textContent = wantsDryRun ? 'Running dry run...' : 'Applying repair...';
        }
        api('cmn_system_health_apply_fix', {
          issue_id: activeIssueId,
          dry_run: wantsDryRun ? 1 : 0,
          confirm_text: confirmText
        }).then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to apply repair.');
          }
          if (repairMessage) {
            repairMessage.textContent = (data.data && data.data.result && data.data.result.message) ? data.data.result.message : 'Repair completed.';
          }
          loadIssues();
          loadFixes(activeIssueId);
          if (!wantsDryRun) {
            window.setTimeout(closeModal, 500);
          }
        }).catch(function (err) {
          if (repairMessage) {
            repairMessage.textContent = err.message || 'Unable to apply repair.';
          }
        }).finally(function () {
          repairApplyBtn.disabled = false;
        });
      });
    }

    modalActionButtons.forEach(function (actionBtn) {
      actionBtn.addEventListener('click', function () {
        var action = actionBtn.getAttribute('data-health-issue-action');
        if (!action || activeIssueId < 1) {
          return;
        }
        if (action === 'export') {
          var exportPayload = {
            issue_id: activeIssueId,
            exported_at: new Date().toISOString()
          };
          var blob = new Blob([JSON.stringify(exportPayload, null, 2)], { type: 'application/json' });
          var url = URL.createObjectURL(blob);
          var a = document.createElement('a');
          a.href = url;
          a.download = 'system-health-issue-' + activeIssueId + '.json';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(url);
          return;
        }
        var updateAction = action === 'ignore' ? 'ignore' : 'review';
        actionBtn.disabled = true;
        api('cmn_update_system_health_issue', {
          issue_id: activeIssueId,
          update_action: updateAction
        }).then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to update issue.');
          }
          closeModal();
          loadIssues();
        }).catch(function (err) {
          setMessage(err.message || 'Unable to update issue.', true);
        }).finally(function () {
          actionBtn.disabled = false;
        });
      });
    });

    Promise.all([loadRuns(), loadIssues(), loadFixes()]).catch(function () {
      setMessage('Unable to load system health data.', true);
    });
  }

  var candidateTourRoot = document.querySelector('[data-candidate-tour]');
  if (candidateTourRoot && candidateTourRoot.getAttribute('data-candidate-tour') === '1' && window.cmnPortal && window.cmnPortal.ajaxUrl) {
    var createTourElements = function () {
      var overlay = document.createElement('div');
      overlay.className = 'cmn-tour-overlay';
      var popover = document.createElement('div');
      popover.className = 'cmn-tour-popover';
      popover.innerHTML = '<div class="cmn-tour-avatar-wrap"><img class="cmn-tour-avatar" alt="CMN Guide"><div class="cmn-tour-avatar-fallback" aria-hidden="true">CMN</div></div><div class="cmn-tour-content"><h4></h4><p></p><div class="cmn-tour-actions"><button type="button" data-tour-back>Back</button><button type="button" data-tour-next>Next</button><button type="button" data-tour-skip>Skip</button><button type="button" data-tour-dismiss>Do not show again</button></div></div>';
      document.body.appendChild(overlay);
      document.body.appendChild(popover);
      var avatar = popover.querySelector('.cmn-tour-avatar');
      var fallback = popover.querySelector('.cmn-tour-avatar-fallback');
      if (avatar) {
        var avatarSrc = (window.cmnPortal && window.cmnPortal.candidateTourAvatar) ? window.cmnPortal.candidateTourAvatar : '';
        avatar.src = avatarSrc;
        if (!avatarSrc && fallback) {
          avatar.style.display = 'none';
          fallback.style.display = 'inline-flex';
        }
        avatar.addEventListener('load', function () {
          if (fallback) {
            fallback.style.display = 'none';
          }
        });
        avatar.addEventListener('error', function () {
          avatar.style.display = 'none';
          if (fallback) {
            fallback.style.display = 'inline-flex';
          }
        });
      }
      return { overlay: overlay, popover: popover };
    };

    var steps = [
      { key: 'bell', title: 'Notifications', text: 'Check this bell for updates and booking messages.' },
      { key: 'logout-top', title: 'Logout', text: 'Use this to safely sign out of your account.' },
      { key: 'availability-button', title: 'Availability Button', text: 'This is the most important action. Confirm your morning availability from 7pm until 8:00am.' },
      { key: 'upcoming-bookings', title: 'Upcoming Booking', text: 'Your next confirmed booking appears here.' },
      { key: 'booking-history', title: 'Booking History', text: 'Review your recent bookings quickly.' },
      { key: 'availability-planner', title: 'Availability Planner', text: 'Set Mon-Fri availability and use bulk range tools.' },
      { key: 'profile-documents', title: 'Profile & Documents', text: 'Keep your profile and uploads complete to improve visibility.' },
      { key: 'availability-summary', title: 'Availability Summary', text: 'This summary updates from your planner changes.' },
      { key: 'settings', nav: true, title: 'Settings', text: 'Use Settings to change theme colours and manage email notification toggles.' },
      { key: 'learning', nav: true, title: 'Learning Centre', text: 'Use Learning Centre for resources and training.' },
      { key: 'certificates', title: 'Certificates', text: 'Your completed certificates are managed here.' },
      { key: 'bookings', nav: true, title: 'Bookings Tab', text: 'Open Bookings for current and previous placements.' },
      { key: 'calendar', nav: true, title: 'Calendar Tab', text: 'Calendar explains day-by-day availability controls.' },
      { key: 'support', nav: true, title: 'Support Hub', text: 'Open a ticket whenever you need help.' },
      { key: 'logout-nav', title: 'Finish', text: 'You are ready to go. Logout is always available here.' }
    ];

    var ui = createTourElements();
    var titleEl = ui.popover.querySelector('h4');
    var textEl = ui.popover.querySelector('p');
    var backBtn = ui.popover.querySelector('[data-tour-back]');
    var nextBtn = ui.popover.querySelector('[data-tour-next]');
    var skipBtn = ui.popover.querySelector('[data-tour-skip]');
    var dismissBtn = ui.popover.querySelector('[data-tour-dismiss]');
    var currentIndex = -1;
    var highlighted = null;
    var repositionTimer = null;
    var scrollListenerAttached = false;

    var dismissTour = function (persist) {
      if (persist) {
        var payload = new FormData();
        payload.append('action', 'cmn_dismiss_candidate_tour');
        payload.append('nonce', window.cmnPortal.candidateTourNonce || '');
        fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: payload,
        });
      }
      if (highlighted) {
        highlighted.classList.remove('cmn-tour-highlight');
      }
      if (scrollListenerAttached) {
        window.removeEventListener('resize', handleViewportChange);
        window.removeEventListener('scroll', throttledViewportChange, true);
        scrollListenerAttached = false;
      }
      ui.overlay.remove();
      ui.popover.remove();
    };

    var resolveElement = function (step) {
      if (step.nav) {
        return document.querySelector('[data-candidate-nav="' + step.key + '"]');
      }
      return document.querySelector('[data-tour-target="' + step.key + '"]');
    };

    var clamp = function (value, min, max) {
      return Math.min(Math.max(value, min), max);
    };

    var getTooltipPosition = function (rect) {
      var margin = 12;
      var offset = 12;
      var vw = window.innerWidth;
      var vh = window.innerHeight;
      var scrollX = window.scrollX || window.pageXOffset || 0;
      var scrollY = window.scrollY || window.pageYOffset || 0;
      var popW = ui.popover.offsetWidth || 320;
      var popH = ui.popover.offsetHeight || 180;
      var placements = [
        { left: rect.right + offset, top: rect.bottom + offset },
        { left: rect.left - popW - offset, top: rect.bottom + offset },
        { left: rect.right + offset, top: rect.top - popH - offset },
        { left: rect.left - popW - offset, top: rect.top - popH - offset }
      ];

      for (var i = 0; i < placements.length; i += 1) {
        var p = placements[i];
        var fitsHoriz = p.left >= margin && (p.left + popW) <= (vw - margin);
        var fitsVert = p.top >= margin && (p.top + popH) <= (vh - margin);
        if (fitsHoriz && fitsVert) {
          return {
            left: p.left + scrollX,
            top: p.top + scrollY
          };
        }
      }

      return {
        left: clamp(((vw - popW) / 2), margin, vw - popW - margin) + scrollX,
        top: clamp(((vh - popH) / 2), margin, vh - popH - margin) + scrollY
      };
    };

    var placeTooltipForElement = function (element) {
      if (!element) {
        return;
      }
      var rect = element.getBoundingClientRect();
      var pos = getTooltipPosition(rect);
      ui.popover.style.left = Math.round(pos.left) + 'px';
      ui.popover.style.top = Math.round(pos.top) + 'px';
    };

    var schedulePlaceTooltip = function (element) {
      if (repositionTimer) {
        window.clearTimeout(repositionTimer);
      }
      repositionTimer = window.setTimeout(function () {
        placeTooltipForElement(element);
      }, 320);
    };

    var throttle = function (fn, wait) {
      var pending = false;
      return function () {
        if (pending) {
          return;
        }
        pending = true;
        window.setTimeout(function () {
          pending = false;
          fn();
        }, wait);
      };
    };

    var handleViewportChange = function () {
      if (!highlighted) {
        return;
      }
      placeTooltipForElement(highlighted);
    };
    var throttledViewportChange = throttle(handleViewportChange, 120);

    var showStep = function (index) {
      if (index < 0 || index >= steps.length) {
        dismissTour(false);
        return;
      }
      var direction = index >= currentIndex ? 1 : -1;
      var i = index;
      var step = null;
      var element = null;
      while (i >= 0 && i < steps.length) {
        step = steps[i];
        element = resolveElement(step);
        if (element) {
          break;
        }
        i += direction;
      }
      if (!element || !step) {
        dismissTour(false);
        return;
      }
      currentIndex = i;
      if (highlighted) {
        highlighted.classList.remove('cmn-tour-highlight');
      }
      highlighted = element;
      highlighted.classList.add('cmn-tour-highlight');
      titleEl.textContent = step.title;
      textEl.textContent = step.text;
      backBtn.disabled = currentIndex === 0;
      nextBtn.textContent = currentIndex === steps.length - 1 ? 'Finish' : 'Next';
      element.scrollIntoView({ behavior: 'smooth', block: 'center' });
      schedulePlaceTooltip(element);

      if (!scrollListenerAttached) {
        window.addEventListener('resize', handleViewportChange);
        window.addEventListener('scroll', throttledViewportChange, true);
        scrollListenerAttached = true;
      }
    };

    backBtn.addEventListener('click', function () {
      showStep(currentIndex - 1);
    });
    nextBtn.addEventListener('click', function () {
      showStep(currentIndex + 1);
    });
    skipBtn.addEventListener('click', function () {
      dismissTour(false);
    });
    dismissBtn.addEventListener('click', function () {
      dismissTour(true);
    });
    ui.overlay.addEventListener('click', function () {
      dismissTour(false);
    });
    showStep(0);
  }
});
