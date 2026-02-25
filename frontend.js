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

  var bindSchoolRegistrationSubmitGuard = function () {
    var forms = document.querySelectorAll('form.cmn-register-form');
    if (!forms.length) {
      return;
    }
    forms.forEach(function (form) {
      var actionField = form.querySelector('input[name="action"]');
      if (!actionField || actionField.value !== 'cmn_register_school') {
        return;
      }
      var submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
      if (!submitButton) {
        return;
      }
      form.addEventListener('submit', function (event) {
        if (form.getAttribute('data-cmn-submitting') === '1') {
          event.preventDefault();
          return;
        }
        form.setAttribute('data-cmn-submitting', '1');
        form.classList.add('is-submitting');
        if ('disabled' in submitButton) {
          submitButton.disabled = true;
        }
        if (submitButton.tagName && submitButton.tagName.toLowerCase() === 'input') {
          submitButton.value = 'Submitting...';
          return;
        }
        submitButton.textContent = 'Submitting...';
      });
    });
  };
  bindSchoolRegistrationSubmitGuard();

  var bindSchoolContactSearch = function () {
    var buttons = document.querySelectorAll('[data-cmn-contact-search]');
    if (!buttons.length) {
      return;
    }
    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        var form = button.closest('form');
        if (!form) {
          return;
        }
        var input = form.querySelector('.cmn-contact-search-input');
        var select = form.querySelector('[data-cmn-contact-results]');
        var status = form.querySelector('[data-cmn-contact-status]');
        var schoolDomain = button.getAttribute('data-cmn-contact-school') || '';
        var nonce = button.getAttribute('data-cmn-contact-nonce') || '';
        var ajaxUrl = button.getAttribute('data-cmn-contact-ajax') || '';
        var query = input ? String(input.value || '').trim() : '';
        if (!ajaxUrl || !nonce || !schoolDomain || query.length < 2) {
          if (status) {
            status.textContent = query.length < 2 ? 'Enter at least 2 characters.' : 'Unable to search.';
          }
          return;
        }
        if (status) {
          status.textContent = 'Searching...';
        }
        if ('disabled' in button) {
          button.disabled = true;
        }
        var payload = new URLSearchParams();
        payload.append('action', 'cmn_school_contact_search');
        payload.append('nonce', nonce);
        payload.append('school_domain', schoolDomain);
        payload.append('query', query);
        fetch(ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          },
          body: payload.toString(),
        })
          .then(function (response) {
            return response.json();
          })
          .then(function (data) {
            if (!select) {
              return;
            }
            var results = data && data.success && data.data ? data.data.results || [] : [];
            select.innerHTML = '';
            if (!results.length) {
              var empty = document.createElement('option');
              empty.value = '';
              empty.textContent = 'No contacts found.';
              select.appendChild(empty);
            } else {
              var placeholder = document.createElement('option');
              placeholder.value = '';
              placeholder.textContent = 'Select contact';
              select.appendChild(placeholder);
              results.forEach(function (row) {
                var option = document.createElement('option');
                option.value = row.id;
                option.textContent = row.label;
                select.appendChild(option);
              });
            }
            if (status) {
              status.textContent = results.length ? results.length + ' contact(s) found.' : 'No contacts found.';
            }
          })
          .catch(function () {
            if (status) {
              status.textContent = 'Search failed. Try again.';
            }
          })
          .finally(function () {
            if ('disabled' in button) {
              button.disabled = false;
            }
          });
      });
    });
  };
  bindSchoolContactSearch();

  var initCandidateNoteCounter = function () {
    var noteInputs = document.querySelectorAll('[data-candidate-note-input]');
    if (!noteInputs.length) {
      return;
    }
    noteInputs.forEach(function (input) {
      var form = input.closest('form');
      var counter = form ? form.querySelector('[data-candidate-note-counter]') : null;
      if (!counter) {
        return;
      }
      var updateCounter = function () {
        var value = String(input.value || '');
        var count = value.length;
        counter.textContent = count + (count === 1 ? ' character' : ' characters');
      };
      input.addEventListener('input', updateCounter);
      updateCounter();
    });
  };
  initCandidateNoteCounter();

  var initCandidateDocDeleteModal = function () {
    var modal = document.querySelector('[data-cmn-doc-delete-modal]');
    if (!modal) {
      return;
    }
    var openButtons = document.querySelectorAll('[data-cmn-doc-delete-open]');
    if (!openButtons.length) {
      return;
    }
    var cancelButtons = modal.querySelectorAll('[data-cmn-doc-delete-cancel]');
    var confirmButton = modal.querySelector('[data-cmn-doc-delete-confirm]');
    var activeForm = null;

    var closeModal = function () {
      modal.hidden = true;
      activeForm = null;
      document.body.classList.remove('cmn-support-modal-lock');
    };

    var openModal = function (form) {
      if (!form) {
        return;
      }
      activeForm = form;
      modal.hidden = false;
      document.body.classList.add('cmn-support-modal-lock');
    };

    openButtons.forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        var form = button.closest('form[data-cmn-doc-delete-form]');
        openModal(form);
      });
    });

    cancelButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        closeModal();
      });
    });

    modal.addEventListener('click', function (event) {
      if (event.target === modal) {
        closeModal();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !modal.hidden) {
        closeModal();
      }
    });

    if (confirmButton) {
      confirmButton.addEventListener('click', function () {
        if (!activeForm) {
          closeModal();
          return;
        }
        activeForm.submit();
        closeModal();
      });
    }
  };
  initCandidateDocDeleteModal();

  var initSchoolProfileControlPanel = function () {
    var profileRoot = document.querySelector('[data-school-profile-root]');
    if (!profileRoot) {
      return;
    }
    var tabNav = document.querySelector('[data-school-profile-tabs]');
    var tabLinks = tabNav ? Array.prototype.slice.call(tabNav.querySelectorAll('[data-school-profile-tab]')) : [];
    var quickEditPanel = profileRoot.querySelector('[data-school-quick-edit-panel]');
    var quickEditToggles = Array.prototype.slice.call(document.querySelectorAll('[data-school-quick-edit-toggle]'));
    var quickEditCancel = profileRoot.querySelector('[data-school-quick-edit-cancel]');
    var activityTypeField = profileRoot.querySelector('[data-school-activity-type]');
    var activityTitleField = profileRoot.querySelector('[data-school-activity-title]');
    var activityPresetButtons = Array.prototype.slice.call(profileRoot.querySelectorAll('[data-school-activity-preset]'));
    var activityFilter = profileRoot.querySelector('[data-school-activity-filter]');
    var activityRows = Array.prototype.slice.call(profileRoot.querySelectorAll('[data-school-activity-item]'));
    var logActivityButtons = Array.prototype.slice.call(document.querySelectorAll('[data-school-log-activity]'));
    var feedbackJumpLinks = Array.prototype.slice.call(document.querySelectorAll('[data-school-feedback-jump]'));
    var feedbackPanel = profileRoot.querySelector('[data-school-feedback-panel]');
    var initialUrl = null;
    try {
      initialUrl = new URL(window.location.href);
    } catch (error) {
      initialUrl = null;
    }

    var syncQuickEditState = function (isOpen) {
      if (quickEditPanel) {
        quickEditPanel.hidden = !isOpen;
      }
      quickEditToggles.forEach(function (btn) {
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
      if (isOpen) {
        var focusField = quickEditPanel ? quickEditPanel.querySelector('input:not([type="hidden"]), select, textarea') : null;
        if (focusField && typeof focusField.focus === 'function') {
          focusField.focus();
        }
      }
    };

    var applyTabState = function (nextTab, updateUrl) {
      var target = String(nextTab || '').trim() || 'overview';
      profileRoot.setAttribute('data-school-active-tab', target);
      tabLinks.forEach(function (link) {
        var linkTab = link.getAttribute('data-school-profile-tab') || '';
        link.classList.toggle('is-active', linkTab === target);
      });
      if (target !== 'overview') {
        syncQuickEditState(false);
      }
      if (!updateUrl) {
        return;
      }
      var url = null;
      try {
        url = new URL(window.location.href);
      } catch (error) {
        return;
      }
      url.searchParams.set('cmn_school_tab', target);
      if (target !== 'activity') {
        url.searchParams.delete('cmn_focus_activity');
        url.searchParams.delete('cmn_focus_feedback');
      }
      window.history.replaceState({}, '', url.toString());
    };

    var focusActivityForm = function () {
      applyTabState('activity', false);
      if (activityTitleField && typeof activityTitleField.focus === 'function') {
        activityTitleField.focus();
      }
    };

    var focusFeedbackPanel = function () {
      applyTabState('activity', false);
      if (feedbackPanel && typeof feedbackPanel.scrollIntoView === 'function') {
        feedbackPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    };

    tabLinks.forEach(function (link) {
      link.addEventListener('click', function (event) {
        event.preventDefault();
        var targetTab = link.getAttribute('data-school-profile-tab') || 'overview';
        applyTabState(targetTab, true);
      });
    });

    quickEditToggles.forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        var willOpen = !quickEditPanel || quickEditPanel.hidden;
        applyTabState('overview', true);
        syncQuickEditState(willOpen);
      });
    });

    if (quickEditCancel) {
      quickEditCancel.addEventListener('click', function () {
        syncQuickEditState(false);
      });
    }

    activityPresetButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        var nextType = button.getAttribute('data-school-activity-preset') || 'note';
        if (activityTypeField) {
          activityTypeField.value = nextType;
        }
        focusActivityForm();
      });
    });

    if (activityFilter && activityRows.length) {
      var applyActivityFilter = function () {
        var selected = String(activityFilter.value || 'all').toLowerCase();
        activityRows.forEach(function (row) {
          var category = String(row.getAttribute('data-activity-category') || 'notes').toLowerCase();
          row.style.display = (selected === 'all' || selected === category) ? '' : 'none';
        });
      };
      activityFilter.addEventListener('change', applyActivityFilter);
      applyActivityFilter();
    }

    logActivityButtons.forEach(function (button) {
      button.addEventListener('click', function (event) {
        event.preventDefault();
        applyTabState('activity', true);
        var url = null;
        try {
          url = new URL(window.location.href);
        } catch (error) {
          url = null;
        }
        if (url) {
          url.searchParams.set('cmn_school_tab', 'activity');
          url.searchParams.set('cmn_focus_activity', '1');
          url.hash = 'cmn-school-add-activity';
          window.history.replaceState({}, '', url.toString());
        }
        focusActivityForm();
      });
    });

    feedbackJumpLinks.forEach(function (link) {
      link.addEventListener('click', function (event) {
        event.preventDefault();
        applyTabState('activity', true);
        var url = null;
        try {
          url = new URL(window.location.href);
        } catch (error) {
          url = null;
        }
        if (url) {
          url.searchParams.set('cmn_school_tab', 'activity');
          url.searchParams.set('cmn_focus_feedback', '1');
          url.hash = 'cmn-school-feedback-panel';
          window.history.replaceState({}, '', url.toString());
        }
        focusFeedbackPanel();
      });
    });

    if (initialUrl) {
      var initialTab = initialUrl.searchParams.get('cmn_school_tab') || profileRoot.getAttribute('data-school-active-tab') || 'overview';
      applyTabState(initialTab, false);
      if (initialUrl.hash === '#cmn-school-quick-edit-panel') {
        applyTabState('overview', false);
        syncQuickEditState(true);
      }
      if (initialUrl.searchParams.get('cmn_focus_activity') === '1' || initialUrl.hash === '#cmn-school-add-activity') {
        window.setTimeout(function () {
          focusActivityForm();
        }, 20);
      }
      if (initialUrl.searchParams.get('cmn_focus_feedback') === '1' || initialUrl.hash === '#cmn-school-feedback-panel') {
        window.setTimeout(function () {
          focusFeedbackPanel();
        }, 20);
      }
    }
  };
  initSchoolProfileControlPanel();

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

  var ensurePortalOverlayRoot = function () {
    if (!document.body || !document.body.classList.contains('cmn-portal-page')) {
      return null;
    }
    var root = document.getElementById('cmn-portal-overlay-root');
    if (!root) {
      root = document.createElement('div');
      root.id = 'cmn-portal-overlay-root';
      root.setAttribute('aria-live', 'polite');
      root.setAttribute('aria-atomic', 'true');
    }
    if (root.parentNode !== document.body) {
      document.body.appendChild(root);
    }
    return root;
  };
  var portalOverlayRoot = ensurePortalOverlayRoot();

  var cmnPollManager = (function () {
    var tasks = {};
    var globalEventsBound = false;

    var clearTaskTimer = function (task) {
      if (!task || !task.timer) {
        return;
      }
      window.clearTimeout(task.timer);
      task.timer = null;
    };

    var getTaskDelay = function (task) {
      if (!task || !task.backoffOnError || task.errorCount < 1) {
        return task && task.intervalMs ? task.intervalMs : 5000;
      }
      var factor = Math.pow(2, Math.max(0, task.errorCount - 1));
      return Math.min(task.maxIntervalMs || 120000, Math.round(task.intervalMs * factor));
    };

    var scheduleTask = function (task) {
      if (!task || task.destroyed) {
        return;
      }
      if (task.visibleOnly && document.hidden) {
        clearTaskTimer(task);
        return;
      }
      clearTaskTimer(task);
      task.timer = window.setTimeout(function () {
        runTask(task, 'interval');
      }, Math.max(500, getTaskDelay(task)));
    };

    var runTask = function (task, reason) {
      if (!task || task.destroyed) {
        return Promise.resolve(false);
      }
      if (task.visibleOnly && document.hidden) {
        scheduleTask(task);
        return Promise.resolve(false);
      }
      if (task.inFlight) {
        return Promise.resolve(false);
      }
      task.inFlight = true;
      var runResult = null;
      try {
        runResult = task.callback(reason || 'manual');
      } catch (error) {
        runResult = Promise.reject(error);
      }
      return Promise.resolve(runResult)
        .then(function () {
          task.errorCount = 0;
          return true;
        })
        .catch(function () {
          if (task.backoffOnError) {
            task.errorCount = Math.min(task.errorCount + 1, 8);
          }
          return false;
        })
        .finally(function () {
          task.inFlight = false;
          scheduleTask(task);
        });
    };

    var register = function (options) {
      if (!options || !options.key || typeof options.callback !== 'function') {
        return null;
      }
      var key = String(options.key);
      if (tasks[key]) {
        tasks[key].destroyed = true;
        clearTaskTimer(tasks[key]);
        delete tasks[key];
      }
      var task = {
        key: key,
        callback: options.callback,
        intervalMs: Math.max(500, parseInt(options.intervalMs || 5000, 10) || 5000),
        maxIntervalMs: Math.max(1000, parseInt(options.maxIntervalMs || 120000, 10) || 120000),
        visibleOnly: options.visibleOnly !== false,
        triggerOnFocus: options.triggerOnFocus !== false,
        triggerOnVisibility: options.triggerOnVisibility !== false,
        backoffOnError: options.backoffOnError !== false,
        immediate: options.immediate !== false,
        timer: null,
        inFlight: false,
        errorCount: 0,
        destroyed: false,
      };
      tasks[key] = task;
      if (task.immediate && !(task.visibleOnly && document.hidden)) {
        runTask(task, 'init');
      } else if (!(task.visibleOnly && document.hidden)) {
        scheduleTask(task);
      }
      return key;
    };

    var unregister = function (key) {
      var normalized = String(key || '');
      if (!normalized || !tasks[normalized]) {
        return;
      }
      tasks[normalized].destroyed = true;
      clearTaskTimer(tasks[normalized]);
      delete tasks[normalized];
    };

    var trigger = function (key, reason) {
      var normalized = String(key || '');
      if (!normalized || !tasks[normalized]) {
        return Promise.resolve(false);
      }
      return runTask(tasks[normalized], reason || 'manual');
    };

    var bindGlobalEvents = function () {
      if (globalEventsBound) {
        return;
      }
      globalEventsBound = true;

      document.addEventListener('visibilitychange', function () {
        var isHidden = document.hidden;
        Object.keys(tasks).forEach(function (key) {
          var task = tasks[key];
          if (!task || task.destroyed) {
            return;
          }
          if (isHidden) {
            if (task.visibleOnly) {
              clearTaskTimer(task);
            }
            return;
          }
          if (task.triggerOnVisibility) {
            runTask(task, 'visibility');
            return;
          }
          scheduleTask(task);
        });
      });

      window.addEventListener('focus', function () {
        Object.keys(tasks).forEach(function (key) {
          var task = tasks[key];
          if (!task || task.destroyed || !task.triggerOnFocus) {
            return;
          }
          runTask(task, 'focus');
        });
      });
    };

    bindGlobalEvents();

    return {
      register: register,
      unregister: unregister,
      trigger: trigger,
    };
  })();

  var staffNav = document.querySelector('[data-staff-nav]');
  if (staffNav) {
    var staffViewKey = 'dashboard';
    try {
      var staffViewParams = new URLSearchParams(window.location.search || '');
      staffViewKey = String(staffViewParams.get('view') || 'dashboard').trim() || 'dashboard';
    } catch (e) {
      staffViewKey = 'dashboard';
    }
    var staffNavUserId = staffNav.getAttribute('data-user-id') || '0';
    var staffNavStorageKey = 'cmn_staff_nav_state_v2_' + staffNavUserId;
    var staffNavCompactKey = 'cmn_staff_nav_compact_v1_' + staffNavUserId;
    var staffNavEditModeLegacyKey = 'cmn_sidebar_edit_mode';
    var staffNavEditModeKey = 'cmn_sidebar_edit_mode_' + staffViewKey;
    var staffNavExpandedGroupKey = 'cmn_staff_nav_expanded_group_v1_' + staffNavUserId;
    var staffNavGroupStateKey = 'cmn_staff_nav_group_state_v1_' + staffNavUserId;
    var staffNavScrollKey = 'cmn_staff_nav_scroll_v1_' + staffNavUserId;
    var staffNavScrollParentKey = 'cmn_staff_nav_scroll_parent_v1_' + staffNavUserId;
    var staffNavScrollMapKey = 'cmn_staff_nav_scroll_map_v2_' + staffNavUserId;
    var staffMainScrollPayloadKey = 'cmn_staff_main_scroll_payload_v1_' + staffNavUserId;
    var staffMainScrollMapKey = 'cmn_staff_main_scroll_map_v2_' + staffNavUserId;
    var staffShell = staffNav.closest('.cmn-staff-shell');
    var staffMainScrollHost = null;
    var staffNavMinimizeBtn = staffNav.querySelector('[data-staff-nav-minimize]');
    var staffNavEditToggleBtn = staffNav.querySelector('[data-staff-nav-edit-toggle]');
    var staffNavEditPanel = staffNav.querySelector('[data-staff-nav-edit-panel]');
    var staffNavEditCancelBtn = staffNav.querySelector('[data-staff-nav-edit-cancel]');
    var staffNavEditResetBtn = staffNav.querySelector('[data-staff-nav-edit-reset]');
    var staffNavEditSaveBtn = staffNav.querySelector('[data-staff-nav-edit-save]');
    var staffNavEditDiscardPanel = staffNav.querySelector('[data-staff-nav-edit-discard]');
    var staffNavEditDiscardConfirmBtn = staffNav.querySelector('[data-staff-nav-edit-discard-confirm]');
    var staffNavEditDiscardKeepBtn = staffNav.querySelector('[data-staff-nav-edit-discard-keep]');
    var staffNavPeekOpen = false;
    var isNavEditing = false;
    var navOrderDirty = false;
    var navOrderSaveInFlight = false;
    var staffMainScrollIntentSet = false;
    var staffNavActiveAutoScrollDone = false;
    var staffInitialScrollHydrationComplete = false;
    staffNav.classList.add('is-nav-hydrating');
    var dragType = '';
    var dragNode = null;
    var dragGroupKey = '';

    var cloneJson = function (value, fallback) {
      try {
        return JSON.parse(JSON.stringify(value));
      } catch (e) {
        return fallback;
      }
    };
    var readNavOrderAttr = function (attrName) {
      var raw = staffNav.getAttribute(attrName) || '';
      if (!raw) {
        return { groups: [], items: {} };
      }
      try {
        var parsed = JSON.parse(raw);
        if (!parsed || typeof parsed !== 'object') {
          return { groups: [], items: {} };
        }
        return parsed;
      } catch (e) {
        return { groups: [], items: {} };
      }
    };
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
    var collectNavOrderFromDom = function () {
      var order = { groups: [], items: {} };
      staffNav.querySelectorAll('[data-staff-nav-group]').forEach(function (groupEl) {
        var groupKey = String(groupEl.getAttribute('data-staff-nav-group') || '').trim();
        if (!groupKey) {
          return;
        }
        order.groups.push(groupKey);
        order.items[groupKey] = [];
        groupEl.querySelectorAll('[data-staff-nav-item]').forEach(function (itemEl) {
          var itemKey = String(itemEl.getAttribute('data-staff-nav-item-key') || '').trim();
          if (!itemKey) {
            return;
          }
          order.items[groupKey].push(itemKey);
        });
      });
      return order;
    };
    var normalizeNavOrder = function (order, defaults) {
      var normalized = { groups: [], items: {} };
      var base = defaults && typeof defaults === 'object' ? defaults : { groups: [], items: {} };
      var baseGroups = Array.isArray(base.groups) ? base.groups.map(function (groupKey) {
        return String(groupKey || '').trim();
      }).filter(Boolean) : [];
      var requestedGroups = order && Array.isArray(order.groups) ? order.groups : [];
      var seenGroups = {};

      requestedGroups.forEach(function (groupKey) {
        groupKey = String(groupKey || '').trim();
        if (!groupKey || seenGroups[groupKey] || baseGroups.indexOf(groupKey) === -1) {
          return;
        }
        normalized.groups.push(groupKey);
        seenGroups[groupKey] = true;
      });
      baseGroups.forEach(function (groupKey) {
        if (!seenGroups[groupKey]) {
          normalized.groups.push(groupKey);
          seenGroups[groupKey] = true;
        }
      });

      normalized.groups.forEach(function (groupKey) {
        var baseItems = Array.isArray(base.items && base.items[groupKey]) ? base.items[groupKey].map(function (itemKey) {
          return String(itemKey || '').trim();
        }).filter(Boolean) : [];
        var requestedItems = Array.isArray(order && order.items && order.items[groupKey]) ? order.items[groupKey] : [];
        var seenItems = {};
        normalized.items[groupKey] = [];
        requestedItems.forEach(function (itemKey) {
          itemKey = String(itemKey || '').trim();
          if (!itemKey || seenItems[itemKey] || baseItems.indexOf(itemKey) === -1) {
            return;
          }
          normalized.items[groupKey].push(itemKey);
          seenItems[itemKey] = true;
        });
        baseItems.forEach(function (itemKey) {
          if (!seenItems[itemKey]) {
            normalized.items[groupKey].push(itemKey);
            seenItems[itemKey] = true;
          }
        });
      });

      return normalized;
    };
    var orderFingerprint = function (order) {
      return JSON.stringify(order || { groups: [], items: {} });
    };
    var applyNavOrderToDom = function (order) {
      var navLinks = staffNav.querySelector('.cmn-staff-nav-links');
      if (!navLinks) {
        return;
      }
      var currentOrder = collectNavOrderFromDom();
      var normalized = normalizeNavOrder(order, currentOrder);
      var groupLookup = {};
      navLinks.querySelectorAll('[data-staff-nav-group]').forEach(function (groupEl) {
        var key = String(groupEl.getAttribute('data-staff-nav-group') || '').trim();
        if (key) {
          groupLookup[key] = groupEl;
        }
      });

      normalized.groups.forEach(function (groupKey) {
        var groupEl = groupLookup[groupKey];
        if (!groupEl) {
          return;
        }
        navLinks.appendChild(groupEl);
        var bodyEl = groupEl.querySelector('[data-staff-nav-body="' + groupKey + '"]');
        if (!bodyEl) {
          return;
        }
        var itemLookup = {};
        bodyEl.querySelectorAll('[data-staff-nav-item]').forEach(function (itemEl) {
          var itemKey = String(itemEl.getAttribute('data-staff-nav-item-key') || '').trim();
          if (itemKey) {
            itemLookup[itemKey] = itemEl;
          }
        });
        var orderedItems = normalized.items[groupKey] || [];
        orderedItems.forEach(function (itemKey) {
          if (itemLookup[itemKey]) {
            bodyEl.appendChild(itemLookup[itemKey]);
            delete itemLookup[itemKey];
          }
        });
        Object.keys(itemLookup).forEach(function (itemKey) {
          bodyEl.appendChild(itemLookup[itemKey]);
        });
      });
    };

    var navOrderDefault = normalizeNavOrder(readNavOrderAttr('data-nav-order-default'), collectNavOrderFromDom());
    var navOrderSaved = normalizeNavOrder(readNavOrderAttr('data-nav-order'), navOrderDefault);
    applyNavOrderToDom(navOrderSaved);
    var navOrderBaseline = cloneJson(navOrderSaved, navOrderDefault);

    var readStaffNavState = function () {
      try {
        var raw = window.sessionStorage.getItem(staffNavStorageKey);
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
        window.sessionStorage.setItem(staffNavStorageKey, JSON.stringify(state || {}));
      } catch (e) {
        // Ignore storage failures.
      }
    };
    var readStaffNavCompactState = function () {
      try {
        return window.sessionStorage.getItem(staffNavCompactKey) === '1';
      } catch (e) {
        return false;
      }
    };
    var writeStaffNavCompactState = function (isCompact) {
      try {
        window.sessionStorage.setItem(staffNavCompactKey, isCompact ? '1' : '0');
      } catch (e) {
        // Ignore storage failures.
      }
    };
    var readStaffNavEditModeState = function () {
      var fallback = { open: false, dirty: false };
      try {
        var raw = window.localStorage.getItem(staffNavEditModeKey) || '';
        if (raw === '') {
          raw = window.localStorage.getItem(staffNavEditModeLegacyKey) || '';
        }
        if (raw === '') {
          return fallback;
        }
        if (raw === '1') {
          return { open: true, dirty: false };
        }
        if (raw === '0') {
          return fallback;
        }
        var parsed = JSON.parse(raw);
        return {
          open: !!(parsed && Number(parsed.open || 0) === 1),
          dirty: !!(parsed && Number(parsed.dirty || 0) === 1)
        };
      } catch (e) {
        return fallback;
      }
    };
    var writeStaffNavEditModeState = function (isOpen, isDirty) {
      try {
        window.localStorage.setItem(staffNavEditModeKey, JSON.stringify({
          open: isOpen ? 1 : 0,
          dirty: isDirty ? 1 : 0
        }));
        window.localStorage.removeItem(staffNavEditModeLegacyKey);
      } catch (e) {
        // Ignore storage failures.
      }
    };
    var readStorageString = function (storage, key) {
      try {
        return String(storage.getItem(key) || '');
      } catch (e) {
        return '';
      }
    };
    var writeStorageString = function (storage, key, value) {
      try {
        if (value === null || value === undefined || value === '') {
          storage.removeItem(key);
          return;
        }
        storage.setItem(key, String(value));
      } catch (e) {
        // Ignore storage failures.
      }
    };
    var readStorageJson = function (storage, key, fallback) {
      var fallbackValue = (fallback && typeof fallback === 'object') ? fallback : {};
      var raw = readStorageString(storage, key);
      if (!raw) {
        return cloneJson(fallbackValue, {});
      }
      try {
        var parsed = JSON.parse(raw);
        if (!parsed || typeof parsed !== 'object') {
          return cloneJson(fallbackValue, {});
        }
        return parsed;
      } catch (e) {
        return cloneJson(fallbackValue, {});
      }
    };
    var writeStorageJson = function (storage, key, value) {
      try {
        if (!value || typeof value !== 'object') {
          storage.removeItem(key);
          return;
        }
        storage.setItem(key, JSON.stringify(value));
      } catch (e) {
        // Ignore storage failures.
      }
    };
    var readStaffExpandedGroupState = function () {
      return readStorageString(window.localStorage, staffNavExpandedGroupKey).trim();
    };
    var writeStaffExpandedGroupState = function (groupKey) {
      writeStorageString(window.localStorage, staffNavExpandedGroupKey, String(groupKey || '').trim());
    };
    var readStaffGroupOpenState = function () {
      return readStorageJson(window.localStorage, staffNavGroupStateKey, {});
    };
    var writeStaffGroupOpenState = function (state) {
      writeStorageJson(window.localStorage, staffNavGroupStateKey, state && typeof state === 'object' ? state : {});
    };
    var readCurrentStaffSearchParams = function () {
      try {
        return new URLSearchParams(window.location.search || '');
      } catch (e) {
        return new URLSearchParams();
      }
    };
    var getLowerQueryValue = function (params, key) {
      if (!params || typeof params.get !== 'function') {
        return '';
      }
      return String(params.get(key) || '').trim().toLowerCase();
    };
    var normalizeRouteValue = function (key, value) {
      var normalizedKey = String(key || '').trim().toLowerCase();
      var normalizedValue = String(value || '').trim().toLowerCase();
      if (!normalizedValue) {
        return '';
      }
      if (normalizedKey === 'view') {
        if (normalizedValue === 'finance_overview') {
          return 'finance-overview';
        }
        if (normalizedValue === 'invoices') {
          return 'invoicing';
        }
        return normalizedValue.replace(/_/g, '-');
      }
      if (normalizedKey === 'cmn_tab') {
        return normalizedValue.replace(/-/g, '_');
      }
      return normalizedValue;
    };
    var routeMatchKeys = [
      'cmn_tab',
      'view',
      'cmn_candidate_tab',
      'cmn_school_tab',
      'school',
      'candidate',
      'candidate_id',
      'cmn_status',
      'support_filter',
      'status',
      'marketing_tab',
      'cmn_email_centre_tab',
      'cmn_intelligence',
      'cmn_finance_focus',
      'cmn_doc_review',
      'cmn_bucket'
    ];
    var routeKeyQueryOrder = [
      'view',
      'cmn_tab',
      'cmn_candidate_tab',
      'cmn_school_tab',
      'school',
      'candidate',
      'candidate_id',
      'cmn_status',
      'support_filter',
      'status',
      'cmn_finance_focus',
      'marketing_tab',
      'cmn_email_centre_tab',
      'cmn_intelligence',
      'cmn_doc_review',
      'cmn_bucket',
      'tab'
    ];
    var getRouteKey = function (params) {
      var source = params && typeof params.get === 'function' ? params : readCurrentStaffSearchParams();
      var parts = [];
      routeKeyQueryOrder.forEach(function (routeKey) {
        var value = normalizeRouteValue(routeKey, getLowerQueryValue(source, routeKey));
        if (!value) {
          return;
        }
        parts.push(routeKey + '=' + encodeURIComponent(value));
      });
      if (!parts.length) {
        return 'dashboard';
      }
      return parts.join('&');
    };
    var getRouteKeyFromUrl = function (url) {
      try {
        var parsed = new URL(String(url || ''), window.location.href);
        return getRouteKey(parsed.searchParams);
      } catch (e) {
        return getRouteKey(readCurrentStaffSearchParams());
      }
    };
    var resolveMajorRouteKeyFromParams = function (params) {
      var view = normalizeRouteValue('view', getLowerQueryValue(params, 'view'));
      if (view) {
        return 'view:' + view;
      }
      var tab = normalizeRouteValue('cmn_tab', getLowerQueryValue(params, 'cmn_tab'));
      if (tab) {
        return 'tab:' + tab;
      }
      var school = normalizeRouteValue('school', getLowerQueryValue(params, 'school'));
      if (school) {
        return 'school:' + school;
      }
      return 'dashboard';
    };
    var resolveMajorRouteKeyFromUrl = function (url) {
      try {
        var parsed = new URL(String(url || ''), window.location.href);
        return String(parsed.pathname || '').replace(/\/+$/, '') + '|' + resolveMajorRouteKeyFromParams(parsed.searchParams);
      } catch (e) {
        return String(window.location.pathname || '').replace(/\/+$/, '') + '|' + resolveMajorRouteKeyFromParams(readCurrentStaffSearchParams());
      }
    };
    var resolveRouteMappedNavItemKey = function (params) {
      var view = normalizeRouteValue('view', getLowerQueryValue(params, 'view'));
      if (view === 'finance-overview') {
        return 'finance_overview';
      }
      if (view === 'invoicing') {
        return 'invoicing';
      }
      var tab = normalizeRouteValue('cmn_tab', getLowerQueryValue(params, 'cmn_tab'));
      if (tab === 'staff_payroll') {
        return 'staff_payroll';
      }
      return '';
    };
    var resolveDynamicActiveNavItemKey = function (params, pathnameOverride) {
      var currentParams = params || readCurrentStaffSearchParams();
      var currentPath = String(pathnameOverride || window.location.pathname || '').replace(/\/+$/, '');
      var best = { key: '', score: -1 };
      staffNav.querySelectorAll('[data-staff-nav-item]').forEach(function (itemEl) {
        var itemKey = String(itemEl.getAttribute('data-staff-nav-item-key') || '').trim();
        if (!itemKey) {
          return;
        }
        var linkEl = itemEl.querySelector('.cmn-school-nav-link[href]');
        if (!linkEl) {
          return;
        }
        var parsedHref;
        try {
          parsedHref = new URL(String(linkEl.getAttribute('href') || ''), window.location.href);
        } catch (e) {
          return;
        }
        var hrefPath = String(parsedHref.pathname || '').replace(/\/+$/, '');
        if (hrefPath !== currentPath) {
          return;
        }

        var score = 0;
        var matched = 0;
        var mismatch = false;
        routeMatchKeys.forEach(function (routeKey) {
          var linkValue = normalizeRouteValue(routeKey, getLowerQueryValue(parsedHref.searchParams, routeKey));
          if (!linkValue) {
            return;
          }
          var currentValue = normalizeRouteValue(routeKey, getLowerQueryValue(currentParams, routeKey));
          if (!currentValue || currentValue !== linkValue) {
            mismatch = true;
            return;
          }
          matched++;
          if (routeKey === 'cmn_tab' || routeKey === 'view' || routeKey === 'school' || routeKey === 'candidate') {
            score += 40;
            return;
          }
          score += 10;
        });
        if (mismatch || matched < 1) {
          return;
        }
        if (score > best.score) {
          best = { key: itemKey, score: score };
        }
      });
      return String(best.key || '').trim();
    };
    var findStaffNavItemByKey = function (targetKey) {
      var needle = String(targetKey || '').trim();
      if (!needle) {
        return null;
      }
      var found = null;
      staffNav.querySelectorAll('[data-staff-nav-item]').forEach(function (itemEl) {
        if (found) {
          return;
        }
        var itemKey = String(itemEl.getAttribute('data-staff-nav-item-key') || '').trim();
        if (itemKey === needle) {
          found = itemEl;
        }
      });
      return found;
    };
    var getFirstActiveGroupKey = function () {
      var activeGroupKey = '';
      staffNav.querySelectorAll('[data-staff-nav-group]').forEach(function (groupEl) {
        if (activeGroupKey) {
          return;
        }
        if (!groupEl.querySelector('.cmn-school-nav-link.is-active')) {
          return;
        }
        activeGroupKey = String(groupEl.getAttribute('data-staff-nav-group') || '').trim();
      });
      return activeGroupKey;
    };
    var resolveCurrentNavParentGroupKey = function () {
      var activeGroupKey = getFirstActiveGroupKey();
      if (activeGroupKey) {
        return activeGroupKey;
      }
      return readStaffExpandedGroupState();
    };
    var syncStaffNavGroupActiveState = function (preferredGroupKey) {
      var activeGroupKey = String(preferredGroupKey || '').trim();
      if (!activeGroupKey) {
        activeGroupKey = getFirstActiveGroupKey();
      }
      staffNav.querySelectorAll('[data-staff-nav-group]').forEach(function (groupEl) {
        var groupKey = String(groupEl.getAttribute('data-staff-nav-group') || '').trim();
        var isActiveGroup = !!activeGroupKey && groupKey === activeGroupKey;
        if (!activeGroupKey) {
          isActiveGroup = !!groupEl.querySelector('.cmn-school-nav-link.is-active');
        }
        groupEl.classList.toggle('is-active-group', isActiveGroup);
      });
      return activeGroupKey;
    };
    var triggerStaffNavActiveAnimation = function () {
      var activeLink = staffNav.querySelector('.cmn-school-nav-link.is-active');
      if (!activeLink) {
        return;
      }
      activeLink.classList.remove('cmn-nav-active-animate');
      // Trigger reflow so class re-application animates on load.
      void activeLink.offsetWidth;
      activeLink.classList.add('cmn-nav-active-animate');
      window.setTimeout(function () {
        activeLink.classList.remove('cmn-nav-active-animate');
      }, 180);
    };
    var applyActiveItemByKey = function (targetKey) {
      var itemEl = findStaffNavItemByKey(targetKey);
      if (!itemEl) {
        return '';
      }
      staffNav.querySelectorAll('.cmn-school-nav-link.is-active').forEach(function (activeLinkEl) {
        activeLinkEl.classList.remove('is-active');
      });
      var itemLinkEl = itemEl.querySelector('.cmn-school-nav-link');
      if (itemLinkEl) {
        itemLinkEl.classList.add('is-active');
      }
      return String(itemEl.getAttribute('data-staff-nav-item-group') || '').trim();
    };
    var getNavGroupKeyForLink = function (linkEl) {
      if (!linkEl) {
        return '';
      }
      var itemEl = linkEl.closest('[data-staff-nav-item]');
      if (!itemEl) {
        return '';
      }
      return String(itemEl.getAttribute('data-staff-nav-item-group') || '').trim();
    };
    var normalizePathname = function (pathname) {
      var normalized = String(pathname || '').trim();
      if (!normalized) {
        return '/';
      }
      normalized = normalized.replace(/\/+$/, '');
      return normalized || '/';
    };
    var isPlainLeftClick = function (event) {
      if (!event) {
        return false;
      }
      return !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey && Number(event.button || 0) === 0;
    };
    var isInternalPortalNavigationUrl = function (url) {
      try {
        var parsed = new URL(String(url || ''), window.location.href);
        if (parsed.origin !== window.location.origin) {
          return false;
        }
        return normalizePathname(parsed.pathname) === normalizePathname(window.location.pathname);
      } catch (e) {
        return false;
      }
    };
    var isInternalPortalReferrer = function () {
      var referrer = String(document.referrer || '').trim();
      if (!referrer) {
        return false;
      }
      try {
        var parsed = new URL(referrer, window.location.href);
        if (parsed.origin !== window.location.origin) {
          return false;
        }
        return normalizePathname(parsed.pathname) === normalizePathname(window.location.pathname);
      } catch (e) {
        return false;
      }
    };
    var shouldRestoreStoredScrollState = function (hasPayload) {
      if (hasPayload) {
        return true;
      }
      var navType = 'navigate';
      try {
        var navEntries = window.performance && typeof window.performance.getEntriesByType === 'function'
          ? window.performance.getEntriesByType('navigation')
          : [];
        if (navEntries && navEntries.length && navEntries[0] && navEntries[0].type) {
          navType = String(navEntries[0].type);
        }
      } catch (e) {
        navType = 'navigate';
      }
      if (navType === 'reload' || navType === 'back_forward') {
        return true;
      }
      return isInternalPortalReferrer();
    };
    var resolveNavParentGroupKeyForUrl = function (url) {
      try {
        var parsed = new URL(String(url || ''), window.location.href);
        var params = parsed.searchParams;
        var mappedItemKey = resolveRouteMappedNavItemKey(params);
        var dynamicItemKey = resolveDynamicActiveNavItemKey(params, parsed.pathname);
        var activeItemKey = mappedItemKey || dynamicItemKey;
        if (!activeItemKey) {
          return '';
        }
        var activeItemEl = findStaffNavItemByKey(activeItemKey);
        if (!activeItemEl) {
          return '';
        }
        return String(activeItemEl.getAttribute('data-staff-nav-item-group') || '').trim();
      } catch (e) {
        return '';
      }
    };
    var setActiveNavFromURL = function (params) {
      var currentParams = params || readCurrentStaffSearchParams();
      var mappedItemKey = resolveRouteMappedNavItemKey(currentParams);
      var dynamicItemKey = resolveDynamicActiveNavItemKey(currentParams);
      var activeItemKey = mappedItemKey || dynamicItemKey;
      var mappedGroupKey = applyActiveItemByKey(activeItemKey);
      return syncStaffNavGroupActiveState(mappedGroupKey || getFirstActiveGroupKey() || readStaffExpandedGroupState());
    };
    var readMainScrollPayload = function () {
      var raw = readStorageString(window.sessionStorage, staffMainScrollPayloadKey);
      if (!raw) {
        return null;
      }
      try {
        var payload = JSON.parse(raw);
        return payload && typeof payload === 'object' ? payload : null;
      } catch (e) {
        return null;
      }
    };
    var writeMainScrollPayload = function (payload) {
      try {
        if (!payload || typeof payload !== 'object') {
          window.sessionStorage.removeItem(staffMainScrollPayloadKey);
          return;
        }
        window.sessionStorage.setItem(staffMainScrollPayloadKey, JSON.stringify(payload));
      } catch (e) {
        // Ignore storage failures.
      }
    };
    var readStaffMainScrollMap = function () {
      return readStorageJson(window.sessionStorage, staffMainScrollMapKey, {});
    };
    var writeStaffMainScrollMap = function (map) {
      writeStorageJson(window.sessionStorage, staffMainScrollMapKey, map && typeof map === 'object' ? map : {});
    };
    var readMainScrollForRoute = function (routeKey) {
      var key = String(routeKey || '').trim();
      if (!key) {
        return -1;
      }
      var map = readStaffMainScrollMap();
      if (!Object.prototype.hasOwnProperty.call(map, key)) {
        return -1;
      }
      var value = Number(map[key]);
      if (!isFinite(value) || value < 0) {
        return -1;
      }
      return Math.round(value);
    };
    var persistMainScrollForRoute = function (routeKey, scrollTop) {
      var key = String(routeKey || '').trim();
      if (!key) {
        return;
      }
      var y = Math.max(0, Math.round(Number(scrollTop) || 0));
      var map = readStaffMainScrollMap();
      map[key] = y;
      writeStaffMainScrollMap(map);
    };
    var readStaffNavScrollMap = function () {
      return readStorageJson(window.sessionStorage, staffNavScrollMapKey, {});
    };
    var writeStaffNavScrollMap = function (map) {
      writeStorageJson(window.sessionStorage, staffNavScrollMapKey, map && typeof map === 'object' ? map : {});
    };
    var readNavScrollForRoute = function (routeKey) {
      var key = String(routeKey || '').trim();
      if (!key) {
        return null;
      }
      var map = readStaffNavScrollMap();
      if (!Object.prototype.hasOwnProperty.call(map, key)) {
        return null;
      }
      var payload = map[key];
      if (!payload || typeof payload !== 'object') {
        return null;
      }
      var y = Number(payload.y);
      var parent = String(payload.parent || '').trim();
      if (!isFinite(y) || y < 0) {
        return null;
      }
      return {
        y: Math.round(y),
        parent: parent
      };
    };
    var persistNavScrollForRoute = function (routeKey, scrollTop, parentGroupKey) {
      var key = String(routeKey || '').trim();
      if (!key) {
        return;
      }
      var y = Math.max(0, Math.round(Number(scrollTop) || 0));
      var map = readStaffNavScrollMap();
      map[key] = {
        y: y,
        parent: String(parentGroupKey || '').trim()
      };
      writeStaffNavScrollMap(map);
    };
    var resolveStaffMainScrollHost = function () {
      if (staffMainScrollHost && document.body.contains(staffMainScrollHost)) {
        return staffMainScrollHost;
      }
      if (staffShell) {
        staffMainScrollHost = staffShell.querySelector('.cmn-staff-main, .cmn-school-main');
      }
      if (!staffMainScrollHost) {
        staffMainScrollHost = document.querySelector('.cmn-staff-main, .cmn-school-main');
      }
      return staffMainScrollHost;
    };
    var getCurrentMainScrollTop = function () {
      var host = resolveStaffMainScrollHost();
      if (host) {
        return Math.max(0, Number(host.scrollTop) || 0);
      }
      return Math.max(0, window.scrollY || window.pageYOffset || 0);
    };
    var setCurrentMainScrollTop = function (scrollTop) {
      var targetScroll = Math.max(0, Number(scrollTop) || 0);
      var host = resolveStaffMainScrollHost();
      if (host) {
        host.scrollTop = targetScroll;
        return;
      }
      if (typeof window.scrollTo === 'function') {
        window.scrollTo(0, targetScroll);
      }
    };
    var getCurrentMajorRouteKey = function () {
      return normalizePathname(window.location.pathname) + '|' + resolveMajorRouteKeyFromParams(readCurrentStaffSearchParams());
    };
    var stashMainScrollForNavigation = function (targetRouteKey, targetParentGroupKey, preserveScroll) {
      var routeKey = String(targetRouteKey || '').trim() || getRouteKey(readCurrentStaffSearchParams());
      var currentRouteKey = getRouteKey(readCurrentStaffSearchParams());
      var currentY = getCurrentMainScrollTop();
      persistMainScrollForRoute(currentRouteKey, currentY);
      var parentGroupKey = String(targetParentGroupKey || '').trim();
      var targetSavedY = readMainScrollForRoute(routeKey);
      var y = targetSavedY >= 0 ? targetSavedY : (preserveScroll ? currentY : 0);
      writeMainScrollPayload({
        targetRoute: routeKey,
        targetParent: parentGroupKey,
        y: Math.max(0, Math.round(y))
      });
    };
    var persistScrollBeforeNav = function (targetUrl, preferredTargetGroupKey, preserveWithinModule) {
      var targetHref = String(targetUrl || '').trim();
      if (!targetHref) {
        return;
      }
      var currentParams = readCurrentStaffSearchParams();
      var currentRouteKey = getRouteKey(currentParams);
      var targetRouteKey = getRouteKeyFromUrl(targetHref);
      var currentParentGroupKey = resolveCurrentNavParentGroupKey() || activeGroupKey;
      var targetGroupKey = String(preferredTargetGroupKey || '').trim();
      if (!targetGroupKey) {
        targetGroupKey = resolveNavParentGroupKeyForUrl(targetHref);
      }
      var preserveMainScroll = false;
      if (targetGroupKey && currentParentGroupKey && targetGroupKey === currentParentGroupKey) {
        preserveMainScroll = true;
      }
      if (!preserveMainScroll && preserveWithinModule) {
        preserveMainScroll = resolveMajorRouteKeyFromUrl(targetHref) === getCurrentMajorRouteKey();
      }
      if (!preserveMainScroll) {
        preserveMainScroll = resolveMajorRouteKeyFromUrl(targetHref) === getCurrentMajorRouteKey();
      }
      if (targetGroupKey) {
        writeStaffExpandedGroupState(targetGroupKey);
      }
      persistStaffNavScrollPosition(currentParentGroupKey, currentRouteKey);
      if (!readNavScrollForRoute(targetRouteKey)) {
        persistNavScrollForRoute(
          targetRouteKey,
          Math.max(0, Math.round(Number(staffNav.scrollTop) || 0)),
          targetGroupKey || currentParentGroupKey || activeGroupKey
        );
      }
      stashMainScrollForNavigation(targetRouteKey, targetGroupKey || currentParentGroupKey, preserveMainScroll);
      staffMainScrollIntentSet = true;
    };
    var deriveFormSubmitTargetUrl = function (formEl, submitterEl) {
      var form = formEl && formEl.nodeType === 1 ? formEl : null;
      if (!form) {
        return window.location.href;
      }
      var action = String(form.getAttribute('action') || window.location.href || '').trim() || window.location.href;
      var method = String(form.getAttribute('method') || 'get').trim().toLowerCase();
      var target;
      try {
        target = new URL(action, window.location.href);
      } catch (e) {
        return window.location.href;
      }
      if (method !== 'get') {
        return target.href;
      }
      var formData;
      try {
        formData = new FormData(form);
      } catch (e) {
        return target.href;
      }
      if (submitterEl && submitterEl.name) {
        formData.append(submitterEl.name, submitterEl.value || '');
      }
      formData.forEach(function (value, key) {
        if (typeof value !== 'string') {
          return;
        }
        target.searchParams.delete(key);
        target.searchParams.append(key, value);
      });
      return target.href;
    };
    var restoreMainScrollFromNavigation = function () {
      var payload = readMainScrollPayload();
      var currentRoute = getRouteKey(readCurrentStaffSearchParams());
      var currentParent = resolveCurrentNavParentGroupKey();
      var resolvedY = -1;
      var allowMapRestore = staffInitialScrollHydrationComplete ? true : shouldRestoreStoredScrollState(!!payload);

      if (payload && typeof payload === 'object') {
        var targetRoute = String(payload.targetRoute || '').trim();
        if (targetRoute && targetRoute === currentRoute) {
          var targetParent = String(payload.targetParent || '').trim();
          var shouldRestoreFromPayload = false;
          if (targetParent === '' || currentParent === '') {
            shouldRestoreFromPayload = true;
          } else if (targetParent === currentParent) {
            shouldRestoreFromPayload = true;
          }
          if (shouldRestoreFromPayload) {
            var payloadY = Number(payload.y || 0);
            if (isFinite(payloadY) && payloadY >= 0) {
              resolvedY = Math.max(0, Math.round(payloadY));
            }
          }
        }
      }

      if (resolvedY < 0 && allowMapRestore) {
        resolvedY = readMainScrollForRoute(currentRoute);
      }

      if (resolvedY >= 0) {
        persistMainScrollForRoute(currentRoute, resolvedY);
        window.requestAnimationFrame(function () {
          setCurrentMainScrollTop(resolvedY);
        });
      }
      writeMainScrollPayload(null);
    };
    var readStaffNavScrollTop = function () {
      var raw = readStorageString(window.sessionStorage, staffNavScrollKey);
      if (!raw) {
        return -1;
      }
      var parsed = Number(raw);
      if (!isFinite(parsed) || parsed < 0) {
        return -1;
      }
      return Math.round(parsed);
    };
    var persistStaffNavScrollPosition = function (preferredParentGroupKey, routeKeyOverride) {
      var scrollTop = Math.max(0, Math.round(Number(staffNav.scrollTop) || 0));
      writeStorageString(window.sessionStorage, staffNavScrollKey, String(scrollTop));
      var parentGroupKey = String(preferredParentGroupKey || '').trim() || resolveCurrentNavParentGroupKey();
      writeStorageString(window.sessionStorage, staffNavScrollParentKey, parentGroupKey);
      var routeKey = String(routeKeyOverride || '').trim() || getRouteKey(readCurrentStaffSearchParams());
      persistNavScrollForRoute(routeKey, scrollTop, parentGroupKey);
    };
    var restoreStaffNavScrollPosition = function (routeKeyOverride) {
      var hasMainPayload = !!readMainScrollPayload();
      if (!staffInitialScrollHydrationComplete && !shouldRestoreStoredScrollState(hasMainPayload)) {
        staffNav.scrollTop = 0;
        return;
      }
      var routeKey = String(routeKeyOverride || '').trim() || getRouteKey(readCurrentStaffSearchParams());
      var mappedPayload = readNavScrollForRoute(routeKey);
      if (mappedPayload) {
        var mappedParent = String(mappedPayload.parent || '').trim();
        var currentMappedParent = resolveCurrentNavParentGroupKey();
        if ((mappedParent || currentMappedParent) && mappedParent && currentMappedParent && mappedParent !== currentMappedParent) {
          staffNav.scrollTop = 0;
          persistStaffNavScrollPosition(currentMappedParent, routeKey);
          return;
        }
        staffNav.scrollTop = mappedPayload.y;
        return;
      }

      var savedScrollTop = readStaffNavScrollTop();
      if (savedScrollTop < 0) {
        return;
      }
      var savedParent = readStorageString(window.sessionStorage, staffNavScrollParentKey).trim();
      var currentParent = resolveCurrentNavParentGroupKey();
      if ((savedParent || currentParent) && savedParent !== currentParent) {
        staffNav.scrollTop = 0;
        persistStaffNavScrollPosition(currentParent, routeKey);
        return;
      }
      staffNav.scrollTop = savedScrollTop;
      writeStorageString(window.sessionStorage, staffNavScrollParentKey, currentParent || savedParent);
    };
    var ensureActiveNavItemVisible = function () {
      if (staffNavActiveAutoScrollDone) {
        return;
      }
      staffNavActiveAutoScrollDone = true;
      var activeLink = staffNav.querySelector('.cmn-school-nav-link.is-active');
      if (!activeLink) {
        return;
      }
      var containerTop = Math.max(0, Number(staffNav.scrollTop) || 0);
      var containerBottom = containerTop + Math.max(0, Number(staffNav.clientHeight) || 0);
      var itemTop = Math.max(0, Number(activeLink.offsetTop) || 0);
      var itemBottom = itemTop + Math.max(0, Number(activeLink.offsetHeight) || 0);
      if (itemTop >= containerTop && itemBottom <= containerBottom) {
        return;
      }
      if (typeof activeLink.scrollIntoView === 'function') {
        activeLink.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
      }
    };
    var persistCurrentMainScrollState = function () {
      if (staffMainScrollIntentSet) {
        return;
      }
      persistMainScrollForRoute(getRouteKey(readCurrentStaffSearchParams()), getCurrentMainScrollTop());
    };
    var isStaffNavMobileViewport = function () {
      if (!window.matchMedia) {
        return window.innerWidth <= 900;
      }
      return window.matchMedia('(max-width: 900px)').matches;
    };
    var setStaffNavPeekState = function (isPeek) {
      staffNavPeekOpen = !!isPeek;
      staffNav.classList.toggle('is-peek-open', staffNavPeekOpen);
      if (staffShell) {
        staffShell.classList.toggle('is-nav-peek-open', staffNavPeekOpen);
      }
    };
    var setStaffNavCompactState = function (isCompact) {
      if (!isCompact) {
        setStaffNavPeekState(false);
      }
      staffNav.classList.toggle('is-collapsed', !!isCompact);
      if (staffShell) {
        staffShell.classList.toggle('is-nav-collapsed', !!isCompact);
        if (!isCompact) {
          staffShell.classList.remove('is-nav-peek-open');
        }
      }
      if (staffNavMinimizeBtn) {
        staffNavMinimizeBtn.setAttribute('aria-pressed', isCompact ? 'true' : 'false');
        staffNavMinimizeBtn.setAttribute('data-tooltip', isCompact ? 'Expand sidebar' : 'Minimise sidebar');
      }
    };
    var enforceStaffNavMobileState = function () {
      if (!isStaffNavMobileViewport()) {
        return;
      }
      setStaffNavPeekState(false);
      setStaffNavCompactState(false);
      writeStaffNavCompactState(false);
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
    var persistStaffNavOrder = function (order) {
      if (!(window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.staffNavNonce)) {
        return Promise.resolve(false);
      }
      var fd = new FormData();
      fd.append('action', 'cmn_save_staff_nav_order');
      fd.append('nonce', window.cmnPortal.staffNavNonce);
      fd.append('order', JSON.stringify(order || { groups: [], items: {} }));
      return fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      }).then(function (response) {
        if (!response || !response.ok) {
          return false;
        }
        return response.json().then(function (json) {
          return !!(json && json.success);
        }).catch(function () {
          return false;
        });
      }).catch(function () {
        return false;
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
    var setNavDragEnabled = function (enabled) {
      staffNav.querySelectorAll('[data-staff-nav-group]').forEach(function (groupEl) {
        groupEl.setAttribute('draggable', enabled ? 'true' : 'false');
      });
      staffNav.querySelectorAll('[data-staff-nav-item]').forEach(function (itemEl) {
        itemEl.setAttribute('draggable', enabled ? 'true' : 'false');
      });
    };
    var setNavDiscardPromptVisible = function (visible) {
      if (!staffNavEditDiscardPanel) {
        return;
      }
      staffNavEditDiscardPanel.hidden = !visible;
    };
    var persistCurrentNavEditModeState = function () {
      writeStaffNavEditModeState(!!isNavEditing, !!navOrderDirty);
    };
    var refreshNavEditControls = function () {
      staffNav.classList.toggle('is-nav-editing', isNavEditing);
      staffNav.classList.toggle('edit-mode-active', isNavEditing);
      if (staffNavEditToggleBtn) {
        staffNavEditToggleBtn.setAttribute('aria-pressed', isNavEditing ? 'true' : 'false');
        staffNavEditToggleBtn.setAttribute('data-tooltip', isNavEditing ? 'Hide edit options' : 'Toggle edit options');
      }
      if (staffNavEditPanel) {
        staffNavEditPanel.hidden = !isNavEditing;
        staffNavEditPanel.classList.toggle('is-open', isNavEditing);
        staffNavEditPanel.setAttribute('aria-hidden', isNavEditing ? 'false' : 'true');
      }
      if (staffNavEditCancelBtn) {
        staffNavEditCancelBtn.disabled = navOrderSaveInFlight;
      }
      if (staffNavEditResetBtn) {
        staffNavEditResetBtn.disabled = navOrderSaveInFlight;
      }
      if (staffNavEditSaveBtn) {
        staffNavEditSaveBtn.disabled = navOrderSaveInFlight || !navOrderDirty;
      }
      persistCurrentNavEditModeState();
    };
    var syncNavDirtyState = function () {
      var currentOrder = normalizeNavOrder(collectNavOrderFromDom(), navOrderDefault);
      navOrderDirty = orderFingerprint(currentOrder) !== orderFingerprint(navOrderBaseline);
      if (!navOrderDirty) {
        setNavDiscardPromptVisible(false);
      }
      refreshNavEditControls();
    };
    var exitNavEditMode = function (revertOrder) {
      if (revertOrder) {
        applyNavOrderToDom(navOrderBaseline);
      }
      isNavEditing = false;
      navOrderDirty = false;
      navOrderSaveInFlight = false;
      setNavDiscardPromptVisible(false);
      setNavDragEnabled(false);
      refreshNavEditControls();
    };
    var enterNavEditMode = function () {
      isNavEditing = true;
      navOrderSaveInFlight = false;
      navOrderBaseline = normalizeNavOrder(collectNavOrderFromDom(), navOrderDefault);
      navOrderDirty = false;
      setNavDiscardPromptVisible(false);
      setNavDragEnabled(true);
      refreshNavEditControls();
    };
    var requestNavEditModeClose = function () {
      if (!isNavEditing || navOrderSaveInFlight) {
        return;
      }
      if (!navOrderDirty) {
        exitNavEditMode(false);
        return;
      }
      setNavDiscardPromptVisible(true);
      persistCurrentNavEditModeState();
    };

    var currentStaffRouteParams = readCurrentStaffSearchParams();
    var activeGroupKey = setActiveNavFromURL(currentStaffRouteParams);
    var navState = readStaffNavState();
    var serverNavState = readServerStaffNavState();
    var persistedExpandedGroup = readStaffExpandedGroupState();
    var storedGroupOpenState = readStaffGroupOpenState();
    Object.keys(serverNavState).forEach(function (key) {
      if (!Object.prototype.hasOwnProperty.call(navState, key)) {
        navState[key] = !!serverNavState[key] ? 1 : 0;
      }
    });
    staffNav.querySelectorAll('[data-staff-nav-group]').forEach(function (groupEl) {
      var key = String(groupEl.getAttribute('data-staff-nav-group') || '').trim();
      var hasActive = !!groupEl.querySelector('.cmn-school-nav-link.is-active');
      var shouldOpen = false;
      if (hasActive || (!!activeGroupKey && key === activeGroupKey)) {
        shouldOpen = true;
      } else if (Object.prototype.hasOwnProperty.call(storedGroupOpenState, key)) {
        shouldOpen = !!storedGroupOpenState[key];
      } else if (Object.prototype.hasOwnProperty.call(navState, key)) {
        shouldOpen = !!navState[key];
      }
      if (!shouldOpen && persistedExpandedGroup && key === persistedExpandedGroup) {
        shouldOpen = true;
      }
      setStaffGroupState(groupEl, shouldOpen);
      navState[key] = shouldOpen ? 1 : 0;
      storedGroupOpenState[key] = shouldOpen ? 1 : 0;
    });
    activeGroupKey = syncStaffNavGroupActiveState(activeGroupKey);
    if (activeGroupKey) {
      writeStaffExpandedGroupState(activeGroupKey);
    }
    writeStaffGroupOpenState(storedGroupOpenState);
    triggerStaffNavActiveAnimation();
    persistStaffNavState(navState);
    staffNav.querySelectorAll('[data-staff-nav-toggle]').forEach(function (toggleBtn) {
      toggleBtn.addEventListener('click', function (event) {
        if (isNavEditing) {
          event.preventDefault();
          return;
        }
        var key = toggleBtn.getAttribute('data-staff-nav-toggle') || '';
        var groupEl = staffNav.querySelector('[data-staff-nav-group="' + key + '"]');
        if (!groupEl) {
          return;
        }
        var openedFromCollapsed = false;
        if (staffNav.classList.contains('is-collapsed') && !staffNavPeekOpen) {
          setStaffNavPeekState(true);
          openedFromCollapsed = true;
        }
        var willOpen = openedFromCollapsed ? true : !groupEl.classList.contains('is-open');
        if (!willOpen && groupEl.querySelector('.cmn-school-nav-link.is-active')) {
          willOpen = true;
        }
        setStaffGroupState(groupEl, willOpen);
        navState[key] = willOpen ? 1 : 0;
        storedGroupOpenState[key] = willOpen ? 1 : 0;
        writeStaffGroupOpenState(storedGroupOpenState);
        if (willOpen) {
          writeStaffExpandedGroupState(key);
        } else if (readStaffExpandedGroupState() === key) {
          var fallbackExpandedGroup = '';
          staffNav.querySelectorAll('[data-staff-nav-group].is-open').forEach(function (openGroupEl) {
            if (fallbackExpandedGroup) {
              return;
            }
            fallbackExpandedGroup = String(openGroupEl.getAttribute('data-staff-nav-group') || '').trim();
          });
          writeStaffExpandedGroupState(fallbackExpandedGroup);
        }
        activeGroupKey = syncStaffNavGroupActiveState('');
        persistStaffNavScrollPosition(activeGroupKey);
        persistStaffNavState(navState);
      });
    });
    var navScrollTicking = false;
    staffNav.addEventListener('scroll', function () {
      if (navScrollTicking) {
        return;
      }
      navScrollTicking = true;
      window.requestAnimationFrame(function () {
        navScrollTicking = false;
        persistStaffNavScrollPosition('');
      });
    }, { passive: true });
    var restoreScrollAfterRender = function () {
      var initialRouteKey = getRouteKey(readCurrentStaffSearchParams());
      restoreStaffNavScrollPosition(initialRouteKey);
      restoreMainScrollFromNavigation();
      ensureActiveNavItemVisible();
      staffInitialScrollHydrationComplete = true;
    };
    setStaffNavCompactState(readStaffNavCompactState());
    enforceStaffNavMobileState();
    window.requestAnimationFrame(function () {
      restoreScrollAfterRender();
      window.requestAnimationFrame(function () {
        staffNav.classList.remove('is-nav-hydrating');
        staffNav.classList.add('is-nav-ready');
      });
    });
    window.addEventListener('resize', function () {
      enforceStaffNavMobileState();
    });
    window.addEventListener('popstate', function () {
      var currentParams = readCurrentStaffSearchParams();
      activeGroupKey = setActiveNavFromURL(currentParams);
      if (activeGroupKey) {
        var activeGroupEl = staffNav.querySelector('[data-staff-nav-group="' + activeGroupKey + '"]');
        if (activeGroupEl) {
          setStaffGroupState(activeGroupEl, true);
          navState[activeGroupKey] = 1;
          storedGroupOpenState[activeGroupKey] = 1;
          writeStaffGroupOpenState(storedGroupOpenState);
          persistStaffNavState(navState);
        }
        writeStaffExpandedGroupState(activeGroupKey);
      }
      triggerStaffNavActiveAnimation();
      restoreScrollAfterRender();
    });
    if (staffNavMinimizeBtn) {
      staffNavMinimizeBtn.addEventListener('click', function () {
        if (isStaffNavMobileViewport()) {
          setStaffNavPeekState(false);
          setStaffNavCompactState(false);
          writeStaffNavCompactState(false);
          return;
        }
        var willCompact = !staffNav.classList.contains('is-collapsed');
        if (willCompact) {
          setStaffNavPeekState(false);
        }
        setStaffNavCompactState(willCompact);
        writeStaffNavCompactState(willCompact);
      });
    }
    staffNav.querySelectorAll('.cmn-school-nav-link').forEach(function (linkEl) {
      linkEl.addEventListener('click', function (event) {
        if (isNavEditing) {
          event.preventDefault();
          return;
        }
        if (isPlainLeftClick(event)) {
          persistScrollBeforeNav(linkEl.getAttribute('href') || window.location.href, getNavGroupKeyForLink(linkEl), false);
        }
        if (staffNav.classList.contains('is-collapsed')) {
          setStaffNavPeekState(false);
        }
      });
    });
    if (staffNavEditToggleBtn) {
      staffNavEditToggleBtn.addEventListener('click', function () {
        if (!isNavEditing) {
          enterNavEditMode();
          return;
        }
        requestNavEditModeClose();
      });
    }
    if (staffNavEditCancelBtn) {
      staffNavEditCancelBtn.addEventListener('click', function () {
        requestNavEditModeClose();
      });
    }
    if (staffNavEditResetBtn) {
      staffNavEditResetBtn.addEventListener('click', function () {
        if (!isNavEditing || navOrderSaveInFlight) {
          return;
        }
        applyNavOrderToDom(navOrderDefault);
        syncNavDirtyState();
      });
    }
    if (staffNavEditSaveBtn) {
      staffNavEditSaveBtn.addEventListener('click', function () {
        if (!isNavEditing || navOrderSaveInFlight || !navOrderDirty) {
          return;
        }
        navOrderSaveInFlight = true;
        refreshNavEditControls();
        var nextOrder = normalizeNavOrder(collectNavOrderFromDom(), navOrderDefault);
        persistStaffNavOrder(nextOrder).then(function (saved) {
          if (!saved) {
            navOrderSaveInFlight = false;
            refreshNavEditControls();
            return;
          }
          navOrderBaseline = cloneJson(nextOrder, navOrderDefault);
          navOrderSaved = cloneJson(nextOrder, navOrderDefault);
          try {
            staffNav.setAttribute('data-nav-order', JSON.stringify(navOrderSaved));
          } catch (e) {
            // Ignore.
          }
          exitNavEditMode(false);
        }).catch(function () {
          navOrderSaveInFlight = false;
          refreshNavEditControls();
        });
      });
    }
    if (staffNavEditDiscardConfirmBtn) {
      staffNavEditDiscardConfirmBtn.addEventListener('click', function () {
        if (!isNavEditing || navOrderSaveInFlight) {
          return;
        }
        exitNavEditMode(true);
      });
    }
    if (staffNavEditDiscardKeepBtn) {
      staffNavEditDiscardKeepBtn.addEventListener('click', function () {
        if (!isNavEditing || navOrderSaveInFlight) {
          return;
        }
        setNavDiscardPromptVisible(false);
        persistCurrentNavEditModeState();
      });
    }

    staffNav.addEventListener('dragstart', function (event) {
      if (!isNavEditing) {
        return;
      }
      var itemEl = event.target.closest('[data-staff-nav-item]');
      if (itemEl) {
        dragType = 'item';
        dragNode = itemEl;
        dragGroupKey = String(itemEl.getAttribute('data-staff-nav-item-group') || '').trim();
      } else {
        var groupEl = event.target.closest('[data-staff-nav-group]');
        if (!groupEl) {
          return;
        }
        dragType = 'group';
        dragNode = groupEl;
        dragGroupKey = '';
      }
      if (!dragNode) {
        return;
      }
      dragNode.classList.add('is-dragging');
      if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', 'cmn-nav-reorder');
      }
    });
    staffNav.addEventListener('dragover', function (event) {
      if (!isNavEditing || !dragNode) {
        return;
      }
      if (dragType === 'group') {
        var targetGroup = event.target.closest('[data-staff-nav-group]');
        if (!targetGroup || targetGroup === dragNode) {
          return;
        }
        event.preventDefault();
        var targetRect = targetGroup.getBoundingClientRect();
        var placeAfter = event.clientY > (targetRect.top + (targetRect.height / 2));
        targetGroup.parentNode.insertBefore(dragNode, placeAfter ? targetGroup.nextSibling : targetGroup);
        return;
      }
      if (dragType === 'item') {
        var targetItem = event.target.closest('[data-staff-nav-item]');
        if (!targetItem || targetItem === dragNode) {
          return;
        }
        var targetGroupKey = String(targetItem.getAttribute('data-staff-nav-item-group') || '').trim();
        if (!targetGroupKey || targetGroupKey !== dragGroupKey) {
          return;
        }
        event.preventDefault();
        var targetItemRect = targetItem.getBoundingClientRect();
        var placeAfterItem = event.clientY > (targetItemRect.top + (targetItemRect.height / 2));
        targetItem.parentNode.insertBefore(dragNode, placeAfterItem ? targetItem.nextSibling : targetItem);
      }
    });
    staffNav.addEventListener('drop', function (event) {
      if (!isNavEditing || !dragNode) {
        return;
      }
      event.preventDefault();
    });
    staffNav.addEventListener('dragend', function () {
      if (dragNode) {
        dragNode.classList.remove('is-dragging');
      }
      dragType = '';
      dragNode = null;
      dragGroupKey = '';
      if (isNavEditing) {
        syncNavDirtyState();
      }
    });

    setNavDragEnabled(false);
    refreshNavEditControls();
    var storedEditModeState = readStaffNavEditModeState();
    if (storedEditModeState.open && storedEditModeState.dirty) {
      enterNavEditMode();
    } else {
      writeStaffNavEditModeState(false, false);
    }
    var persistStaffNavStateBeforeUnload = function () {
      persistStaffNavScrollPosition(resolveCurrentNavParentGroupKey() || activeGroupKey);
      persistCurrentMainScrollState();
    };
    window.addEventListener('pagehide', persistStaffNavStateBeforeUnload);
    window.addEventListener('beforeunload', persistStaffNavStateBeforeUnload);
    document.addEventListener('click', function (event) {
      if (event.defaultPrevented || !isPlainLeftClick(event)) {
        return;
      }
      var linkEl = event.target && typeof event.target.closest === 'function'
        ? event.target.closest('a[href]')
        : null;
      if (!linkEl || staffNav.contains(linkEl)) {
        return;
      }
      var href = String(linkEl.getAttribute('href') || '').trim();
      if (!href || href.charAt(0) === '#' || /^javascript:/i.test(href)) {
        return;
      }
      var targetAttr = String(linkEl.getAttribute('target') || '').toLowerCase();
      if (targetAttr === '_blank' || linkEl.hasAttribute('download')) {
        return;
      }
      if (!isInternalPortalNavigationUrl(href)) {
        return;
      }
      persistScrollBeforeNav(href, '', true);
    });
    document.addEventListener('submit', function (event) {
      if (event.defaultPrevented) {
        return;
      }
      var formEl = event.target && event.target.nodeName === 'FORM' ? event.target : null;
      if (!formEl || staffNav.contains(formEl)) {
        return;
      }
      var targetUrl = deriveFormSubmitTargetUrl(formEl, event.submitter || null);
      if (!isInternalPortalNavigationUrl(targetUrl)) {
        return;
      }
      persistScrollBeforeNav(targetUrl, '', true);
    });
    document.addEventListener('click', function (event) {
      if (!staffNavPeekOpen || !staffNav.classList.contains('is-collapsed')) {
        return;
      }
      if (staffNav.contains(event.target)) {
        return;
      }
      setStaffNavPeekState(false);
    });
    document.addEventListener('keydown', function (event) {
      if ((event.key || '') !== 'Escape') {
        return;
      }
      if (isNavEditing && !navOrderSaveInFlight) {
        if (staffNavEditDiscardPanel && !staffNavEditDiscardPanel.hidden) {
          setNavDiscardPromptVisible(false);
          persistCurrentNavEditModeState();
        } else {
          requestNavEditModeClose();
        }
        return;
      }
      if (!staffNavPeekOpen || !staffNav.classList.contains('is-collapsed')) {
        return;
      }
      setStaffNavPeekState(false);
    });
  }

  if (window.cmnPortal && Number(window.cmnPortal.isStaffUser || 0) === 1 && window.cmnPortal.ajaxUrl && window.cmnPortal.staffPresenceNonce) {
    var touchStaffPresence = function () {
      var fd = new FormData();
      fd.append('action', 'cmn_touch_staff_presence');
      fd.append('nonce', window.cmnPortal.staffPresenceNonce);
      fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      }).catch(function () {
        // Ignore transient network failures.
      });
    };
    touchStaffPresence();
    window.setInterval(touchStaffPresence, 120000);
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
      var managerSelect = bulkForm.querySelector('select[name="cmn_bulk_manager_id"]');
      var statusSelect = bulkForm.querySelector('select[name="cmn_bulk_status"]');
      var pipelineSelect = bulkForm.querySelector('select[name="cmn_bulk_pipeline"]');
      var groupsInput = bulkForm.querySelector('input[name="cmn_bulk_groups"]');
      var selectedRows = bulkForm.querySelectorAll('.cmn-school-select:checked');
      if (!actionSelect || !actionSelect.value) {
        event.preventDefault();
        alert('Select a bulk action first.');
        return;
      }
      if (actionSelect.value !== 'share_groups' && !selectedRows.length) {
        event.preventDefault();
        alert('Select at least one school first.');
        return;
      }
      if (actionSelect.value === 'assign_manager' && (!managerSelect || !managerSelect.value)) {
        event.preventDefault();
        alert('Select an account manager first.');
        return;
      }
      if (actionSelect.value === 'set_status' && (!statusSelect || !statusSelect.value)) {
        event.preventDefault();
        alert('Select a status first.');
        return;
      }
      if (actionSelect.value === 'set_pipeline' && (!pipelineSelect || !pipelineSelect.value)) {
        event.preventDefault();
        alert('Select a pipeline stage first.');
        return;
      }
      if ((actionSelect.value === 'assign_groups' || actionSelect.value === 'share_groups') && (!groupsInput || !groupsInput.value.trim())) {
        event.preventDefault();
        alert('Enter at least one group name.');
        return;
      }
      if (actionSelect.value === 'delete') {
        if (!window.confirm('Delete selected schools? This cannot be undone.')) {
          event.preventDefault();
        }
        return;
      }
      if (actionSelect.value === 'remove_duplicates') {
        if (!window.confirm('Remove duplicate schools in the selected set? Duplicates are detected by School ID/domain and cannot be undone.')) {
          event.preventDefault();
        }
      }
    });
  }

  var schoolImportRunner = document.querySelector('[data-school-import-runner]');
  if (schoolImportRunner) {
    var importAjaxUrl = schoolImportRunner.getAttribute('data-ajax-url') || (window.cmnPortal && window.cmnPortal.ajaxUrl) || '';
    var importJobId = schoolImportRunner.getAttribute('data-job-id') || '';
    var importNonce = schoolImportRunner.getAttribute('data-nonce') || '';
    var importLimit = parseInt(schoolImportRunner.getAttribute('data-limit') || '10', 10);
    var importMaxRetries = Math.max(1, parseInt(schoolImportRunner.getAttribute('data-max-retries') || '3', 10) || 3);
    var importDone = schoolImportRunner.getAttribute('data-done') === '1';
    var importInFlight = false;
    var importStopped = false;
    var importRetryCount = 0;
    var importCurrentOffset = 0;
    var statusEl = schoolImportRunner.querySelector('[data-school-import-status]');
    var summaryEl = schoolImportRunner.querySelector('[data-school-import-summary]');
    var lastEl = schoolImportRunner.querySelector('[data-school-import-last]');
    var progressFillEl = schoolImportRunner.querySelector('[data-school-import-progress-fill]');
    var processedEl = schoolImportRunner.querySelector('[data-school-import-processed]');
    var totalEl = schoolImportRunner.querySelector('[data-school-import-total]');
    var okEl = schoolImportRunner.querySelector('[data-school-import-ok]');
    var needsEl = schoolImportRunner.querySelector('[data-school-import-needs]');
    var hardEl = schoolImportRunner.querySelector('[data-school-import-hard]');

    var setImportStatus = function (message, isError) {
      if (!statusEl) {
        return;
      }
      statusEl.textContent = message || '';
      statusEl.classList.toggle('is-error', !!isError);
    };

    var numberOrZero = function (value) {
      var raw = String(value || '0').replace(/[^0-9-]/g, '');
      var parsed = parseInt(raw || '0', 10);
      return Number.isFinite(parsed) ? parsed : 0;
    };

    var setImportMetric = function (el, value) {
      if (!el) {
        return;
      }
      el.textContent = String(numberOrZero(value));
    };

    importCurrentOffset = numberOrZero(processedEl ? processedEl.textContent : 0);

    var updateImportUi = function (payload) {
      var totals = payload && payload.totals ? payload.totals : {};
      var group = payload && payload.group ? payload.group : {};
      var processed = numberOrZero(totals.processed);
      var total = numberOrZero(totals.total);
      var importOk = numberOrZero(totals.imported_ok);
      var needsAttention = numberOrZero(totals.needs_attention);
      var hardInvalid = numberOrZero(totals.hard_invalid);
      var overallPct = numberOrZero(payload.overall_progress_pct);
      var groupIndex = numberOrZero(group.index);
      var groupTotal = numberOrZero(group.total);
      var groupStart = numberOrZero(group.start);
      var groupEnd = numberOrZero(group.end);
      var groupPct = numberOrZero(group.progress_pct);
      var lastProcessed = payload && payload.last_processed ? payload.last_processed : null;
      var lastRow = lastProcessed ? numberOrZero(lastProcessed.row_index) : 0;
      var lastName = lastProcessed && lastProcessed.school_name ? String(lastProcessed.school_name) : '';

      if (processedEl) {
        processedEl.textContent = String(processed);
      }
      if (totalEl) {
        totalEl.textContent = String(total);
      }
      setImportMetric(okEl, importOk);
      setImportMetric(needsEl, needsAttention);
      setImportMetric(hardEl, hardInvalid);
      if (progressFillEl) {
        progressFillEl.style.width = String(Math.max(0, Math.min(100, overallPct))) + '%';
      }
      if (lastEl) {
        if (lastRow > 0) {
          lastEl.textContent = 'Last processed row ' + lastRow + (lastName ? ': ' + lastName : '');
        } else {
          lastEl.textContent = '';
        }
      }

      if (!payload.done) {
        var schoolLabel = lastName || 'working...';
        setImportStatus(
          'Importing school ' + Math.max(0, Math.min(total, lastRow)) + ' of ' + total +
          ': ' + schoolLabel +
          ' | Group ' + Math.max(1, groupIndex) + ' of ' + Math.max(1, groupTotal) +
          ' (rows ' + Math.max(1, groupStart) + '-' + Math.max(1, groupEnd) + ')' +
          ' | Group ' + groupPct + '%' +
          ' | Overall ' + overallPct + '%' +
          ' | Imported: ' + importOk +
          ' | Needs attention: ' + needsAttention +
          ' | Hard invalid: ' + hardInvalid +
          ' | Working...',
          false
        );
      } else {
        schoolImportRunner.classList.add('is-done');
        if (summaryEl) {
          summaryEl.classList.add('is-done');
        }
        setImportStatus(
          'Import complete. Processed ' + processed + ' of ' + total +
          ' | Imported: ' + importOk +
          ' | Needs attention: ' + needsAttention +
          ' | Hard invalid: ' + hardInvalid + '.',
          false
        );
      }
    };

    var parseImportJsonResponse = function (response) {
      return response.text().then(function (text) {
        var payload;
        try {
          payload = JSON.parse(text);
        } catch (_err) {
          throw new Error('Import endpoint returned invalid JSON.');
        }
        if (!payload || !payload.success || !payload.data) {
          throw new Error((payload && payload.data && payload.data.message) ? payload.data.message : 'Import request failed.');
        }
        return payload.data;
      });
    };

    var requestImportChunk = function (options) {
      var opts = options && typeof options === 'object' ? options : {};
      var body = new FormData();
      body.append('action', 'cmn_bulk_import_schools_run');
      body.append('nonce', importNonce);
      body.append('job_id', importJobId);
      body.append('limit', String(Math.max(1, Math.min(20, importLimit || 10))));
      body.append('offset', String(Math.max(0, importCurrentOffset)));
      if (opts.skipCurrent) {
        body.append('skip_current', '1');
        body.append('skip_reason', String(opts.skipReason || 'Skipped after retries'));
        body.append('limit', '1');
      }
      var controller = typeof AbortController === 'function' ? new AbortController() : null;
      var timeoutId = 0;
      if (controller) {
        timeoutId = window.setTimeout(function () {
          controller.abort();
        }, 45000);
      }
      return fetch(importAjaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body,
        signal: controller ? controller.signal : undefined
      }).then(parseImportJsonResponse).finally(function () {
        if (timeoutId) {
          window.clearTimeout(timeoutId);
        }
      });
    };

    var forceSkipCurrentRow = function (reasonText) {
      return requestImportChunk({
        skipCurrent: true,
        skipReason: reasonText || ('Skipped row at offset ' + importCurrentOffset + ' after retries')
      }).then(function (payload) {
        var nextOffset = numberOrZero(payload.offset_next || payload.next_offset);
        if (nextOffset > importCurrentOffset) {
          importCurrentOffset = nextOffset;
        } else {
          importCurrentOffset += 1;
        }
        updateImportUi(payload);
        return payload;
      });
    };

    var runImportChunk = function (delayMs) {
      if (importStopped || importDone || importInFlight || !importAjaxUrl || !importJobId || !importNonce) {
        return;
      }
      window.setTimeout(function () {
        if (importStopped || importDone || importInFlight) {
          return;
        }
        importInFlight = true;
        var previousOffset = importCurrentOffset;
        requestImportChunk().then(function (payload) {
          importInFlight = false;
          var nextOffset = numberOrZero(payload.offset_next || payload.next_offset);
          if (nextOffset > importCurrentOffset) {
            importCurrentOffset = nextOffset;
          }
          importRetryCount = 0;
          updateImportUi(payload);
          if (payload.done) {
            importDone = true;
            return;
          }
          if (importCurrentOffset <= previousOffset && numberOrZero(payload.processed_this_chunk || payload.processed) <= 0) {
            setImportStatus('No forward progress at row ' + (previousOffset + 1) + '. Skipping this row and continuing...', true);
            forceSkipCurrentRow('No forward progress detected at row ' + (previousOffset + 1)).then(function (skipPayload) {
              if (skipPayload && skipPayload.done) {
                importDone = true;
                return;
              }
              runImportChunk(140);
            }).catch(function (skipError) {
              importStopped = true;
              setImportStatus('Import stopped: could not skip stuck row ' + (previousOffset + 1) + '. Error: ' + (skipError && skipError.message ? skipError.message : 'Unknown skip failure') + '.', true);
            });
            return;
          }
          runImportChunk(120);
        }).catch(function (error) {
          importInFlight = false;
          importRetryCount += 1;
          if (importRetryCount <= importMaxRetries) {
            var waitMs = Math.min(6000, 1200 * Math.pow(2, Math.max(0, importRetryCount - 1)));
            setImportStatus('Retry ' + importRetryCount + '/' + importMaxRetries + ' for row ' + (importCurrentOffset + 1) + ' after error: ' + (error && error.message ? error.message : 'Request failed') + '.', true);
            runImportChunk(waitMs);
            return;
          }
          importRetryCount = 0;
          setImportStatus('Retries exhausted at row ' + (importCurrentOffset + 1) + '. Skipping this row and continuing...', true);
          forceSkipCurrentRow('Skipped after repeated request failure at row ' + (importCurrentOffset + 1)).then(function (skipPayload) {
            if (skipPayload && skipPayload.done) {
              importDone = true;
              return;
            }
            runImportChunk(140);
          }).catch(function (skipError) {
            importStopped = true;
            setImportStatus('Import paused after retries. Could not skip row ' + (importCurrentOffset + 1) + '. Last error: ' + (skipError && skipError.message ? skipError.message : (error && error.message ? error.message : 'Request failed')) + '.', true);
          });
        });
      }, Math.max(0, numberOrZero(delayMs)));
    };

    if (!importDone) {
      runImportChunk(80);
    }
  }

  var schoolImportMapForm = document.querySelector('[data-school-import-map-form]');
  var schoolImportMapPreviewRoot = document.querySelector('[data-school-import-map-preview]');
  if (schoolImportMapForm && schoolImportMapPreviewRoot) {
    var mapPreviewRaw = schoolImportMapPreviewRoot.getAttribute('data-preview') || '';
    var mapPreviewBody = schoolImportMapPreviewRoot.querySelector('[data-school-import-mapped-preview-body]');
    var mapSelects = Array.prototype.slice.call(schoolImportMapForm.querySelectorAll('select[name^="cmn_map["]'));
    var mapPreviewData = { headers: [], rows: [] };
    try {
      var parsedPreview = JSON.parse(mapPreviewRaw || '{}');
      if (parsedPreview && Array.isArray(parsedPreview.headers) && Array.isArray(parsedPreview.rows)) {
        mapPreviewData = parsedPreview;
      }
    } catch (_err) {
      mapPreviewData = { headers: [], rows: [] };
    }

    var mapFieldOrder = [
      'cmn_school_name',
      'cmn_location',
      'cmn_phone',
      'cmn_email',
      'cmn_cover_manager',
      'cmn_cover_manager_email',
      'cmn_email_name'
    ];

    var headerAliases = {
      cmn_school_name: ['school name', 'school', 'establishment name'],
      cmn_location: ['location', 'town', 'city', 'area'],
      cmn_phone: ['phone', 'contact number', 'telephone', 'tel'],
      cmn_email: ['email', 'school email'],
      cmn_cover_manager: ['cover manager', 'manager name'],
      cmn_cover_manager_email: ['cover manager email', 'manager email'],
      cmn_email_name: ['email name', 'contact name', 'sender name']
    };

    var normalizeMapText = function (value) {
      return String(value || '').toLowerCase().replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim();
    };

    var escapeMapHtml = function (value) {
      return String(value || '').replace(/[&<>\"']/g, function (char) {
        if (char === '&') {
          return '&amp;';
        }
        if (char === '<') {
          return '&lt;';
        }
        if (char === '>') {
          return '&gt;';
        }
        if (char === '"') {
          return '&quot;';
        }
        return '&#39;';
      });
    };

    var findSelectForField = function (fieldKey) {
      return schoolImportMapForm.querySelector('select[name="cmn_map[' + fieldKey + ']"]');
    };

    var autoMapField = function (fieldKey) {
      var select = findSelectForField(fieldKey);
      if (!select || select.value) {
        return;
      }
      var aliases = headerAliases[fieldKey] || [];
      var bestIndex = '';
      var bestScore = 0;
      mapPreviewData.headers.forEach(function (header, idx) {
        var headerNorm = normalizeMapText(header);
        var score = 0;
        aliases.forEach(function (alias) {
          var aliasNorm = normalizeMapText(alias);
          if (!aliasNorm) {
            return;
          }
          if (headerNorm === aliasNorm) {
            score = Math.max(score, 100);
          } else if (headerNorm.indexOf(aliasNorm) !== -1 || aliasNorm.indexOf(headerNorm) !== -1) {
            score = Math.max(score, 50);
          }
        });
        if (score > bestScore) {
          bestScore = score;
          bestIndex = String(idx);
        }
      });
      if (bestIndex !== '') {
        select.value = bestIndex;
      }
    };

    var mappedCellValue = function (row, fieldKey) {
      var select = findSelectForField(fieldKey);
      if (!select || select.value === '') {
        return '—';
      }
      var idx = parseInt(select.value, 10);
      if (!Number.isFinite(idx) || idx < 0) {
        return '—';
      }
      return (row && row[idx] !== undefined && row[idx] !== null && String(row[idx]).trim() !== '') ? String(row[idx]) : '—';
    };

    var renderMappedPreview = function () {
      if (!mapPreviewBody) {
        return;
      }
      var rows = Array.isArray(mapPreviewData.rows) ? mapPreviewData.rows.slice(0, 10) : [];
      if (!rows.length) {
        mapPreviewBody.innerHTML = '<tr><td colspan="7">No preview rows available.</td></tr>';
        return;
      }
      var html = rows.map(function (row) {
        return '<tr>' + mapFieldOrder.map(function (fieldKey) {
          return '<td>' + escapeMapHtml(mappedCellValue(row, fieldKey)) + '</td>';
        }).join('') + '</tr>';
      }).join('');
      mapPreviewBody.innerHTML = html;
    };

    mapFieldOrder.forEach(function (fieldKey) {
      autoMapField(fieldKey);
    });
    renderMappedPreview();
    mapSelects.forEach(function (select) {
      select.addEventListener('change', renderMappedPreview);
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

  var schoolRequestForms = document.querySelectorAll('[data-school-request-form]');
  if (schoolRequestForms.length) {
    schoolRequestForms.forEach(function (form) {
      var decisionSelect = form.querySelector('[data-school-request-decision]');
      var noteWrap = form.querySelector('[data-school-request-note]');
      var noteInput = form.querySelector('[data-school-request-note-input]');
      var convertToClientField = form.querySelector('.cmn-convert-client-flag');
      var convertWrap = form.querySelector('[data-school-request-convert]');
      var convertCheckbox = form.querySelector('[data-school-request-convert-input]');
      var schoolName = form.getAttribute('data-school-name') || 'this school';
      if (!decisionSelect) {
        return;
      }
      var syncSchoolRequestNoteState = function () {
        var decision = decisionSelect.value || 'assign_only';
        var needsNote = decision === 'more_info_needed' || decision === 'rejected';
        var isApproved = decision === 'approved';
        if (noteWrap) {
          noteWrap.hidden = !needsNote;
        }
        if (noteInput) {
          noteInput.required = needsNote;
        }
        if (convertWrap) {
          convertWrap.hidden = !isApproved;
        }
        if (convertToClientField) {
          if (isApproved) {
            convertToClientField.value = (convertCheckbox && convertCheckbox.checked) ? '1' : '0';
          } else {
            convertToClientField.value = '0';
          }
        }
      };
      decisionSelect.addEventListener('change', syncSchoolRequestNoteState);
      if (convertCheckbox) {
        convertCheckbox.addEventListener('change', syncSchoolRequestNoteState);
      }
      form.addEventListener('submit', function (event) {
        var decision = decisionSelect.value || 'assign_only';
        var needsNote = decision === 'more_info_needed' || decision === 'rejected';
        if (noteInput) {
          noteInput.required = needsNote;
          if (needsNote && !noteInput.value.trim()) {
            event.preventDefault();
            window.alert(decision === 'rejected' ? 'Please add a reason for declining.' : 'Please add what information is needed.');
            noteInput.focus();
            return;
          }
        }
        if (convertToClientField) {
          if (decision === 'approved') {
            convertToClientField.value = (convertCheckbox && convertCheckbox.checked) ? '1' : '0';
          } else {
            convertToClientField.value = '0';
          }
        }
      });
      syncSchoolRequestNoteState();
    });
  }

  var schoolRequestChatRoots = document.querySelectorAll('[data-school-request-chat]');
  if (schoolRequestChatRoots.length && window.cmnPortal && window.cmnPortal.ajaxUrl) {
    schoolRequestChatRoots.forEach(function (chatRoot) {
      var entityType = chatRoot.getAttribute('data-entity-type') || 'school_request';
      var entityId = parseInt(chatRoot.getAttribute('data-entity-id') || '0', 10);
      if (!entityId) {
        return;
      }
      var viewerRole = chatRoot.getAttribute('data-viewer-role') || 'staff';
      var messagesWrap = chatRoot.querySelector('[data-thread-messages]');
      var form = chatRoot.querySelector('[data-thread-form]');
      var messageInput = form ? form.querySelector('textarea[name="body"]') : null;
      var attachmentInput = form ? form.querySelector('input[name="attachments[]"]') : null;
      var sendButton = form ? form.querySelector('button[type="submit"]') : null;
      var feedbackEl = chatRoot.querySelector('[data-thread-feedback]');
      var statusEl = chatRoot.querySelector('[data-thread-status]');
      var unreadEl = chatRoot.querySelector('[data-thread-unread]');
      var setStatusButtons = chatRoot.querySelectorAll('[data-thread-set-status]');
      var pollSeconds = parseInt((window.cmnPortal && window.cmnPortal.threadPollSeconds) || '20', 10);
      if (!pollSeconds || pollSeconds < 10) {
        pollSeconds = 20;
      }
      var basePollMs = pollSeconds * 1000;
      var pollDelayMs = basePollMs;
      var maxPollMs = 120000;
      var pollTimer = null;
      var activeToken = chatRoot.getAttribute('data-thread-token') || '';
      var threadNonce = (window.cmnPortal && window.cmnPortal.threadNonce) ? String(window.cmnPortal.threadNonce) : '';
      var requestInFlight = false;
      var sendInFlight = false;
      var terminalError = false;

      var setFeedback = function (message, isError) {
        if (!feedbackEl) {
          return;
        }
        feedbackEl.textContent = message || '';
        feedbackEl.classList.toggle('is-error', !!isError);
      };

      var setComposerEnabled = function (enabled) {
        if (messageInput) {
          messageInput.disabled = !enabled;
        }
        if (sendButton) {
          sendButton.disabled = !enabled;
        }
        if (attachmentInput) {
          attachmentInput.disabled = !enabled;
        }
      };

      var escapeHtml = function (value) {
        return String(value || '').replace(/[&<>\"']/g, function (char) {
          if (char === '&') {
            return '&amp;';
          }
          if (char === '<') {
            return '&lt;';
          }
          if (char === '>') {
            return '&gt;';
          }
          if (char === '"') {
            return '&quot;';
          }
          return '&#39;';
        });
      };


      var escapeAttr = function (value) {
        return escapeHtml(value);
      };

      var isNearBottom = function () {
        if (!messagesWrap) {
          return true;
        }
        var remaining = messagesWrap.scrollHeight - (messagesWrap.scrollTop + messagesWrap.clientHeight);
        return remaining < 80;
      };

      var scrollMessagesToBottom = function () {
        if (!messagesWrap) {
          return;
        }
        messagesWrap.scrollTop = messagesWrap.scrollHeight;
      };

      var applyRotationToken = function (payload) {
        if (!payload || typeof payload !== 'object') {
          return;
        }
        if (payload.token) {
          activeToken = String(payload.token);
          chatRoot.setAttribute('data-thread-token', activeToken);
        }
      };

      var renderMessages = function (messages) {
        if (!messagesWrap) {
          return;
        }
        if (!Array.isArray(messages) || !messages.length) {
          messagesWrap.innerHTML = '<div class="cmn-empty">No messages yet.</div>';
          return;
        }
        var shouldScroll = isNearBottom();
        var html = '';
        messages.forEach(function (message) {
          var bubbleClass = String(message && message.bubble_class ? message.bubble_class : 'is-system');
          var sender = String(message && message.sender_label ? message.sender_label : 'System');
          var created = String(message && message.created_at_label ? message.created_at_label : '');
          var body = String(message && message.body ? message.body : '');
          var attachments = Array.isArray(message && message.attachments) ? message.attachments : [];
          html += '<div class="cmn-support-bubble cmn-booking-bubble ' + escapeHtml(bubbleClass) + '">';
          html += '<div class="cmn-support-meta">' + escapeHtml(sender + (created ? (' - ' + created) : '')) + '</div>';
          html += '<div class="cmn-support-text">' + escapeHtml(body).replace(/\n/g, '<br>') + '</div>';
          if (attachments.length) {
            html += '<div class="cmn-support-attachments">';
            attachments.forEach(function (attachment) {
              var url = attachment && attachment.url ? String(attachment.url) : '';
              var filename = attachment && attachment.filename ? String(attachment.filename) : 'Attachment';
              if (!url) {
                return;
              }
              html += '<a class="cmn-support-attachment-chip" href="' + escapeAttr(url) + '" target="_blank" rel="noopener noreferrer">' + escapeHtml(filename) + '</a>';
            });
            html += '</div>';
          }
          html += '</div>';
        });
        messagesWrap.innerHTML = html;
        if (shouldScroll) {
          scrollMessagesToBottom();
        }
      };

      var applyStatusState = function (thread) {
        var status = String(thread && thread.status ? thread.status : 'open');
        var isClosed = status === 'closed';
        if (statusEl) {
          statusEl.classList.remove('is-warning', 'is-verified');
          statusEl.classList.add(isClosed ? 'is-warning' : 'is-verified');
          statusEl.textContent = String(thread && thread.status_label ? thread.status_label : (isClosed ? 'Closed' : 'Open'));
        }
        if (unreadEl) {
          var unread = parseInt((thread && thread.unread_count) || '0', 10) || 0;
          unreadEl.textContent = unread > 0 ? (unread + ' unread') : 'No unread';
          unreadEl.classList.toggle('is-muted', unread < 1);
        }
        if (setStatusButtons && setStatusButtons.length) {
          setStatusButtons.forEach(function (button) {
            var target = button.getAttribute('data-thread-set-status') || '';
            button.disabled = sendInFlight || target === status;
          });
        }
        if (viewerRole === 'school' && isClosed) {
          setFeedback('Thread is closed right now. Sending a message will reopen it.', false);
        }
      };

      var applyThread = function (thread) {
        if (!thread || typeof thread !== 'object') {
          return;
        }
        if (thread.thread_id) {
          chatRoot.setAttribute('data-thread-id', String(thread.thread_id));
        }
        renderMessages(thread.messages || []);
        applyStatusState(thread);
      };

      var sendThreadRequest = function (actionName, payload) {
        var fd = new FormData();
        fd.append('action', actionName);
        fd.append('entity_type', entityType);
        fd.append('entity_id', String(entityId));
        if (activeToken) {
          fd.append('token', activeToken);
        }
        if (threadNonce) {
          fd.append('nonce', threadNonce);
        }
        if (payload && typeof payload === 'object') {
          Object.keys(payload).forEach(function (key) {
            var value = payload[key];
            if (value === undefined || value === null) {
              return;
            }
            if (key === 'attachments' && Array.isArray(value)) {
              value.forEach(function (file) {
                if (file instanceof File) {
                  fd.append('attachments[]', file, file.name || 'attachment');
                }
              });
              return;
            }
            fd.append(key, String(value));
          });
        }
        return fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
        }).then(function (response) {
          return response.text();
        }).then(function (text) {
          try {
            return JSON.parse(text);
          } catch (error) {
            return {
              success: false,
              data: {
                message: 'Unexpected server response.'
              }
            };
          }
        });
      };

      var scheduleNextPoll = function (delayMs) {
        if (pollTimer) {
          window.clearTimeout(pollTimer);
        }
        pollTimer = window.setTimeout(function () {
          if (terminalError) {
            return;
          }
          if (document.hidden) {
            scheduleNextPoll(basePollMs);
            return;
          }
          refreshThread(false).then(function () {
            pollDelayMs = basePollMs;
            scheduleNextPoll(basePollMs);
          }).catch(function () {
            pollDelayMs = Math.min(maxPollMs, Math.max(basePollMs, pollDelayMs * 2));
            scheduleNextPoll(pollDelayMs);
          });
        }, Math.max(1500, delayMs));
      };

      var refreshThread = function (showErrors) {
        if (requestInFlight || terminalError) {
          return Promise.resolve();
        }
        requestInFlight = true;
        return sendThreadRequest('cmn_thread_get', {}).then(function (data) {
          requestInFlight = false;
          if (!data || !data.success || !data.data) {
            var message = (data && data.data && data.data.message) ? String(data.data.message) : 'Unable to refresh chat.';
            if (showErrors) {
              setFeedback(message, true);
            }
            var lowered = message.toLowerCase();
            if (lowered.indexOf('expired') !== -1 || lowered.indexOf('unauthorized') !== -1 || lowered.indexOf('invalid secure chat link') !== -1) {
              terminalError = true;
              setComposerEnabled(false);
            }
            return Promise.reject(new Error(message));
          }
          applyRotationToken(data.data);
          applyThread(data.data.thread || {});
          if (!sendInFlight && (!viewerRole || viewerRole !== 'school' || String((data.data.thread || {}).status || 'open') !== 'closed')) {
            setFeedback('', false);
          }
          return data;
        }).catch(function (error) {
          requestInFlight = false;
          if (showErrors) {
            setFeedback(error && error.message ? error.message : 'Unable to refresh chat.', true);
          }
          return Promise.reject(error);
        });
      };

      if (form && messageInput) {
        form.addEventListener('submit', function (event) {
          event.preventDefault();
          if (sendInFlight || terminalError) {
            return;
          }
          var body = messageInput.value ? messageInput.value.trim() : '';
          var files = [];
          if (attachmentInput && attachmentInput.files && attachmentInput.files.length) {
            files = Array.prototype.slice.call(attachmentInput.files);
          }
          if (!body && !files.length) {
            setFeedback('Message or attachment is required.', true);
            messageInput.focus();
            return;
          }
          sendInFlight = true;
          setComposerEnabled(false);
          setFeedback('Sending message...', false);
          sendThreadRequest('cmn_thread_post_message', {
            body: body,
            attachments: files,
          }).then(function (data) {
            sendInFlight = false;
            setComposerEnabled(true);
            if (!data || !data.success || !data.data) {
              setFeedback((data && data.data && data.data.message) ? String(data.data.message) : 'Unable to send message.', true);
              return;
            }
            applyRotationToken(data.data);
            applyThread(data.data.thread || {});
            messageInput.value = '';
            if (attachmentInput) {
              attachmentInput.value = '';
            }
            var attachmentErrors = Array.isArray(data.data.attachment_errors) ? data.data.attachment_errors : [];
            if (attachmentErrors.length) {
              setFeedback('Message sent. Some files were skipped: ' + attachmentErrors.join('; '), true);
            } else {
              setFeedback('Message sent.', false);
            }
            pollDelayMs = basePollMs;
            scheduleNextPoll(basePollMs);
          }).catch(function () {
            sendInFlight = false;
            setComposerEnabled(true);
            setFeedback('Unable to send message.', true);
          });
        });
      }

      if (setStatusButtons && setStatusButtons.length) {
        setStatusButtons.forEach(function (button) {
          button.addEventListener('click', function () {
            if (sendInFlight || terminalError) {
              return;
            }
            var nextStatus = button.getAttribute('data-thread-set-status') || '';
            if (!nextStatus) {
              return;
            }
            sendInFlight = true;
            setStatusButtons.forEach(function (innerButton) {
              innerButton.disabled = true;
            });
            setFeedback('Updating thread status...', false);
            sendThreadRequest('cmn_thread_set_status', {
              status: nextStatus,
            }).then(function (data) {
              sendInFlight = false;
              if (!data || !data.success || !data.data) {
                setFeedback((data && data.data && data.data.message) ? String(data.data.message) : 'Unable to update thread status.', true);
                setStatusButtons.forEach(function (innerButton) {
                  innerButton.disabled = false;
                });
                return;
              }
              applyThread(data.data.thread || {});
              setFeedback('Thread status updated.', false);
              pollDelayMs = basePollMs;
              scheduleNextPoll(basePollMs);
            }).catch(function () {
              sendInFlight = false;
              setFeedback('Unable to update thread status.', true);
              setStatusButtons.forEach(function (innerButton) {
                innerButton.disabled = false;
              });
            });
          });
        });
      }

      document.addEventListener('visibilitychange', function () {
        if (document.hidden || terminalError) {
          return;
        }
        refreshThread(false).then(function () {
          pollDelayMs = basePollMs;
          scheduleNextPoll(basePollMs);
        }).catch(function () {
          pollDelayMs = Math.min(maxPollMs, Math.max(basePollMs, pollDelayMs * 2));
          scheduleNextPoll(pollDelayMs);
        });
      });

      refreshThread(false).then(function () {
        scheduleNextPoll(basePollMs);
      }).catch(function () {
        scheduleNextPoll(basePollMs);
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
      var availabilityDot = document.querySelector('[data-availability-dot]');
      var availabilityDotLabel = document.querySelector('[data-availability-dot-label]');
      var unavailableButton = document.querySelector('[data-availability-unavailable-button]');
      var availabilityConfirmedPill = document.querySelector('[data-availability-confirmed-pill]');
      var availabilityImpact = document.querySelector('[data-availability-impact]');
      var availabilitySocial = document.querySelector('[data-availability-social]');
      var calendarBlocked = availabilityButton.getAttribute('data-calendar-blocked') === '1';
      var availabilityPeriodLabel = availabilityButton.getAttribute('data-availability-period-label') || 'tomorrow morning';
      var confirmActionLabel = (availabilityButton.textContent || '').trim();
      if (!confirmActionLabel) {
        confirmActionLabel = "CONFIRM I'M AVAILABLE " + availabilityPeriodLabel.toUpperCase();
      }
      var unavailableActionLabel = unavailableButton ? (unavailableButton.textContent || '').trim() : '';
      if (!unavailableActionLabel) {
        unavailableActionLabel = "I'M NOT AVAILABLE " + availabilityPeriodLabel.toUpperCase();
      }
      var changeActionLabel = 'Change to NOT AVAILABLE';
      var changeFromUnavailableLabel = 'Change availability';
      var confirmedPillLabel = 'AVAILABILITY CONFIRMED';
      var countdownEl = document.querySelector('[data-availability-countdown]');
      var openAtRaw = availabilityButton.getAttribute('data-availability-open-at') || '';
      var closeAtRaw = availabilityButton.getAttribute('data-availability-close-at') || '';
      var openAtTs = openAtRaw ? Date.parse(openAtRaw) : NaN;
      var closeAtTs = closeAtRaw ? Date.parse(closeAtRaw) : NaN;
      var availabilityNextOpenLabel = (availabilityButton.getAttribute('data-availability-next-open-label') || '').trim();
      if (!availabilityNextOpenLabel && !Number.isNaN(openAtTs)) {
        try {
          var openLabelDay = new Date(openAtTs).toLocaleDateString('en-US', { weekday: 'long' }).toUpperCase();
          if (openLabelDay) {
            availabilityNextOpenLabel = 'Confirm from ' + openLabelDay + ' 7:00PM';
          }
        } catch (e) {}
      }
      var availabilityCountdownTimer = null;
      var unlockAtRaw = availabilityButton.getAttribute('data-availability-unlock-at') || '';
      var unlockAtTs = unlockAtRaw ? Date.parse(unlockAtRaw) : NaN;
      var availabilityUnlockTimer = null;
      var availabilityPrimaryButtonLabel = (availabilityButton.textContent || '').trim();
      var availabilityConfirmedCount = NaN;
      if (availabilitySocial) {
        var availabilityConfirmedMatch = (availabilitySocial.textContent || '').match(/(\d+)/);
        if (availabilityConfirmedMatch && availabilityConfirmedMatch[1]) {
          availabilityConfirmedCount = parseInt(availabilityConfirmedMatch[1], 10);
        }
      }
      if (Number.isNaN(availabilityConfirmedCount)) {
        availabilityConfirmedCount = 0;
      }
      var availabilityLabelSwapTimer = null;
      var availabilityLabelSwapState = false;
      availabilityButton.style.pointerEvents = 'auto';
      var syncAvailabilityButtonLabel = function () {
        if (!availabilityPrimaryButtonLabel) {
          availabilityPrimaryButtonLabel = (availabilityButton.textContent || '').trim();
        }
        if (availabilityButton.disabled && availabilityNextOpenLabel) {
          if (!availabilityLabelSwapTimer) {
            availabilityLabelSwapState = false;
            availabilityButton.textContent = availabilityPrimaryButtonLabel;
            availabilityLabelSwapTimer = window.setInterval(function () {
              if (!availabilityButton.disabled || !availabilityNextOpenLabel) {
                if (availabilityLabelSwapTimer) {
                  window.clearInterval(availabilityLabelSwapTimer);
                  availabilityLabelSwapTimer = null;
                }
                availabilityButton.textContent = availabilityPrimaryButtonLabel;
                return;
              }
              availabilityLabelSwapState = !availabilityLabelSwapState;
              availabilityButton.textContent = availabilityLabelSwapState ? availabilityNextOpenLabel : availabilityPrimaryButtonLabel;
            }, 2200);
          }
          return;
        }
        if (availabilityLabelSwapTimer) {
          window.clearInterval(availabilityLabelSwapTimer);
          availabilityLabelSwapTimer = null;
        }
        availabilityButton.textContent = availabilityPrimaryButtonLabel;
      };
      var setAvailabilityButtonLabel = function (text) {
        availabilityPrimaryButtonLabel = (text || '').trim();
        availabilityButton.textContent = availabilityPrimaryButtonLabel;
      };
      var getConfirmedCountFromPayload = function (payload) {
        if (!payload || typeof payload.confirmed_count === 'undefined') {
          return NaN;
        }
        var parsedCount = parseInt(payload.confirmed_count, 10);
        return Number.isNaN(parsedCount) ? NaN : Math.max(0, parsedCount);
      };
      var renderAvailabilityConfirmedCount = function () {
        if (!availabilitySocial) {
          return;
        }
        availabilitySocial.textContent = '🔥 ' + availabilityConfirmedCount + ' candidates have already confirmed';
      };
      var setAvailabilityActionVisibility = function (stateKey) {
        var isConfirmedAvailable = stateKey === 'confirmed_available';
        var isConfirmedNotAvailable = stateKey === 'confirmed_not_available';
        var isWindowClosed = stateKey === 'window_closed';
        if (isConfirmedAvailable || isConfirmedNotAvailable) {
          availabilityButton.setAttribute('hidden', 'hidden');
          availabilityButton.setAttribute('aria-hidden', 'true');
        } else {
          availabilityButton.removeAttribute('hidden');
          availabilityButton.removeAttribute('aria-hidden');
        }
        if (availabilityConfirmedPill) {
          availabilityConfirmedPill.textContent = confirmedPillLabel;
          if (isConfirmedAvailable) {
            availabilityConfirmedPill.removeAttribute('hidden');
          } else {
            availabilityConfirmedPill.setAttribute('hidden', 'hidden');
          }
        }
        if (!unavailableButton) {
          return;
        }
        if (isWindowClosed) {
          unavailableButton.setAttribute('hidden', 'hidden');
          unavailableButton.setAttribute('aria-hidden', 'true');
          unavailableButton.textContent = unavailableActionLabel;
          return;
        }
        unavailableButton.removeAttribute('hidden');
        unavailableButton.removeAttribute('aria-hidden');
        if (isConfirmedAvailable) {
          unavailableButton.textContent = changeActionLabel;
        } else if (isConfirmedNotAvailable) {
          unavailableButton.textContent = changeFromUnavailableLabel;
        } else {
          unavailableButton.textContent = unavailableActionLabel;
        }
      };
      var renderAvailabilityCountdown = function () {
        if (!countdownEl || Number.isNaN(openAtTs) || Number.isNaN(closeAtTs)) {
          return;
        }
        var nowMs = Date.now();
        if (nowMs < openAtTs) {
          var toOpen = Math.max(0, Math.floor((openAtTs - nowMs) / 1000));
          var hOpen = Math.floor(toOpen / 3600);
          var mOpen = Math.floor((toOpen % 3600) / 60);
          var sOpen = toOpen % 60;
          countdownEl.textContent = 'Opens in: ' + hOpen + 'h ' + mOpen + 'm ' + (sOpen < 10 ? '0' : '') + sOpen + 's';
          return;
        }
        var toClose = Math.max(0, Math.floor((closeAtTs - nowMs) / 1000));
        if (toClose <= 0) {
          countdownEl.textContent = 'Window closed at 7:30am.';
          return;
        }
        var h = Math.floor(toClose / 3600);
        var m = Math.floor((toClose % 3600) / 60);
        var s = toClose % 60;
        countdownEl.textContent = 'Time left to confirm: ' + h + 'h ' + m + 'm ' + (s < 10 ? '0' : '') + s + 's';
      };
      renderAvailabilityCountdown();
      availabilityCountdownTimer = window.setInterval(renderAvailabilityCountdown, 1000);
      if (availabilityCard && availabilityCard.classList.contains('is-blocked')) {
        if (availabilityDot && availabilityDotLabel) {
          availabilityDot.classList.remove('is-confirmed', 'is-neutral');
          availabilityDot.classList.add('is-blocked');
          availabilityDotLabel.textContent = "I'm not available";
        }
      }
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
        if (availabilityMessage && availabilityMessage.textContent.trim().toLowerCase() === 'not responded yet.') {
          availabilityMessage.textContent = 'Not responded yet';
        }
        if (availabilityUnlockTimer) {
          window.clearInterval(availabilityUnlockTimer);
          availabilityUnlockTimer = null;
        }
        syncAvailabilityButtonLabel();
      };
      maybeUnlockAvailabilityButton();
      if (availabilityButton.disabled && !calendarBlocked) {
        if (availabilityHelper && !availabilityHelper.textContent.trim()) {
          availabilityHelper.textContent = 'You can confirm availability from 7:00pm on the previous day until 7:30am.';
        }
      }
      if (availabilityButton.disabled && !Number.isNaN(unlockAtTs)) {
        availabilityUnlockTimer = window.setInterval(maybeUnlockAvailabilityButton, 30000);
      }
      syncAvailabilityButtonLabel();
      var setStatusDot = function (state, text) {
        if (!availabilityDot || !availabilityDotLabel) {
          return;
        }
        availabilityDot.classList.remove('is-confirmed', 'is-neutral', 'is-blocked');
        availabilityDot.classList.add(state);
        availabilityDotLabel.textContent = text;
      };

      var setAvailabilityVisualState = function (stateKey, options) {
        options = options || {};
        var previousState = '';
        if (availabilityCard) {
          previousState = (availabilityCard.getAttribute('data-availability-state') || '').trim();
        }
        if (!previousState) {
          previousState = (availabilityButton.getAttribute('data-availability-response-state') || '').trim();
        }
        var wasAvailable = previousState
          ? previousState === 'confirmed_available'
          : availabilityButton.getAttribute('data-available') === '1';
        if (!stateKey) {
          stateKey = previousState || (wasAvailable ? 'confirmed_available' : 'unconfirmed');
        }
        var isAvailable = stateKey === 'confirmed_available';
        var isBlockedState = stateKey === 'confirmed_not_available';
        var isWindowClosedState = stateKey === 'window_closed';
        availabilityButton.setAttribute('data-available', isAvailable ? '1' : '0');
        availabilityButton.setAttribute('data-availability-response-state', stateKey);
        setAvailabilityButtonLabel(confirmActionLabel);
        setAvailabilityActionVisibility(stateKey);
        if (availabilityCard) {
          availabilityCard.setAttribute('data-availability-state', stateKey);
          availabilityCard.classList.toggle('is-confirmed', isAvailable);
          availabilityCard.classList.toggle('is-blocked', isBlockedState);
          availabilityCard.classList.toggle('is-window-closed', isWindowClosedState);
        }
        if (isBlockedState) {
          setStatusDot('is-blocked', 'Marked as not available');
          if (availabilityMessage) {
            availabilityMessage.textContent = 'Marked as not available';
          }
          if (availabilityImpact) {
            availabilityImpact.textContent = 'You will appear lower in manager searches.';
          }
        } else if (isWindowClosedState) {
          setStatusDot('is-neutral', 'Window closed');
          if (availabilityMessage) {
            availabilityMessage.textContent = 'Window closed';
          }
          if (availabilityImpact) {
            availabilityImpact.textContent = 'You will appear lower in manager searches.';
          }
        } else if (isAvailable) {
          setStatusDot('is-confirmed', 'Confirmed');
          if (availabilityMessage) {
            availabilityMessage.textContent = 'Availability confirmed';
          }
          if (availabilityImpact) {
            availabilityImpact.textContent = 'You appear at the top of manager searches.';
          }
        } else {
          setStatusDot('is-neutral', 'Not responded yet');
          if (availabilityMessage) {
            availabilityMessage.textContent = 'Not responded yet';
          }
          if (availabilityImpact) {
            availabilityImpact.textContent = 'You will appear lower in manager searches.';
          }
        }
        if (typeof options.confirmedCount === 'number' && !Number.isNaN(options.confirmedCount)) {
          availabilityConfirmedCount = Math.max(0, options.confirmedCount);
        } else if (wasAvailable !== isAvailable) {
          availabilityConfirmedCount = Math.max(0, availabilityConfirmedCount + (isAvailable ? 1 : -1));
        }
        renderAvailabilityConfirmedCount();
      };
      setAvailabilityButtonLabel(confirmActionLabel);
      if (unavailableButton) {
        unavailableButton.textContent = unavailableActionLabel;
      }
      var initialStateKey = availabilityCard ? (availabilityCard.getAttribute('data-availability-state') || '').trim() : '';
      if (!initialStateKey) {
        initialStateKey = (availabilityButton.getAttribute('data-availability-response-state') || '').trim();
      }
      if (!initialStateKey) {
        initialStateKey = availabilityButton.getAttribute('data-available') === '1' ? 'confirmed_available' : 'unconfirmed';
      }
      setAvailabilityVisualState(initialStateKey, { confirmedCount: availabilityConfirmedCount });
      if (unavailableButton) {
        unavailableButton.addEventListener('click', function () {
          unavailableButton.disabled = true;
          var currentStateKey = '';
          if (availabilityCard) {
            currentStateKey = (availabilityCard.getAttribute('data-availability-state') || '').trim();
          }
          if (!currentStateKey) {
            currentStateKey = (availabilityButton.getAttribute('data-availability-response-state') || '').trim();
          }
          var unavailableAction = currentStateKey === 'confirmed_not_available'
            ? 'cmn_mark_available'
            : 'cmn_mark_unavailable_morning';
          var formDataUnavailable = new FormData();
          formDataUnavailable.append('action', unavailableAction);
          formDataUnavailable.append('nonce', availabilityNonce);
          fetch(availabilityAjaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formDataUnavailable,
          })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              unavailableButton.disabled = false;
              if (data && data.success) {
                var nextState = (data.data && data.data.response_state) || (unavailableAction === 'cmn_mark_available' ? 'confirmed_available' : 'confirmed_not_available');
                setAvailabilityVisualState(nextState, { confirmedCount: getConfirmedCountFromPayload(data.data) });
                if (data.data && typeof data.data.button_enabled !== 'undefined') {
                  availabilityButton.disabled = !data.data.button_enabled;
                } else {
                  availabilityButton.disabled = false;
                }
                if (availabilityHelper) {
                  availabilityHelper.textContent = '';
                }
                syncAvailabilityButtonLabel();
              } else if (availabilityMessage) {
                availabilityMessage.textContent = data && data.data && data.data.message ? data.data.message : 'Unable to update availability.';
                if (data && data.data && typeof data.data.response_state === 'string') {
                  setAvailabilityVisualState(data.data.response_state, { confirmedCount: getConfirmedCountFromPayload(data.data) });
                }
              }
            })
            .catch(function () {
              unavailableButton.disabled = false;
              if (availabilityMessage) {
                availabilityMessage.textContent = 'Unable to update availability.';
              }
            });
        });
      }

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
              var nextStateKey = (data.data && typeof data.data.response_state === 'string' && data.data.response_state)
                ? data.data.response_state
                : 'confirmed_available';
              setAvailabilityVisualState(nextStateKey, { confirmedCount: getConfirmedCountFromPayload(data.data) });
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
              syncAvailabilityButtonLabel();
            } else {
              availabilityButton.disabled = false;
              if (availabilityMessage) {
                availabilityMessage.textContent = data && data.data && data.data.message ? data.data.message : 'Unable to save availability.';
              }
              if (data && data.data && typeof data.data.response_state === 'string') {
                setAvailabilityVisualState(data.data.response_state, { confirmedCount: getConfirmedCountFromPayload(data.data) });
              }
              if (data && data.data && typeof data.data.button_enabled !== 'undefined') {
                availabilityButton.disabled = !data.data.button_enabled;
              }
              if (availabilityHelper && data && data.data && data.data.message) {
                availabilityHelper.textContent = data.data.message;
              }
              if (availabilityCard && data && data.data && data.data.message && data.data.message.toLowerCase().indexOf('unavailable') !== -1) {
                availabilityCard.classList.add('is-blocked');
              }
              syncAvailabilityButtonLabel();
            }
          })
          .catch(function () {
            availabilityButton.disabled = false;
            if (availabilityMessage) {
              availabilityMessage.textContent = 'Unable to save availability.';
            }
            syncAvailabilityButtonLabel();
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

  var acceptWarningForms = document.querySelectorAll('[data-cmn-accept-warning]');
  if (acceptWarningForms.length) {
    acceptWarningForms.forEach(function (form) {
      form.addEventListener('submit', function (event) {
        var ok = window.confirm('You are about to be placed into a documented booking chat with the school and CoverMeNow. Please do not discuss pay in booking chat. Continue?');
        if (!ok) {
          event.preventDefault();
        }
      });
    });
  }

  var initSchoolCandidateDecks = function () {
    var deckRoots = document.querySelectorAll('[data-school-candidate-deck]');
    if (!deckRoots.length) {
      return;
    }
    deckRoots.forEach(function (deckRoot) {
      if (deckRoot.getAttribute('data-deck-initialized') === '1') {
        return;
      }
      deckRoot.setAttribute('data-deck-initialized', '1');
      var carousel = deckRoot.querySelector('[data-deck-carousel]');
      var prevBtn = deckRoot.querySelector('[data-deck-prev]');
      var nextBtn = deckRoot.querySelector('[data-deck-next]');
      var stack = deckRoot.querySelector('[data-deck-stack]');
      if (!stack) {
        return;
      }
      var cards = Array.prototype.slice.call(stack.querySelectorAll('[data-deck-card]'));
      if (!cards.length) {
        return;
      }
      deckRoot.classList.add('is-ready');
      var dotsWrap = deckRoot.querySelector('[data-deck-dots]');
      var dots = dotsWrap ? Array.prototype.slice.call(dotsWrap.querySelectorAll('[data-deck-dot]')) : [];
      var activeIndex = 0;
      var dragState = {
        id: null,
        startX: 0,
        startY: 0,
        dx: 0,
        dragging: false,
      };

      var getWrappedRelative = function (index) {
        var total = cards.length;
        if (total < 2) {
          return 0;
        }
        var relative = index - activeIndex;
        if (relative > total / 2) {
          relative -= total;
        } else if (relative < -(total / 2)) {
          relative += total;
        }
        return relative;
      };

      var rebuildDotState = function () {
        if (!dotsWrap) {
          return;
        }
        dots = Array.prototype.slice.call(dotsWrap.querySelectorAll('[data-deck-dot]'));
        dots.forEach(function (dot, idx) {
          dot.setAttribute('data-deck-index', String(idx));
          dot.classList.toggle('is-active', idx === activeIndex);
        });
      };

      var updateArrowState = function () {
        var canNavigate = cards.length > 1;
        if (prevBtn) {
          prevBtn.disabled = !canNavigate;
          prevBtn.hidden = !canNavigate;
        }
        if (nextBtn) {
          nextBtn.disabled = !canNavigate;
          nextBtn.hidden = !canNavigate;
        }
      };

      var renderStack = function () {
        if (!cards.length) {
          stack.classList.add('is-empty');
          if (dotsWrap) {
            dotsWrap.hidden = true;
          }
          updateArrowState();
          return;
        }
        stack.classList.remove('is-empty');
        if (dotsWrap) {
          dotsWrap.hidden = cards.length < 2;
        }
        if (activeIndex < 0) {
          activeIndex = cards.length - 1;
        } else if (activeIndex >= cards.length) {
          activeIndex = 0;
        }
        var isCompactViewport = window.matchMedia('(max-width: 720px)').matches;
        var stackWidth = Math.max(320, stack.clientWidth || 320);
        var sideOffset = Math.max(260, Math.min(620, Math.round(stackWidth * (stackWidth > 1100 ? 0.52 : 0.5))));
        cards.forEach(function (card, idx) {
          var relative = getWrappedRelative(idx);
          var isActive = relative === 0;
          var isPrev = relative === -1;
          var isNext = relative === 1;
          var isVisible = isCompactViewport ? isActive : (isActive || isPrev || isNext);
          card.classList.toggle('is-active', isActive);
          card.classList.toggle('is-prev', isPrev);
          card.classList.toggle('is-next', isNext);
          card.classList.toggle('is-stacked', !isActive);
          card.classList.toggle('is-hidden', !isVisible);
          if (!isVisible) {
            var hiddenDirection = relative < 0 ? -1 : 1;
            card.style.opacity = '0';
            card.style.pointerEvents = 'none';
            card.style.transform = 'translate3d(calc(-50% + ' + (hiddenDirection * (sideOffset + 220)) + 'px), 28px, 0) scale(0.84)';
            card.style.zIndex = '0';
            return;
          }
          if (isActive) {
            card.style.opacity = '1';
            card.style.pointerEvents = 'auto';
            card.style.transform = 'translate3d(-50%, 0, 0) scale(1)';
            card.style.zIndex = '500';
            return;
          }
          var direction = relative < 0 ? -1 : 1;
          card.style.opacity = '0.96';
          card.style.pointerEvents = 'none';
          card.style.transform = 'translate3d(calc(-50% + ' + (direction * sideOffset) + 'px), 16px, 0) scale(0.9)';
          card.style.zIndex = '320';
        });
        updateArrowState();
        rebuildDotState();
      };

      var moveTo = function (index) {
        if (!cards.length) {
          return;
        }
        if (index < 0) {
          activeIndex = cards.length - 1;
        } else if (index >= cards.length) {
          activeIndex = 0;
        } else {
          activeIndex = index;
        }
        renderStack();
      };

      var moveNext = function () {
        moveTo(activeIndex + 1);
      };

      var movePrev = function () {
        moveTo(activeIndex - 1);
      };

      var dismissActive = function () {
        if (!cards.length) {
          return;
        }
        var removed = cards.splice(activeIndex, 1)[0];
        if (removed && removed.parentNode === stack) {
          removed.parentNode.removeChild(removed);
        }
        if (dotsWrap) {
          var dotToRemove = dots[activeIndex];
          if (dotToRemove && dotToRemove.parentNode === dotsWrap) {
            dotToRemove.parentNode.removeChild(dotToRemove);
          }
        }
        if (activeIndex >= cards.length) {
          activeIndex = 0;
        }
        renderStack();
      };

      var bindCardPointer = function (card) {
        card.addEventListener('pointerdown', function (event) {
          if (!card.classList.contains('is-active')) {
            return;
          }
          if (event.target && event.target.closest && event.target.closest('button, a, input, select, textarea, label')) {
            return;
          }
          dragState.id = event.pointerId;
          dragState.startX = event.clientX;
          dragState.startY = event.clientY;
          dragState.dx = 0;
          dragState.dragging = true;
          try {
            card.setPointerCapture(event.pointerId);
          } catch (_error) {}
        });

        card.addEventListener('pointermove', function (event) {
          if (!dragState.dragging || dragState.id !== event.pointerId || !card.classList.contains('is-active')) {
            return;
          }
          var dx = event.clientX - dragState.startX;
          var dy = event.clientY - dragState.startY;
          if (Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > 14) {
            return;
          }
          dragState.dx = dx;
          card.style.transform = 'translate3d(calc(-50% + ' + dx + 'px), 0, 0) rotate(' + (dx / 18) + 'deg)';
          card.style.opacity = String(Math.max(0.45, 1 - (Math.abs(dx) / 260)));
        });

        var finishDrag = function (event) {
          if (!dragState.dragging || dragState.id !== event.pointerId) {
            return;
          }
          dragState.dragging = false;
          var dx = dragState.dx;
          dragState.dx = 0;
          try {
            card.releasePointerCapture(event.pointerId);
          } catch (_error) {}
          if (Math.abs(dx) >= 90) {
            if (dx < 0) {
              moveNext();
            } else {
              movePrev();
            }
            return;
          }
          renderStack();
        };

        card.addEventListener('pointerup', finishDrag);
        card.addEventListener('pointercancel', finishDrag);
      };
      cards.forEach(bindCardPointer);

      deckRoot.addEventListener('click', function (event) {
        var prev = event.target && event.target.closest ? event.target.closest('[data-deck-prev]') : null;
        if (prev && prevBtn && prevBtn.contains(prev)) {
          movePrev();
          return;
        }
        var next = event.target && event.target.closest ? event.target.closest('[data-deck-next]') : null;
        if (next && nextBtn && nextBtn.contains(next)) {
          moveNext();
          return;
        }
        var dot = event.target && event.target.closest ? event.target.closest('[data-deck-dot]') : null;
        if (dot && dotsWrap && dotsWrap.contains(dot)) {
          var dotIndex = parseInt(dot.getAttribute('data-deck-index') || '-1', 10);
          if (dotIndex >= 0 && dotIndex < cards.length) {
            moveTo(dotIndex);
          }
          return;
        }
        var actionButton = event.target && event.target.closest ? event.target.closest('[data-deck-action]') : null;
        if (!actionButton) {
          return;
        }
        var activeCard = cards[activeIndex];
        if (!activeCard || !activeCard.contains(actionButton)) {
          return;
        }
        var action = actionButton.getAttribute('data-deck-action') || '';
        if (action === 'dismiss') {
          dismissActive();
          return;
        }
        if (action === 'maybe') {
          moveNext();
        }
      });

      if (carousel) {
        carousel.addEventListener('keydown', function (event) {
          if (event.key === 'ArrowLeft') {
            event.preventDefault();
            movePrev();
            return;
          }
          if (event.key === 'ArrowRight') {
            event.preventDefault();
            moveNext();
          }
        });
      }

      window.addEventListener('resize', renderStack);
      renderStack();
    });
  };
  initSchoolCandidateDecks();

  var initSchoolLiveMatches = function () {
    var root = document.querySelector('[data-live-matches-root]');
    if (!root || root.getAttribute('data-live-init') === '1') {
      return;
    }
    root.setAttribute('data-live-init', '1');

    var initialScript = root.querySelector('[data-live-initial]');
    var initialPayload = {};
    try {
      initialPayload = initialScript ? JSON.parse(initialScript.textContent || '{}') : {};
    } catch (e) {
      initialPayload = {};
    }

    var defaultFilters = {
      radius: 30,
      availability: 'all',
      roles: [],
      compliance: [],
      skills: [],
      search: ''
    };
    var tabs = ['all_candidates', 'available_now', 'not_responded', 'shortlist'];
    var state = {
      tab: tabs.indexOf(String(initialPayload.tab || 'all_candidates')) >= 0 ? String(initialPayload.tab || 'all_candidates') : 'all_candidates',
      filters: Object.assign({}, defaultFilters, initialPayload.filters || {}),
      counts: Object.assign({
        all_candidates: 0,
        available_now: 0,
        not_responded: 0,
        shortlist: 0
      }, initialPayload.counts || {}),
      candidates: Array.isArray(initialPayload.candidates) ? initialPayload.candidates.slice() : [],
      stats: Object.assign({
        candidates_found: 0,
        available_now: 0,
        not_responded: 0,
        avg_response_minutes: 12
      }, initialPayload.stats || {}),
      total: Math.max(0, parseInt(initialPayload.total || 0, 10) || 0),
      limit: Math.max(6, parseInt(initialPayload.limit || 24, 10) || 24),
      hasMore: !!initialPayload.has_more,
      notInterestedDays: Math.max(1, parseInt(initialPayload.not_interested_days || 14, 10) || 14),
      activeIndexByTab: {},
      loading: false,
      requestInFlight: false,
      loadMoreInFlight: false,
      timersTick: 0
    };
    tabs.forEach(function (tabKey) {
      state.activeIndexByTab[tabKey] = 0;
    });

    var storageKey = 'cmn_live_matches_state_' + String(initialPayload.school_id || '0');
    var hasAjax = !!(window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.liveMatchesNonce);
    var carousel = root.querySelector('[data-live-carousel]');
    var stackEl = root.querySelector('[data-live-stack]');
    var dotsEl = root.querySelector('[data-live-dots]');
    var loadingEl = root.querySelector('[data-live-loading]');
    var emptyEl = root.querySelector('[data-live-empty]');
    var emptyRadiusEl = root.querySelector('[data-live-empty-radius]');
    var carouselShell = root.querySelector('[data-live-carousel-shell]');
    var errorEl = root.querySelector('[data-live-error]');
    var prevBtn = root.querySelector('[data-live-prev]');
    var nextBtn = root.querySelector('[data-live-next]');
    var retryBtn = root.querySelector('[data-live-retry]');
    var tabButtons = Array.prototype.slice.call(root.querySelectorAll('[data-live-tab]'));
    var countEls = root.querySelectorAll('[data-live-count]');
    var radiusLabelEl = root.querySelector('[data-live-radius-label]');
    var filterChipsEl = root.querySelector('[data-live-filter-chips]');
    var kpiTotal = root.querySelector('[data-live-kpi-total]');
    var kpiAvailable = root.querySelector('[data-live-kpi-available]');
    var kpiPending = root.querySelector('[data-live-kpi-pending]');
    var kpiResponse = root.querySelector('[data-live-kpi-response]');
    var openFiltersBtns = Array.prototype.slice.call(root.querySelectorAll('[data-live-open-filters]'));
    var closeFiltersBtns = Array.prototype.slice.call(root.querySelectorAll('[data-live-close-filters]'));
    var drawer = root.querySelector('[data-live-drawer]');
    var applyFiltersBtn = root.querySelector('[data-live-apply-filters]');
    var clearFiltersBtn = root.querySelector('[data-live-clear-filters]');
    var resetFiltersBtn = root.querySelector('[data-live-reset-filters]');
    var requestMultipleBtn = root.querySelector('[data-live-request-multiple]');
    var broadcastBtn = root.querySelector('[data-live-broadcast]');
    var radiusInput = root.querySelector('[data-live-filter-radius]');
    var radiusValueEl = root.querySelector('[data-live-radius-value]');
    var roleInputs = Array.prototype.slice.call(root.querySelectorAll('[data-live-filter-role]'));
    var complianceInputs = Array.prototype.slice.call(root.querySelectorAll('[data-live-filter-compliance]'));
    var skillInputs = Array.prototype.slice.call(root.querySelectorAll('[data-live-filter-skill]'));
    var availabilityInputs = Array.prototype.slice.call(root.querySelectorAll('[data-live-filter-availability]'));
    var searchInput = root.querySelector('[data-live-filter-search]');
    var timerInterval = null;

    var sanitizeArray = function (value) {
      if (!Array.isArray(value)) {
        return [];
      }
      return value.map(function (item) {
        return String(item || '').trim().toLowerCase();
      }).filter(Boolean).filter(function (item, idx, arr) {
        return arr.indexOf(item) === idx;
      });
    };
    var sanitizeFilters = function (value) {
      var raw = value && typeof value === 'object' ? value : {};
      var radius = parseInt(raw.radius || 30, 10);
      if (!radius || radius < 5) {
        radius = 5;
      } else if (radius > 30) {
        radius = 30;
      }
      var availability = String(raw.availability || 'all').trim().toLowerCase();
      if (['all', 'now', 'morning', 'afternoon', 'tomorrow'].indexOf(availability) === -1) {
        availability = 'all';
      }
      return {
        radius: radius,
        availability: availability,
        roles: sanitizeArray(raw.roles).filter(function (item) {
          return ['qts', 'cover_supervisor', 'hlta', 'ta'].indexOf(item) >= 0;
        }),
        compliance: sanitizeArray(raw.compliance).filter(function (item) {
          return ['dbs_verified', 'id_verified', 'qts_verified'].indexOf(item) >= 0;
        }),
        skills: sanitizeArray(raw.skills).filter(function (item) {
          return ['classroom_management', 'communication', 'first_aid'].indexOf(item) >= 0;
        }),
        search: String(raw.search || '').trim()
      };
    };
    state.filters = sanitizeFilters(state.filters);
    var initialTab = tabs.indexOf(String(initialPayload.tab || 'all_candidates')) >= 0 ? String(initialPayload.tab || 'all_candidates') : 'all_candidates';
    var initialFilters = sanitizeFilters(initialPayload.filters || {});

    var getActiveIndex = function () {
      var idx = parseInt(state.activeIndexByTab[state.tab] || 0, 10);
      if (!isFinite(idx) || idx < 0) {
        idx = 0;
      }
      if (idx >= state.candidates.length) {
        idx = Math.max(0, state.candidates.length - 1);
      }
      state.activeIndexByTab[state.tab] = idx;
      return idx;
    };
    var setActiveIndex = function (idx) {
      var next = parseInt(idx || 0, 10);
      if (!isFinite(next) || next < 0) {
        next = 0;
      }
      if (next >= state.candidates.length) {
        next = Math.max(0, state.candidates.length - 1);
      }
      state.activeIndexByTab[state.tab] = next;
    };

    var buildRouteStateParams = function () {
      var params = new URLSearchParams(window.location.search || '');
      params.set('cmn_live_tab', state.tab);
      params.set('cmn_live_radius', String(state.filters.radius || 30));
      params.set('cmn_live_availability', String(state.filters.availability || 'all'));
      params.set('cmn_live_idx', String(getActiveIndex()));
      if (state.filters.roles.length) {
        params.set('cmn_live_roles', state.filters.roles.join(','));
      } else {
        params.delete('cmn_live_roles');
      }
      if (state.filters.compliance.length) {
        params.set('cmn_live_compliance', state.filters.compliance.join(','));
      } else {
        params.delete('cmn_live_compliance');
      }
      if (state.filters.skills.length) {
        params.set('cmn_live_skills', state.filters.skills.join(','));
      } else {
        params.delete('cmn_live_skills');
      }
      if (state.filters.search) {
        params.set('cmn_live_search', state.filters.search);
      } else {
        params.delete('cmn_live_search');
      }
      return params;
    };

    var syncRouteState = function () {
      if (!window.history || !window.history.replaceState) {
        return;
      }
      try {
        var url = new URL(window.location.href);
        url.search = buildRouteStateParams().toString();
        window.history.replaceState(window.history.state, '', url.toString());
      } catch (e) {
        // Ignore URL state sync issues.
      }
    };

    var parseRouteState = function () {
      var parsed = {
        hasExplicit: false,
        tab: '',
        filters: null,
        activeIndex: null
      };
      try {
        var params = new URLSearchParams(window.location.search || '');
        parsed.hasExplicit = params.has('cmn_live_tab')
          || params.has('cmn_live_radius')
          || params.has('cmn_live_availability')
          || params.has('cmn_live_roles')
          || params.has('cmn_live_compliance')
          || params.has('cmn_live_skills')
          || params.has('cmn_live_search')
          || params.has('cmn_live_idx');
        if (!parsed.hasExplicit) {
          return parsed;
        }
        var tab = String(params.get('cmn_live_tab') || '').trim();
        if (tabs.indexOf(tab) >= 0) {
          parsed.tab = tab;
        }
        parsed.filters = sanitizeFilters({
          radius: params.get('cmn_live_radius') || state.filters.radius,
          availability: params.get('cmn_live_availability') || state.filters.availability,
          roles: String(params.get('cmn_live_roles') || '').split(','),
          compliance: String(params.get('cmn_live_compliance') || '').split(','),
          skills: String(params.get('cmn_live_skills') || '').split(','),
          search: String(params.get('cmn_live_search') || '')
        });
        var idx = parseInt(params.get('cmn_live_idx') || '', 10);
        if (isFinite(idx) && idx >= 0) {
          parsed.activeIndex = idx;
        }
      } catch (e) {
        parsed.hasExplicit = false;
      }
      return parsed;
    };

    var saveState = function () {
      try {
        window.sessionStorage.setItem(storageKey, JSON.stringify({
          tab: state.tab,
          filters: state.filters,
          activeIndexByTab: state.activeIndexByTab,
          ts: Date.now()
        }));
      } catch (e) {
        // Ignore storage errors.
      }
    };
    var restoreState = function () {
      var routeState = parseRouteState();
      if (routeState.hasExplicit) {
        if (routeState.tab && tabs.indexOf(routeState.tab) >= 0) {
          state.tab = routeState.tab;
        }
        if (routeState.filters) {
          state.filters = sanitizeFilters(routeState.filters);
        }
        if (routeState.activeIndex !== null) {
          state.activeIndexByTab[state.tab] = routeState.activeIndex;
        }
        return;
      }
      // Default entry should always open the operational view on All Candidates
      // to avoid stale session tab/filter state hiding not-responded matches.
      state.tab = 'all_candidates';
      state.filters = sanitizeFilters(defaultFilters);
      tabs.forEach(function (tabKey) {
        state.activeIndexByTab[tabKey] = 0;
      });
    };
    restoreState();
    var initialCandidates = Array.isArray(initialPayload.candidates) ? initialPayload.candidates : [];
    var shouldFetchInitial = !!hasAjax && (
      !Array.isArray(initialPayload.candidates)
      || initialCandidates.length === 0
      || String(state.tab) !== String(initialTab)
      || JSON.stringify(state.filters || {}) !== JSON.stringify(initialFilters || {})
    );

    var cardStatusLabel = function (candidate) {
      return String(candidate && candidate.status_label ? candidate.status_label : 'NOT RESPONDED');
    };
    var cardStatusClass = function (candidate) {
      return String(candidate && candidate.status_class ? candidate.status_class : 'is-not-responded');
    };
    var buildCandidateCard = function (candidate, idx) {
      var fullName = String(candidate.name || 'Candidate').trim();
      var firstName = String(candidate.first_name || '').trim();
      if (!firstName) {
        firstName = fullName.split(/\s+/).filter(Boolean)[0] || 'Candidate';
      }
      firstName = firstName.split(/\s+/).filter(Boolean)[0] || 'Candidate';
      var roleLine = String(candidate.role_line || 'Cover Supervisor');
      var ratingValue = String(candidate.rating_value || '0.0');
      var ratingFloat = parseFloat(candidate.rating_float || ratingValue || '0');
      if (!isFinite(ratingFloat) || ratingFloat < 0) {
        ratingFloat = 0;
      }
      var reviewCount = parseInt(candidate.review_count || 0, 10);
      var reviewLabel = reviewCount > 0 ? ('(' + reviewCount + ')') : '(0)';
      var roundedStars = Math.max(0, Math.min(5, Math.round(ratingFloat)));
      var distance = String(candidate.distance_label || 'Distance unknown');
      var statusLabel = cardStatusLabel(candidate);
      var statusClass = cardStatusClass(candidate);
      var stateCardClass = statusLabel === 'AVAILABLE NOW' ? 'is-available-state' : 'is-not-responded-state';
      var availabilityLabel = String(candidate.availability_label || 'Awaiting response');
      var confirmedAt = String(candidate.confirmed_at || '').trim();
      var availabilityText = confirmedAt ? (availabilityLabel + ' • Confirmed at ' + confirmedAt) : availabilityLabel;
      var rateLabel = String(candidate.day_rate_label || 'Rate on request');
      var avatarUrl = String(candidate.avatar_url || (window.cmnPortal && window.cmnPortal.avatarFallbackUrl ? window.cmnPortal.avatarFallbackUrl : ''));
      var requestEnabled = !!candidate.request_enabled;
      var requestId = parseInt(candidate.existing_request_id || 0, 10);
      var requestStatus = String(candidate.request_status || '').toLowerCase();
      var hasOpenOffer = requestId > 0 && (requestStatus === '' || requestStatus === 'requested' || requestStatus === 'pending');
      var offerExpiresAt = String(candidate.request_expires_at || '').trim();
      var shortlistActive = !!candidate.is_shortlisted;
      var profileUrl = String(candidate.profile_url || '#');
      var skills = Array.isArray(candidate.skills) ? candidate.skills.slice(0, 3) : ['Classroom Management', 'Communication', 'First Aid'];
      var statusAria = statusLabel === 'AVAILABLE NOW' ? 'Candidate available now' : 'Candidate not responded yet';
      var starsHtml = '<span class="cmn-live-stars" aria-hidden="true">' + [0, 1, 2, 3, 4].map(function (starIndex) {
        return '<span class="' + (starIndex < roundedStars ? 'is-on' : 'is-off') + '">★</span>';
      }).join('') + '</span>';
      var offerTimerHtml = hasOpenOffer && offerExpiresAt
        ? '<div class="cmn-live-offer-timer" data-live-offer-expires="' + offerExpiresAt.replace(/"/g, '&quot;') + '" role="status" aria-live="polite" aria-label="Offer expires in --:--">Offer expires in --:--</div>'
        : '<div class="cmn-live-offer-timer is-hidden" aria-hidden="true"></div>';
      return '' +
        '<article class="cmn-live-match-card ' + stateCardClass + '" data-live-card data-live-index="' + idx + '" data-candidate-id="' + String(candidate.candidate_id || candidate.id || 0) + '" data-live-state="' + String(statusLabel).toLowerCase().replace(/\s+/g, '_') + '">' +
          '<div class="cmn-live-match-card__head">' +
            '<div class="cmn-live-match-card__avatar"><img src="' + avatarUrl.replace(/"/g, '&quot;') + '" alt="' + fullName.replace(/"/g, '&quot;') + '"></div>' +
            '<div class="cmn-live-match-card__identity">' +
              '<h3>' + firstName + ' <span class="cmn-live-verified" aria-label="Verified">✓</span></h3>' +
              '<p class="cmn-live-role-line">' + roleLine + '</p>' +
              '<p class="cmn-live-rating">' + starsHtml + ' <strong>' + ratingValue + '</strong> <span>' + reviewLabel + '</span></p>' +
            '</div>' +
            '<div class="cmn-live-match-card__meta">' +
              '<span class="cmn-live-status ' + statusClass + '" aria-label="' + statusAria + '">' + statusLabel + '</span>' +
              '<span class="cmn-live-distance">Distance: ' + distance + '</span>' +
            '</div>' +
          '</div>' +
          '<div class="cmn-live-availability-strip ' + (statusLabel === 'AVAILABLE NOW' ? 'is-live' : 'is-pending') + '">' + availabilityText + '</div>' +
          '<div class="cmn-live-rate-row">' + rateLabel + ' <span aria-hidden="true">▾</span></div>' +
          '<div class="cmn-live-skill-row">' +
            skills.map(function (skill) {
              return '<span class="cmn-live-skill-chip">' + String(skill) + '</span>';
            }).join('') +
          '</div>' +
          offerTimerHtml +
          '<div class="cmn-live-match-card__actions">' +
            '<button type="button" class="cmn-primary cmn-btn-mini" data-live-book-now' + (requestEnabled && !hasOpenOffer ? '' : ' disabled') + '>' + (hasOpenOffer ? 'Offer sent' : 'Book Now') + '</button>' +
            '<button type="button" class="cmn-ghost cmn-btn-mini' + (shortlistActive ? ' is-active' : '') + '" data-live-shortlist><span class="cmn-live-btn-icon" aria-hidden="true">✋</span>' + (shortlistActive ? 'Shortlisted' : 'Shortlist') + '</button>' +
            '<button type="button" class="cmn-live-not-interested cmn-btn-mini" data-live-not-interested>Not Interested</button>' +
            '<a class="cmn-ghost cmn-btn-mini cmn-live-view-profile" href="' + profileUrl.replace(/"/g, '&quot;') + '" data-live-view-profile>View Profile</a>' +
          '</div>' +
          '<div class="cmn-live-side-cta" aria-hidden="true">View Profile</div>' +
          '<div class="cmn-live-card-message" data-live-card-msg></div>' +
        '</article>';
    };

    var setError = function (message, tone) {
      if (!errorEl) {
        return;
      }
      var msg = String(message || '').trim();
      if (!msg) {
        errorEl.hidden = true;
        errorEl.classList.remove('is-success');
        return;
      }
      errorEl.hidden = false;
      errorEl.classList.toggle('is-success', String(tone || '') === 'success');
      var label = errorEl.querySelector('span');
      if (label) {
        label.textContent = msg;
      } else {
        errorEl.textContent = msg;
      }
    };
    var setLoading = function (isLoading) {
      state.loading = !!isLoading;
      if (loadingEl) {
        loadingEl.hidden = !state.loading;
      }
      if (prevBtn) {
        prevBtn.disabled = state.loading || state.candidates.length < 2;
      }
      if (nextBtn) {
        nextBtn.disabled = state.loading || state.candidates.length < 2;
      }
    };
    var syncKpis = function () {
      if (kpiTotal) {
        kpiTotal.textContent = String(state.stats.candidates_found || state.counts.all_candidates || 0);
      }
      if (kpiAvailable) {
        kpiAvailable.textContent = String(state.stats.available_now || state.counts.available_now || 0);
      }
      if (kpiPending) {
        kpiPending.textContent = String(state.stats.not_responded || state.counts.not_responded || 0);
      }
      if (kpiResponse) {
        kpiResponse.textContent = String(state.stats.avg_response_minutes || 12) + ' min';
      }
    };
    var syncCounts = function () {
      countEls.forEach(function (el) {
        var key = String(el.getAttribute('data-live-count') || '').trim();
        if (!key) {
          return;
        }
        el.textContent = String(state.counts[key] || 0);
      });
      tabButtons.forEach(function (btn) {
        var key = String(btn.getAttribute('data-live-tab') || '').trim();
        btn.classList.toggle('is-active', key === state.tab);
        btn.setAttribute('aria-selected', key === state.tab ? 'true' : 'false');
      });
      if (radiusLabelEl) {
        radiusLabelEl.textContent = String(state.filters.radius || 30);
      }
      if (emptyRadiusEl) {
        emptyRadiusEl.textContent = String(state.filters.radius || 30);
      }
      syncKpis();
    };
    var syncFilterControls = function () {
      if (radiusInput) {
        radiusInput.value = String(state.filters.radius || 30);
      }
      if (radiusValueEl) {
        radiusValueEl.textContent = String(state.filters.radius || 30);
      }
      roleInputs.forEach(function (input) {
        input.checked = state.filters.roles.indexOf(String(input.value || '').trim()) >= 0;
      });
      complianceInputs.forEach(function (input) {
        input.checked = state.filters.compliance.indexOf(String(input.value || '').trim()) >= 0;
      });
      skillInputs.forEach(function (input) {
        input.checked = state.filters.skills.indexOf(String(input.value || '').trim()) >= 0;
      });
      availabilityInputs.forEach(function (input) {
        input.checked = String(input.value || '') === String(state.filters.availability || 'all');
      });
      if (searchInput) {
        searchInput.value = String(state.filters.search || '');
      }
    };
    var renderFilterChips = function () {
      if (!filterChipsEl) {
        return;
      }
      var chips = [];
      chips.push('Radius: ' + String(state.filters.radius || 30) + ' miles');
      if (state.filters.availability && state.filters.availability !== 'all') {
        chips.push('Availability: ' + state.filters.availability.replace('_', ' '));
      }
      state.filters.roles.forEach(function (role) {
        chips.push(role.replace('_', ' '));
      });
      state.filters.compliance.forEach(function (item) {
        chips.push(item.replace('_', ' '));
      });
      state.filters.skills.forEach(function (item) {
        chips.push(item.replace('_', ' '));
      });
      if (state.filters.search) {
        chips.push('Search: ' + state.filters.search);
      }
      filterChipsEl.innerHTML = chips.map(function (chip) {
        return '<span class="cmn-live-filter-chip">' + chip + '</span>';
      }).join('');
    };
    var getRelative = function (index, activeIndex, total) {
      if (total < 2) {
        return 0;
      }
      var relative = index - activeIndex;
      if (relative > total / 2) {
        relative -= total;
      } else if (relative < -(total / 2)) {
        relative += total;
      }
      return relative;
    };
    var renderDots = function () {
      if (!dotsEl) {
        return;
      }
      if (!state.candidates.length) {
        dotsEl.innerHTML = '';
        return;
      }
      var activeIndex = getActiveIndex();
      dotsEl.innerHTML = state.candidates.map(function (_candidate, idx) {
        var active = idx === activeIndex ? ' is-active' : '';
        return '<button type="button" class="cmn-school-candidate-deck-dot' + active + '" data-live-dot data-live-index="' + idx + '" aria-label="Candidate ' + (idx + 1) + '"></button>';
      }).join('');
    };
    var prefetchAdjacentAvatars = function () {
      if (!state.candidates.length) {
        return;
      }
      var activeIndex = getActiveIndex();
      var ids = [activeIndex - 1, activeIndex + 1];
      ids.forEach(function (idx) {
        var wrapped = idx;
        if (wrapped < 0) {
          wrapped = state.candidates.length - 1;
        } else if (wrapped >= state.candidates.length) {
          wrapped = 0;
        }
        var candidate = state.candidates[wrapped];
        if (!candidate || !candidate.avatar_url) {
          return;
        }
        var img = new Image();
        img.src = String(candidate.avatar_url);
      });
    };
    var renderStackTransforms = function () {
      if (!stackEl) {
        return;
      }
      var cards = Array.prototype.slice.call(stackEl.querySelectorAll('[data-live-card]'));
      var activeIndex = getActiveIndex();
      var total = cards.length;
      var compact = window.matchMedia('(max-width: 840px)').matches;
      var width = Math.max(320, stackEl.clientWidth || 320);
      var offset = Math.max(220, Math.min(540, Math.round(width * 0.45)));
      cards.forEach(function (card, idx) {
        var relative = getRelative(idx, activeIndex, total);
        var isActive = relative === 0;
        var isPrev = relative === -1;
        var isNext = relative === 1;
        var visible = compact ? isActive : (isActive || isPrev || isNext);
        card.classList.toggle('is-active', isActive);
        card.classList.toggle('is-prev', isPrev);
        card.classList.toggle('is-next', isNext);
        card.classList.toggle('is-hidden', !visible);
        if (!visible) {
          card.style.opacity = '0';
          card.style.pointerEvents = 'none';
          card.style.transform = 'translate3d(calc(-50% + ' + (relative < 0 ? -1 : 1) * (offset + 180) + 'px), 30px, 0) scale(0.84)';
          card.style.zIndex = '1';
          return;
        }
        if (isActive) {
          card.style.opacity = '1';
          card.style.pointerEvents = 'auto';
          card.style.transform = 'translate3d(-50%, 0, 0) scale(1)';
          card.style.zIndex = '30';
          return;
        }
        card.style.opacity = '0.8';
        card.style.pointerEvents = 'auto';
        card.style.transform = 'translate3d(calc(-50% + ' + (relative < 0 ? -offset : offset) + 'px), 16px, 0) scale(0.9)';
        card.style.zIndex = '12';
      });
      renderDots();
      prefetchAdjacentAvatars();
      saveState();
    };
    var renderCards = function () {
      if (!stackEl) {
        return;
      }
      if (!state.candidates.length) {
        stackEl.innerHTML = '';
        if (emptyEl) {
          emptyEl.hidden = false;
        }
        if (carouselShell) {
          carouselShell.hidden = true;
        }
        if (dotsEl) {
          dotsEl.innerHTML = '';
        }
        return;
      }
      if (emptyEl) {
        emptyEl.hidden = true;
      }
      if (carouselShell) {
        carouselShell.hidden = false;
      }
      stackEl.innerHTML = state.candidates.map(buildCandidateCard).join('');
      renderStackTransforms();
      refreshOfferTimers();
    };
    var moveTo = function (idx) {
      if (!state.candidates.length) {
        return;
      }
      setActiveIndex(idx);
      renderStackTransforms();
      if (state.hasMore && getActiveIndex() >= Math.max(0, state.candidates.length - 3)) {
        loadMoreFeed();
      }
    };
    var moveNext = function () {
      moveTo(getActiveIndex() + 1);
    };
    var movePrev = function () {
      moveTo(getActiveIndex() - 1);
    };
    var readFiltersFromDrawer = function () {
      var next = sanitizeFilters({
        radius: radiusInput ? parseInt(radiusInput.value || state.filters.radius || 30, 10) : state.filters.radius,
        availability: (availabilityInputs.find(function (input) { return input.checked; }) || {}).value || 'all',
        roles: roleInputs.filter(function (input) { return input.checked; }).map(function (input) { return input.value; }),
        compliance: complianceInputs.filter(function (input) { return input.checked; }).map(function (input) { return input.value; }),
        skills: skillInputs.filter(function (input) { return input.checked; }).map(function (input) { return input.value; }),
        search: searchInput ? searchInput.value : state.filters.search
      });
      return next;
    };
    var setDrawerOpen = function (open) {
      if (!drawer) {
        return;
      }
      drawer.hidden = !open;
      drawer.classList.toggle('is-open', !!open);
      if (open && radiusInput) {
        radiusInput.focus();
      }
    };
    var updateFromPayload = function (payload, options) {
      var opts = options || {};
      state.tab = tabs.indexOf(String(payload.tab || state.tab)) >= 0 ? String(payload.tab || state.tab) : state.tab;
      state.filters = sanitizeFilters(payload.filters || state.filters);
      state.counts = Object.assign(state.counts, payload.counts || {});
      state.stats = Object.assign(state.stats, payload.stats || {});
      var incoming = Array.isArray(payload.candidates) ? payload.candidates.slice() : [];
      if (opts.append && state.candidates.length) {
        var seen = {};
        state.candidates.forEach(function (candidate) {
          seen[String(candidate && (candidate.candidate_id || candidate.id || ''))] = true;
        });
        incoming.forEach(function (candidate) {
          var candidateKey = String(candidate && (candidate.candidate_id || candidate.id || ''));
          if (!candidateKey || seen[candidateKey]) {
            return;
          }
          seen[candidateKey] = true;
          state.candidates.push(candidate);
        });
      } else {
        state.candidates = incoming;
      }
      state.total = Math.max(0, parseInt(payload.total || state.total || state.candidates.length, 10) || 0);
      state.limit = Math.max(6, parseInt(payload.limit || state.limit || 24, 10) || 24);
      state.hasMore = !!payload.has_more;
      state.notInterestedDays = Math.max(1, parseInt(payload.not_interested_days || state.notInterestedDays || 14, 10) || 14);
      if (opts.resetIndex) {
        state.activeIndexByTab[state.tab] = 0;
      } else {
        setActiveIndex(state.activeIndexByTab[state.tab] || 0);
      }
      syncCounts();
      syncFilterControls();
      renderFilterChips();
      renderCards();
      saveState();
    };
    var postJson = function (action, payload) {
      var formData = new FormData();
      formData.append('action', action);
      formData.append('nonce', window.cmnPortal.liveMatchesNonce || '');
      Object.keys(payload || {}).forEach(function (key) {
        var value = payload[key];
        if (Array.isArray(value) || (value && typeof value === 'object')) {
          formData.append(key, JSON.stringify(value));
        } else if (value !== undefined && value !== null) {
          formData.append(key, String(value));
        }
      });
      return fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      }).then(function (response) {
        return response.json();
      });
    };
    var fetchFeed = function (options) {
      var opts = options || {};
      if (!hasAjax || state.requestInFlight || state.loadMoreInFlight) {
        return Promise.resolve(false);
      }
      state.requestInFlight = true;
      setLoading(true);
      setError('');
      return postJson('cmn_school_live_matches_feed', {
        tab: state.tab,
        filters: state.filters,
        limit: state.limit || 24,
        offset: 0
      }).then(function (data) {
        if (!data || !data.success || !data.data) {
          throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to load matches.');
        }
        updateFromPayload(data.data, { resetIndex: !!opts.resetIndex });
        return true;
      }).catch(function (error) {
        setError(error && error.message ? error.message : 'Unable to refresh live matches.');
        return false;
      }).finally(function () {
        state.requestInFlight = false;
        setLoading(false);
      });
    };
    var loadMoreFeed = function () {
      if (!hasAjax || state.requestInFlight || state.loadMoreInFlight || !state.hasMore) {
        return Promise.resolve(false);
      }
      var requestedTab = state.tab;
      var requestedFilters = JSON.stringify(state.filters || {});
      var requestedOffset = state.candidates.length;
      state.loadMoreInFlight = true;
      return postJson('cmn_school_live_matches_feed', {
        tab: requestedTab,
        filters: state.filters,
        limit: state.limit || 24,
        offset: requestedOffset
      }).then(function (data) {
        if (!data || !data.success || !data.data) {
          throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to load more matches.');
        }
        if (requestedTab !== state.tab || requestedFilters !== JSON.stringify(state.filters || {})) {
          return false;
        }
        updateFromPayload(data.data, { append: true, resetIndex: false });
        return true;
      }).catch(function () {
        return false;
      }).finally(function () {
        state.loadMoreInFlight = false;
      });
    };
    var updateCardMessage = function (card, text, tone) {
      if (!card) {
        return;
      }
      var msg = card.querySelector('[data-live-card-msg]');
      if (!msg) {
        return;
      }
      msg.textContent = String(text || '');
      msg.classList.remove('is-success', 'is-error');
      if (!text) {
        return;
      }
      msg.classList.add(tone === 'success' ? 'is-success' : 'is-error');
    };
    var secondsUntil = function (isoText) {
      var raw = String(isoText || '').trim();
      if (!raw) {
        return -1;
      }
      var ts = Date.parse(raw.replace(' ', 'T'));
      if (!isFinite(ts)) {
        return -1;
      }
      return Math.floor((ts - Date.now()) / 1000);
    };
    var refreshOfferTimers = function () {
      if (!stackEl) {
        return;
      }
      var timerEls = stackEl.querySelectorAll('[data-live-offer-expires]');
      timerEls.forEach(function (timerEl) {
        var seconds = secondsUntil(timerEl.getAttribute('data-live-offer-expires') || '');
        if (!isFinite(seconds) || seconds < 0) {
          timerEl.textContent = 'Offer expired';
          timerEl.setAttribute('aria-label', 'Offer expired');
          timerEl.classList.add('is-expired');
          return;
        }
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        var timerLabel = 'Offer expires in ' + String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        timerEl.textContent = timerLabel;
        timerEl.setAttribute('aria-label', timerLabel);
        timerEl.classList.remove('is-expired');
      });
    };
    var startTimerTick = function () {
      if (timerInterval) {
        window.clearInterval(timerInterval);
      }
      timerInterval = window.setInterval(refreshOfferTimers, 1000);
    };

    tabButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var tabKey = String(btn.getAttribute('data-live-tab') || '').trim();
        if (tabs.indexOf(tabKey) === -1 || tabKey === state.tab) {
          return;
        }
        var previousTab = state.tab;
        state.tab = tabKey;
        state.activeIndexByTab[tabKey] = state.activeIndexByTab[tabKey] || 0;
        fetchFeed({ resetIndex: false }).then(function (ok) {
          if (ok) {
            return;
          }
          state.tab = previousTab;
          syncCounts();
          renderCards();
          saveState();
        });
      });
    });
    if (prevBtn) {
      prevBtn.addEventListener('click', movePrev);
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', moveNext);
    }
    if (carousel) {
      carousel.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
          event.preventDefault();
          movePrev();
        } else if (event.key === 'ArrowRight') {
          event.preventDefault();
          moveNext();
        }
      });
      var swipe = {
        pointerId: null,
        startX: 0,
        startY: 0,
        deltaX: 0,
        active: false
      };
      carousel.addEventListener('pointerdown', function (event) {
        if (event.target && event.target.closest && event.target.closest('button, a, input, select, textarea')) {
          return;
        }
        swipe.pointerId = event.pointerId;
        swipe.startX = event.clientX;
        swipe.startY = event.clientY;
        swipe.deltaX = 0;
        swipe.active = true;
      });
      carousel.addEventListener('pointermove', function (event) {
        if (!swipe.active || swipe.pointerId !== event.pointerId) {
          return;
        }
        swipe.deltaX = event.clientX - swipe.startX;
      });
      var finishSwipe = function (event) {
        if (!swipe.active || swipe.pointerId !== event.pointerId) {
          return;
        }
        var deltaX = swipe.deltaX;
        var deltaY = event.clientY - swipe.startY;
        swipe.active = false;
        swipe.pointerId = null;
        swipe.deltaX = 0;
        if (Math.abs(deltaY) > Math.abs(deltaX)) {
          return;
        }
        if (deltaX <= -70) {
          moveNext();
        } else if (deltaX >= 70) {
          movePrev();
        }
      };
      carousel.addEventListener('pointerup', finishSwipe);
      carousel.addEventListener('pointercancel', finishSwipe);
    }
    if (retryBtn) {
      retryBtn.addEventListener('click', function () {
        fetchFeed({ resetIndex: false });
      });
    }
    openFiltersBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setDrawerOpen(true);
      });
    });
    closeFiltersBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setDrawerOpen(false);
      });
    });
    if (applyFiltersBtn) {
      applyFiltersBtn.addEventListener('click', function () {
        var previousFilters = sanitizeFilters(state.filters);
        var previousIndex = state.activeIndexByTab[state.tab] || 0;
        state.filters = readFiltersFromDrawer();
        state.activeIndexByTab[state.tab] = 0;
        setDrawerOpen(false);
        fetchFeed({ resetIndex: true }).then(function (ok) {
          if (ok) {
            return;
          }
          state.filters = previousFilters;
          state.activeIndexByTab[state.tab] = previousIndex;
          syncCounts();
          syncFilterControls();
          renderFilterChips();
          renderCards();
          saveState();
        });
      });
    }
    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener('click', function () {
        var previousFilters = sanitizeFilters(state.filters);
        var previousIndex = state.activeIndexByTab[state.tab] || 0;
        state.filters = Object.assign({}, defaultFilters);
        syncFilterControls();
        state.activeIndexByTab[state.tab] = 0;
        fetchFeed({ resetIndex: true }).then(function (ok) {
          if (ok) {
            return;
          }
          state.filters = previousFilters;
          state.activeIndexByTab[state.tab] = previousIndex;
          syncCounts();
          syncFilterControls();
          renderFilterChips();
          renderCards();
          saveState();
        });
      });
    }
    if (resetFiltersBtn) {
      resetFiltersBtn.addEventListener('click', function () {
        var previousFilters = sanitizeFilters(state.filters);
        var previousIndex = state.activeIndexByTab[state.tab] || 0;
        state.filters = Object.assign({}, defaultFilters);
        syncFilterControls();
        state.activeIndexByTab[state.tab] = 0;
        fetchFeed({ resetIndex: true }).then(function (ok) {
          if (ok) {
            return;
          }
          state.filters = previousFilters;
          state.activeIndexByTab[state.tab] = previousIndex;
          syncCounts();
          syncFilterControls();
          renderFilterChips();
          renderCards();
          saveState();
        });
      });
    }
    if (radiusInput && radiusValueEl) {
      radiusInput.addEventListener('input', function () {
        radiusValueEl.textContent = String(radiusInput.value || '30');
      });
    }

    var triggerBroadcast = function () {
      if (!hasAjax || state.requestInFlight) {
        return;
      }
      var ids = state.candidates.map(function (candidate) {
        return parseInt(candidate.candidate_id || candidate.id || 0, 10);
      }).filter(function (id) {
        return id > 0;
      });
      postJson('cmn_school_live_matches_broadcast_request', {
        tab: state.tab,
        filters: state.filters,
        candidate_ids: ids
      }).then(function (data) {
        if (!data || !data.success) {
          throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to log broadcast request.');
        }
        setError((data.data && data.data.message) ? data.data.message : 'Broadcast request logged.', 'success');
      }).catch(function (error) {
        setError(error && error.message ? error.message : 'Unable to log broadcast request.');
      });
    };
    if (requestMultipleBtn) {
      requestMultipleBtn.addEventListener('click', triggerBroadcast);
    }
    if (broadcastBtn) {
      broadcastBtn.addEventListener('click', triggerBroadcast);
    }

    root.addEventListener('click', function (event) {
      var dot = event.target.closest('[data-live-dot]');
      if (dot) {
        var dotIdx = parseInt(dot.getAttribute('data-live-index') || '0', 10);
        moveTo(dotIdx);
        return;
      }
      var card = event.target.closest('[data-live-card]');
      if (card && !event.target.closest('button, a')) {
        var cardIdx = parseInt(card.getAttribute('data-live-index') || '0', 10);
        if (cardIdx !== getActiveIndex()) {
          moveTo(cardIdx);
        }
        return;
      }

      var shortlistBtn = event.target.closest('[data-live-shortlist]');
      if (shortlistBtn) {
        var shortlistCard = shortlistBtn.closest('[data-live-card]');
        var shortlistId = parseInt(shortlistCard ? shortlistCard.getAttribute('data-candidate-id') : '0', 10);
        if (!shortlistId || !hasAjax) {
          return;
        }
        shortlistBtn.disabled = true;
        postJson('cmn_school_live_matches_shortlist_toggle', {
          candidate_id: shortlistId,
          tab: state.tab,
          filters: state.filters
        }).then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to update shortlist.');
          }
          fetchFeed({ resetIndex: false });
        }).catch(function (error) {
          updateCardMessage(shortlistCard, error && error.message ? error.message : 'Unable to update shortlist.', 'error');
        }).finally(function () {
          shortlistBtn.disabled = false;
        });
        return;
      }

      var notInterestedBtn = event.target.closest('[data-live-not-interested]');
      if (notInterestedBtn) {
        var dismissCard = notInterestedBtn.closest('[data-live-card]');
        var dismissId = parseInt(dismissCard ? dismissCard.getAttribute('data-candidate-id') : '0', 10);
        if (!dismissId || !hasAjax) {
          return;
        }
        if (!window.confirm('Hide this candidate from live matches for a while?')) {
          return;
        }
        var optionalReason = window.prompt('Optional reason for audit log (leave blank to skip):', '');
        if (optionalReason === null) {
          optionalReason = '';
        }
        notInterestedBtn.disabled = true;
        postJson('cmn_school_live_matches_not_interested', {
          candidate_id: dismissId,
          tab: state.tab,
          filters: state.filters,
          duration_days: state.notInterestedDays || 14,
          reason: String(optionalReason || '')
        }).then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to hide candidate.');
          }
          fetchFeed({ resetIndex: true });
        }).catch(function (error) {
          updateCardMessage(dismissCard, error && error.message ? error.message : 'Unable to hide candidate.', 'error');
        }).finally(function () {
          notInterestedBtn.disabled = false;
        });
        return;
      }

      var bookNowBtn = event.target.closest('[data-live-book-now]');
      if (bookNowBtn) {
        var bookCard = bookNowBtn.closest('[data-live-card]');
        var bookId = parseInt(bookCard ? bookCard.getAttribute('data-candidate-id') : '0', 10);
        if (!bookId || !hasAjax || bookNowBtn.disabled) {
          return;
        }
        var activeCandidate = state.candidates.find(function (candidate) {
          return parseInt(candidate.candidate_id || candidate.id || 0, 10) === bookId;
        });
        if (!activeCandidate || !activeCandidate.availability_date) {
          updateCardMessage(bookCard, 'Availability date missing for this candidate.', 'error');
          return;
        }
        bookNowBtn.disabled = true;
        updateCardMessage(bookCard, 'Sending offer...', 'success');
        var requestData = new FormData();
        requestData.append('action', 'cmn_request_candidate');
        requestData.append('nonce', window.cmnPortal.requestCandidateNonce || '');
        requestData.append('candidate_id', String(bookId));
        requestData.append('requested_date', String(activeCandidate.availability_date || ''));
        requestData.append('live_tab', state.tab);
        requestData.append('live_filters', JSON.stringify(state.filters || {}));
        fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: requestData
        }).then(function (response) {
          return response.json();
        }).then(function (data) {
          if (!data || !data.success) {
            throw new Error((data && data.data && data.data.message) ? data.data.message : 'Unable to create offer.');
          }
          updateCardMessage(bookCard, (data.data && data.data.message) ? data.data.message : 'Offer sent.', 'success');
          fetchFeed({ resetIndex: false });
        }).catch(function (error) {
          updateCardMessage(bookCard, error && error.message ? error.message : 'Unable to create offer.', 'error');
          bookNowBtn.disabled = false;
        });
        return;
      }

      var profileLink = event.target.closest('[data-live-view-profile]');
      if (profileLink) {
        saveState();
        try {
          var profileUrl = new URL(profileLink.href, window.location.origin);
          var liveParams = buildRouteStateParams();
          liveParams.forEach(function (value, key) {
            profileUrl.searchParams.set(key, value);
          });
          profileLink.href = profileUrl.toString();
        } catch (e) {
          // Ignore URL rewrite errors.
        }
      }
    });

    window.addEventListener('resize', renderStackTransforms);
    syncCounts();
    syncFilterControls();
    renderFilterChips();
    renderCards();
    startTimerTick();
    if (shouldFetchInitial) {
      fetchFeed({ resetIndex: false });
    }
  };
  initSchoolLiveMatches();

  var requestButtons = document.querySelectorAll('[data-request-candidate]');
  if (requestButtons.length && window.cmnPortal && window.cmnPortal.ajaxUrl) {
    requestButtons.forEach(function (btn) {
      var card = btn.closest('.cmn-available-card') || btn.parentElement;
      var roleSelect = card ? card.querySelector('[data-request-role-select]') : null;
      var schoolRateEl = card ? card.querySelector('[data-request-school-rate]') : null;
      var refreshRateDisplay = function () {
        if (!roleSelect || !schoolRateEl) {
          return;
        }
        var option = roleSelect.options[roleSelect.selectedIndex];
        var rawRate = option ? (option.getAttribute('data-school-rate') || '0') : '0';
        var parsedRate = parseFloat(rawRate || '0');
        if (!isFinite(parsedRate) || parsedRate < 0) {
          parsedRate = 0;
        }
        schoolRateEl.textContent = 'GBP ' + parsedRate.toFixed(2);
      };
      refreshRateDisplay();
      if (roleSelect) {
        roleSelect.addEventListener('change', refreshRateDisplay);
      }

      btn.addEventListener('click', function () {
        if (btn.disabled) {
          return;
        }
        btn.disabled = true;
        var messageEl = card ? card.querySelector('[data-request-message]') : null;
        if (messageEl) {
          messageEl.textContent = 'Sending request...';
        }

        var selectedRoleLabel = '';
        var selectedSchoolRate = '';
        var selectedCandidateRate = '';
        if (roleSelect && roleSelect.options.length) {
          var selectedOption = roleSelect.options[roleSelect.selectedIndex];
          selectedRoleLabel = selectedOption ? (selectedOption.value || '') : '';
          selectedSchoolRate = selectedOption ? (selectedOption.getAttribute('data-school-rate') || '') : '';
          selectedCandidateRate = selectedOption ? (selectedOption.getAttribute('data-candidate-rate') || '') : '';
        }

        var formData = new FormData();
        formData.append('action', 'cmn_request_candidate');
        formData.append('nonce', window.cmnPortal.requestCandidateNonce || '');
        formData.append('candidate_id', btn.getAttribute('data-candidate-id') || '');
        formData.append('requested_date', btn.getAttribute('data-request-date') || '');
        if (selectedRoleLabel) {
          formData.append('role_label', selectedRoleLabel);
        }
        if (selectedSchoolRate) {
          formData.append('school_charge_rate', selectedSchoolRate);
        }
        if (selectedCandidateRate) {
          formData.append('candidate_pay_rate', selectedCandidateRate);
        }
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
              btn.textContent = 'Request sent';
              btn.setAttribute('data-requested', '1');
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

  var partnerProgrammeRoot = document.querySelector('[data-partner-programme-root]');
  if (partnerProgrammeRoot) {
    var partnerStatsEndpoint = partnerProgrammeRoot.getAttribute('data-partner-stats-endpoint') || '';
    var partnerRestNonce = partnerProgrammeRoot.getAttribute('data-partner-rest-nonce') || '';
    var partnerErrorEl = partnerProgrammeRoot.querySelector('[data-partner-error]');
    var tierLabel = function (tier) {
      var key = String(tier || '').toLowerCase();
      if (!key) {
        return 'Standard';
      }
      return key.charAt(0).toUpperCase() + key.slice(1);
    };
    var formatCurrencyGbp = function (value) {
      var amount = parseFloat(value || '0');
      if (!isFinite(amount)) {
        amount = 0;
      }
      try {
        return new Intl.NumberFormat('en-GB', {
          style: 'currency',
          currency: 'GBP',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(amount);
      } catch (error) {
        return 'GBP ' + amount.toFixed(2);
      }
    };
    var formatWhole = function (value) {
      var amount = parseInt(value || '0', 10);
      if (!isFinite(amount)) {
        amount = 0;
      }
      try {
        return new Intl.NumberFormat('en-GB', {
          maximumFractionDigits: 0
        }).format(amount);
      } catch (error) {
        return String(amount);
      }
    };
    var formatPercent = function (value) {
      var amount = parseFloat(value || '0');
      if (!isFinite(amount)) {
        amount = 0;
      }
      var rounded = Math.round(amount * 100) / 100;
      var text = (Math.abs(rounded - Math.round(rounded)) < 0.0001) ? String(Math.round(rounded)) : rounded.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');
      return text + '%';
    };
    var setText = function (selector, value) {
      var node = partnerProgrammeRoot.querySelector(selector);
      if (node) {
        node.textContent = value;
      }
    };
    var setHidden = function (node, hidden) {
      if (!node) {
        return;
      }
      node.hidden = !!hidden;
      node.classList.toggle('is-hidden', !!hidden);
    };
    var getProgressData = function (credits) {
      var value = parseInt(credits || '0', 10);
      if (!isFinite(value) || value < 0) {
        value = 0;
      }
      if (value >= 1500) {
        return {
          topTier: true,
          nextTier: '',
          remaining: 0,
          progressPercent: 100
        };
      }
      var ranges = [
        { min: 700, max: 1500, next: 'Elite' },
        { min: 300, max: 700, next: 'Gold' },
        { min: 100, max: 300, next: 'Silver' },
        { min: 0, max: 100, next: 'Bronze' }
      ];
      var selected = ranges[ranges.length - 1];
      ranges.forEach(function (item) {
        if (value >= item.min && value < item.max) {
          selected = item;
        }
      });
      var span = Math.max(1, selected.max - selected.min);
      var pct = ((value - selected.min) / span) * 100;
      if (!isFinite(pct)) {
        pct = 0;
      }
      pct = Math.max(0, Math.min(100, pct));
      return {
        topTier: false,
        nextTier: selected.next,
        remaining: Math.max(0, selected.max - value),
        progressPercent: pct
      };
    };
    var renderProgress = function (credits) {
      var progress = getProgressData(credits);
      var topEl = partnerProgrammeRoot.querySelector('[data-partner-progress-top]');
      var bodyEl = partnerProgrammeRoot.querySelector('[data-partner-progress-body]');
      var fillEl = partnerProgrammeRoot.querySelector('[data-partner-progress-fill]');
      setHidden(topEl, !progress.topTier);
      setHidden(bodyEl, progress.topTier);
      if (progress.topTier) {
        return;
      }
      setText('[data-partner-next-tier]', progress.nextTier);
      setText('[data-partner-credits-remaining]', formatWhole(progress.remaining) + ' credits to next tier');
      if (fillEl) {
        fillEl.style.width = progress.progressPercent.toFixed(2) + '%';
      }
    };
    var renderStats = function (stats) {
      if (!stats || typeof stats !== 'object') {
        return;
      }
      setText('[data-partner-tier]', tierLabel(stats.tier));
      setText('[data-partner-tier-badge]', tierLabel(stats.tier));
      setText('[data-partner-lifetime-credits]', formatWhole(stats.lifetime_credits));
      setText('[data-partner-discount]', formatPercent(stats.discount_percent));
      setText('[data-partner-lifetime-savings]', formatCurrencyGbp(stats.lifetime_savings));
      setText('[data-partner-academic-savings]', formatCurrencyGbp(stats.academic_year_savings));
      var projectionStatus = String(stats.projection_status || 'insufficient_data').toLowerCase();
      if (projectionStatus === 'ok') {
        setText('[data-partner-projection-message]', 'Projected savings this academic year');
        setText('[data-partner-projected-savings]', formatCurrencyGbp(stats.projected_academic_year_savings));
      } else {
        setText('[data-partner-projection-message]', 'Projection available after 2 months of activity');
        setText('[data-partner-projected-savings]', '—');
      }
      if (stats.academic_year_label) {
        setText('[data-partner-academic-year-label]', 'Saved this academic year (' + String(stats.academic_year_label) + ')');
      }
      renderProgress(stats.lifetime_credits);
    };
    if (partnerStatsEndpoint) {
      var requestHeaders = {};
      if (partnerRestNonce) {
        requestHeaders['X-WP-Nonce'] = partnerRestNonce;
      }
      fetch(partnerStatsEndpoint, {
        method: 'GET',
        credentials: 'same-origin',
        headers: requestHeaders
      })
        .then(function (response) {
          return response.json().catch(function () {
            return {};
          }).then(function (payload) {
            if (!response.ok || !payload || payload.ok === false) {
              var message = (payload && payload.message) ? payload.message : 'Unable to load partner programme stats.';
              throw new Error(message);
            }
            return payload;
          });
        })
        .then(function (payload) {
          renderStats(payload);
          if (partnerErrorEl) {
            partnerErrorEl.hidden = true;
            partnerErrorEl.classList.add('is-hidden');
            partnerErrorEl.textContent = '';
          }
        })
        .catch(function (error) {
          if (partnerErrorEl) {
            partnerErrorEl.hidden = false;
            partnerErrorEl.classList.remove('is-hidden');
            partnerErrorEl.textContent = error && error.message ? error.message : 'Unable to load partner programme stats.';
          }
        });
    }
  }

  var candidateRewardsRoot = document.querySelector('[data-cmn-rewards-root]');
  if (candidateRewardsRoot) {
    var rewardsFetchAction = candidateRewardsRoot.getAttribute('data-cmn-rewards-fetch-action') || 'cmn_candidate_rewards_overview';
    var rewardsFetchNonce = candidateRewardsRoot.getAttribute('data-cmn-rewards-fetch-nonce') || ((window.cmnPortal && window.cmnPortal.candidateRewardsViewNonce) ? String(window.cmnPortal.candidateRewardsViewNonce) : '');
    var rewardsAppealAction = candidateRewardsRoot.getAttribute('data-cmn-rewards-appeal-action') || 'cmn_candidate_rewards_open_appeal';
    var rewardsAppealNonce = candidateRewardsRoot.getAttribute('data-cmn-rewards-appeal-nonce') || ((window.cmnPortal && window.cmnPortal.candidateRewardsAppealNonce) ? String(window.cmnPortal.candidateRewardsAppealNonce) : '');
    var rewardsErrorEl = candidateRewardsRoot.querySelector('[data-cmn-rewards-error]');
    var rewardsTierClasses = ['is-standard', 'is-bronze', 'is-silver', 'is-gold', 'is-elite'];
    var ajaxUrl = (window.cmnPortal && window.cmnPortal.ajaxUrl) ? String(window.cmnPortal.ajaxUrl) : '';

    var rewardsWhole = function (value) {
      var amount = parseInt(value || '0', 10);
      if (!isFinite(amount)) {
        amount = 0;
      }
      try {
        return new Intl.NumberFormat('en-GB', {
          maximumFractionDigits: 0
        }).format(amount);
      } catch (error) {
        return String(amount);
      }
    };

    var rewardsMoney = function (value) {
      var amount = parseFloat(value || '0');
      if (!isFinite(amount)) {
        amount = 0;
      }
      try {
        return new Intl.NumberFormat('en-GB', {
          style: 'currency',
          currency: 'GBP',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(amount);
      } catch (error) {
        return 'GBP ' + amount.toFixed(2);
      }
    };

    var rewardsSetText = function (selector, value) {
      var node = candidateRewardsRoot.querySelector(selector);
      if (node) {
        node.textContent = String(value == null ? '' : value);
      }
    };

    var setRewardsError = function (message) {
      if (!rewardsErrorEl) {
        return;
      }
      var text = String(message || '');
      rewardsErrorEl.textContent = text;
      rewardsErrorEl.hidden = text === '';
      rewardsErrorEl.classList.toggle('is-hidden', text === '');
    };

    var rewardsEscape = function (value) {
      return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    };

    var renderRewardsAwards = function (awards) {
      var host = candidateRewardsRoot.querySelector('[data-cmn-rewards-awards]');
      if (!host) {
        return;
      }
      var rows = Array.isArray(awards) ? awards.slice(0, 3) : [];
      if (!rows.length) {
        host.innerHTML = '<div class="cmn-empty">No bonus awards yet this year.</div>';
        return;
      }
      host.innerHTML = rows.map(function (award) {
        var block = parseInt(award && award.block_index ? award.block_index : '0', 10);
        if (!isFinite(block) || block < 1) {
          block = 1;
        }
        var amount = rewardsMoney(award && award.bonus_amount ? award.bonus_amount : 0);
        var awardedOn = award && award.awarded_at_label ? String(award.awarded_at_label) : '—';
        return '' +
          '<div class="cmn-candidate-rewards-award-row">' +
            '<span>Block ' + rewardsEscape(String(block)) + '</span>' +
            '<strong>' + rewardsEscape(amount) + '</strong>' +
            '<small>' + rewardsEscape(awardedOn) + '</small>' +
          '</div>';
      }).join('');
    };

    var renderConductHistory = function (events) {
      var host = candidateRewardsRoot.querySelector('[data-cmn-rewards-recent-conduct]');
      if (!host) {
        return;
      }
      var rows = Array.isArray(events) ? events.slice(0, 4) : [];
      if (!rows.length) {
        host.innerHTML = '<div class="cmn-empty">No conduct events recorded.</div>';
        return;
      }
      host.innerHTML = rows.map(function (event) {
        var label = event && event.label ? String(event.label) : 'Event';
        var when = event && event.occurred_at_label ? String(event.occurred_at_label) : '—';
        return '' +
          '<div class="cmn-candidate-rewards-conduct-row">' +
            '<strong>' + rewardsEscape(label) + '</strong>' +
            '<span>' + rewardsEscape(when) + '</span>' +
          '</div>';
      }).join('');
    };

    var renderAppealBookingChips = function (options) {
      var host = candidateRewardsRoot.querySelector('[data-cmn-rewards-open-bookings]');
      if (!host) {
        return;
      }
      var rows = Array.isArray(options) ? options : [];
      if (!rows.length) {
        host.innerHTML = '';
        return;
      }
      host.innerHTML = rows.map(function (row) {
        var bookingId = parseInt(row && row.booking_id ? row.booking_id : '0', 10);
        if (!isFinite(bookingId) || bookingId < 1) {
          return '';
        }
        return '<span class="cmn-status-chip">Booking #' + rewardsEscape(String(bookingId)) + '</span>';
      }).join('');
    };

    var renderAppealBookingSelect = function (options) {
      var select = candidateRewardsRoot.querySelector('[data-cmn-rewards-appeal-booking]');
      if (!select) {
        return;
      }
      var rows = Array.isArray(options) ? options : [];
      select.innerHTML = rows.map(function (row) {
        var bookingId = parseInt(row && row.booking_id ? row.booking_id : '0', 10);
        if (!isFinite(bookingId) || bookingId < 1) {
          return '';
        }
        var label = row && row.label ? String(row.label) : ('Booking #' + bookingId);
        return '<option value="' + rewardsEscape(String(bookingId)) + '">' + rewardsEscape(label) + '</option>';
      }).join('');
    };

    var applyRewardsPayload = function (payload) {
      if (!payload || typeof payload !== 'object') {
        return;
      }
      var state = (payload.state && typeof payload.state === 'object') ? payload.state : {};
      var bonus = (payload.bonus && typeof payload.bonus === 'object') ? payload.bonus : {};
      var referral = (payload.referral && typeof payload.referral === 'object') ? payload.referral : {};
      var conduct = (payload.conduct && typeof payload.conduct === 'object') ? payload.conduct : {};

      var tier = String(state.tier || 'standard').toLowerCase();
      var tierLabel = String(state.tier_label || 'Standard');
      var badge = candidateRewardsRoot.querySelector('[data-cmn-rewards-tier-badge]');
      if (badge) {
        rewardsTierClasses.forEach(function (className) {
          badge.classList.remove(className);
        });
        badge.classList.add('is-' + tier);
        badge.textContent = tierLabel;
      }

      var conductClass = String(state.conduct_chip_class || 'is-approved');
      var conductBadge = candidateRewardsRoot.querySelector('[data-cmn-rewards-conduct-badge]');
      if (conductBadge) {
        conductBadge.classList.remove('is-approved', 'is-pending', 'is-declined');
        conductBadge.classList.add(conductClass);
        conductBadge.textContent = String(state.conduct_label || 'Clear');
      }

      var shiftsCompleted = parseInt(state.shifts_completed || '0', 10);
      if (!isFinite(shiftsCompleted)) {
        shiftsCompleted = 0;
      }
      var nextTierThreshold = parseInt(state.next_tier_threshold || '0', 10);
      if (!isFinite(nextTierThreshold)) {
        nextTierThreshold = 0;
      }
      var shiftsToNextTier = parseInt(state.shifts_to_next_tier || '0', 10);
      if (!isFinite(shiftsToNextTier) || shiftsToNextTier < 0) {
        shiftsToNextTier = 0;
      }

      rewardsSetText('[data-cmn-rewards-year]', payload.academic_year_label || '');
      rewardsSetText('[data-cmn-rewards-tier]', tierLabel);
      rewardsSetText('[data-cmn-rewards-shifts]', rewardsWhole(shiftsCompleted));
      rewardsSetText('[data-cmn-rewards-next-tier]', state.next_tier || 'Top tier achieved');
      rewardsSetText(
        '[data-cmn-rewards-next-tier-copy]',
        nextTierThreshold > 0
          ? (String(shiftsToNextTier) + ' shifts to go (target: ' + String(nextTierThreshold) + ')')
          : 'Top tier achieved'
      );

      var lastBonusAmount = rewardsMoney(bonus.last_bonus_amount || 0);
      rewardsSetText('[data-cmn-rewards-last-bonus]', lastBonusAmount);
      rewardsSetText('[data-cmn-rewards-last-bonus-date]', bonus.last_bonus_awarded_at_label || 'Not yet awarded');

      var nextBonusAt = parseInt(bonus.next_bonus_at || '30', 10);
      if (!isFinite(nextBonusAt) || nextBonusAt < 30) {
        nextBonusAt = 30;
      }
      var shiftsToNextBonus = parseInt(bonus.shifts_to_next_bonus || '0', 10);
      if (!isFinite(shiftsToNextBonus) || shiftsToNextBonus < 0) {
        shiftsToNextBonus = 0;
      }
      rewardsSetText('[data-cmn-rewards-next-bonus]', 'Next bonus at ' + String(nextBonusAt) + ' shifts (' + String(shiftsToNextBonus) + ' to go)');

      var eliteNote = candidateRewardsRoot.querySelector('[data-cmn-rewards-elite-note]');
      if (eliteNote) {
        var eliteActive = !!bonus.elite_plus_active;
        eliteNote.classList.toggle('is-hidden', !eliteActive);
      }

      rewardsSetText('[data-cmn-rewards-ticket-count]', String(parseInt(referral.ticket_count || '0', 10) || 0));
      var codeInput = candidateRewardsRoot.querySelector('[data-cmn-rewards-referral-code]');
      if (codeInput) {
        codeInput.value = String(referral.referral_code || '');
      }

      renderRewardsAwards(Array.isArray(bonus.awards) ? bonus.awards : []);
      renderAppealBookingSelect(Array.isArray(conduct.appeal_booking_options) ? conduct.appeal_booking_options : []);
      renderAppealBookingChips(Array.isArray(conduct.appeal_booking_options) ? conduct.appeal_booking_options : []);
      renderConductHistory(Array.isArray(conduct.recent_events) ? conduct.recent_events : []);

      var openAppealBtn = candidateRewardsRoot.querySelector('[data-cmn-rewards-open-appeal]');
      var appealAvailability = candidateRewardsRoot.querySelector('[data-cmn-rewards-appeal-availability]');
      var canAppeal = !!(conduct && conduct.appeal_available);
      if (openAppealBtn) {
        openAppealBtn.disabled = !canAppeal;
      }
      if (appealAvailability) {
        appealAvailability.textContent = canAppeal
          ? 'Appeals are logged and reviewed by staff.'
          : 'No active no-show records available for appeal right now.';
      }
    };

    var postRewardsAction = function (actionName, nonceValue, payload) {
      if (!ajaxUrl) {
        return Promise.reject(new Error('Portal AJAX URL is not configured.'));
      }
      var formData = new FormData();
      formData.append('action', String(actionName || ''));
      if (nonceValue) {
        formData.append('nonce', String(nonceValue));
      }
      if (payload && typeof payload === 'object') {
        Object.keys(payload).forEach(function (key) {
          var value = payload[key];
          if (value === undefined || value === null) {
            return;
          }
          formData.append(key, String(value));
        });
      }
      return fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      }).then(function (response) {
        return response.json().catch(function () {
          return {};
        }).then(function (body) {
          if (!response.ok) {
            var fallback = (body && body.data && body.data.error && body.data.error.message)
              ? body.data.error.message
              : (body && body.data && body.data.message ? body.data.message : 'Request failed.');
            throw new Error(fallback);
          }
          return body;
        });
      });
    };

    var reloadRewardsOverview = function () {
      return postRewardsAction(rewardsFetchAction, rewardsFetchNonce, {})
        .then(function (response) {
          if (!response || !response.success || !response.data || response.data.ok === false) {
            throw new Error((response && response.data && response.data.message) ? response.data.message : 'Unable to load rewards information.');
          }
          var payload = (response.data && response.data.data && typeof response.data.data === 'object')
            ? response.data.data
            : {};
          applyRewardsPayload(payload);
          setRewardsError('');
          return payload;
        })
        .catch(function (error) {
          setRewardsError(error && error.message ? error.message : 'Unable to load rewards information.');
          throw error;
        });
    };

    var copyButton = candidateRewardsRoot.querySelector('[data-cmn-rewards-copy-code]');
    var copyInput = candidateRewardsRoot.querySelector('[data-cmn-rewards-referral-code]');
    var copyFeedback = candidateRewardsRoot.querySelector('[data-cmn-rewards-copy-feedback]');
    if (copyButton && copyInput) {
      copyButton.addEventListener('click', function () {
        var value = String(copyInput.value || '');
        if (!value) {
          return;
        }
        var done = function (message) {
          if (copyFeedback) {
            copyFeedback.textContent = message;
          }
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(value)
            .then(function () {
              done('Referral code copied.');
            })
            .catch(function () {
              copyInput.focus();
              copyInput.select();
              try {
                document.execCommand('copy');
              } catch (error) {
                // Fallback silently.
              }
              done('Referral code copied.');
            });
        } else {
          copyInput.focus();
          copyInput.select();
          try {
            document.execCommand('copy');
          } catch (error) {
            // Fallback silently.
          }
          done('Referral code copied.');
        }
      });
    }

    var appealModal = candidateRewardsRoot.querySelector('[data-cmn-rewards-appeal-modal]');
    var openAppealBtn = candidateRewardsRoot.querySelector('[data-cmn-rewards-open-appeal]');
    var closeAppealButtons = candidateRewardsRoot.querySelectorAll('[data-cmn-rewards-close-appeal]');
    var appealForm = candidateRewardsRoot.querySelector('[data-cmn-rewards-appeal-form]');
    var appealFeedback = candidateRewardsRoot.querySelector('[data-cmn-rewards-appeal-feedback]');
    var closeAppealModal = function () {
      if (!appealModal) {
        return;
      }
      appealModal.classList.remove('is-open');
      appealModal.setAttribute('aria-hidden', 'true');
    };
    var openAppealModal = function () {
      if (!appealModal) {
        return;
      }
      if (appealFeedback) {
        appealFeedback.textContent = '';
      }
      appealModal.classList.add('is-open');
      appealModal.setAttribute('aria-hidden', 'false');
    };

    if (openAppealBtn && appealModal) {
      openAppealBtn.addEventListener('click', function () {
        if (openAppealBtn.disabled) {
          return;
        }
        openAppealModal();
      });
    }
    if (closeAppealButtons.length) {
      closeAppealButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
          closeAppealModal();
        });
      });
    }
    if (appealModal) {
      appealModal.addEventListener('click', function (event) {
        if (event.target === appealModal) {
          closeAppealModal();
        }
      });
    }
    if (appealForm) {
      appealForm.addEventListener('submit', function (event) {
        event.preventDefault();
        var bookingField = appealForm.querySelector('[name="booking_id"]');
        var messageField = appealForm.querySelector('[name="message"]');
        var bookingId = bookingField ? parseInt(bookingField.value || '0', 10) : 0;
        var message = messageField ? String(messageField.value || '').trim() : '';
        if (!bookingId || !message) {
          if (appealFeedback) {
            appealFeedback.textContent = 'Booking and appeal details are required.';
          }
          return;
        }
        if (appealFeedback) {
          appealFeedback.textContent = 'Submitting appeal...';
        }
        postRewardsAction(rewardsAppealAction, rewardsAppealNonce, {
          booking_id: bookingId,
          message: message
        }).then(function (response) {
          if (!response || !response.success || !response.data || response.data.ok === false) {
            throw new Error((response && response.data && response.data.message) ? response.data.message : 'Unable to submit appeal.');
          }
          if (appealFeedback) {
            appealFeedback.textContent = response.data.message || 'Appeal submitted.';
          }
          if (messageField) {
            messageField.value = '';
          }
          var payload = (response.data && response.data.data && typeof response.data.data === 'object')
            ? response.data.data
            : null;
          if (payload) {
            applyRewardsPayload(payload);
          } else {
            reloadRewardsOverview().catch(function () {});
          }
          window.setTimeout(function () {
            closeAppealModal();
          }, 600);
        }).catch(function (error) {
          if (appealFeedback) {
            appealFeedback.textContent = error && error.message ? error.message : 'Unable to submit appeal.';
          }
        });
      });
    }

    reloadRewardsOverview().catch(function () {});
  }

  var weeklyEarningsRoot = document.querySelector('[data-cmn-weekly-earnings-root]');
  if (weeklyEarningsRoot) {
    var weeklyAction = weeklyEarningsRoot.getAttribute('data-cmn-weekly-earnings-action') || 'cmn_candidate_weekly_earnings_overview';
    var weeklyNonce = weeklyEarningsRoot.getAttribute('data-cmn-weekly-earnings-nonce') || ((window.cmnPortal && window.cmnPortal.candidateWeeklyEarningsNonce) ? String(window.cmnPortal.candidateWeeklyEarningsNonce) : '');
    var weeklyAjaxUrl = (window.cmnPortal && window.cmnPortal.ajaxUrl) ? String(window.cmnPortal.ajaxUrl) : '';
    var weeklyErrorEl = weeklyEarningsRoot.querySelector('[data-cmn-weekly-earnings-error]');
    var weeklyChipEl = weeklyEarningsRoot.querySelector('[data-cmn-weekly-earnings-chip]');
    var weeklyNoteEl = weeklyEarningsRoot.querySelector('[data-cmn-weekly-earnings-note]');

    var weeklySetText = function (selector, value) {
      var node = weeklyEarningsRoot.querySelector(selector);
      if (node) {
        node.textContent = String(value == null ? '' : value);
      }
    };

    var weeklyMoney = function (value) {
      var amount = parseFloat(value || '0');
      if (!isFinite(amount)) {
        amount = 0;
      }
      try {
        return new Intl.NumberFormat('en-GB', {
          style: 'currency',
          currency: 'GBP',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(amount);
      } catch (error) {
        return 'GBP ' + amount.toFixed(2);
      }
    };

    var weeklySetError = function (message) {
      if (!weeklyErrorEl) {
        return;
      }
      var text = String(message || '');
      weeklyErrorEl.textContent = text;
      weeklyErrorEl.hidden = text === '';
      weeklyErrorEl.classList.toggle('is-hidden', text === '');
    };

    var weeklySetChip = function (label, chipClass) {
      if (!weeklyChipEl) {
        return;
      }
      var resolvedLabel = String(label || 'Estimate');
      var resolvedClass = String(chipClass || 'is-pending').trim();
      if (!resolvedClass) {
        resolvedClass = 'is-pending';
      }
      weeklyChipEl.textContent = resolvedLabel;
      weeklyChipEl.classList.remove('is-approved', 'is-pending', 'is-declined', 'is-warning');
      weeklyChipEl.classList.add(resolvedClass);
    };

    var fetchWeeklyEarnings = function () {
      if (!weeklyAjaxUrl) {
        weeklySetError('Unable to load weekly earnings right now.');
        return;
      }
      var formData = new FormData();
      formData.append('action', weeklyAction);
      if (weeklyNonce) {
        formData.append('nonce', weeklyNonce);
      }

      fetch(weeklyAjaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      }).then(function (response) {
        return response.json().catch(function () {
          return {};
        }).then(function (body) {
          if (!response.ok || !body || !body.success || !body.data || body.data.ok === false) {
            var message = (body && body.data && body.data.error && body.data.error.message)
              ? body.data.error.message
              : (body && body.data && body.data.message ? body.data.message : 'Unable to load weekly earnings.');
            throw new Error(message);
          }
          return body.data && body.data.data ? body.data.data : {};
        });
      }).then(function (payload) {
        var weekRange = (payload && payload.week_range && payload.week_range.label)
          ? String(payload.week_range.label) + ' (Fri-Thu)'
          : 'Fri-Thu';
        var expectedDisplay = '';
        if (payload && Number(payload.expected_payout_is_hold || 0) === 1) {
          expectedDisplay = payload.expected_payout_display
            ? String(payload.expected_payout_display)
            : 'On hold until bank details added';
        } else {
          expectedDisplay = weeklyMoney(payload.expected_payout || payload.total || 0);
        }
        weeklySetText('[data-cmn-weekly-earnings-range]', weekRange);
        weeklySetText('[data-cmn-weekly-earnings-total]', weeklyMoney(payload.total || 0));
        weeklySetText('[data-cmn-weekly-earnings-expected]', expectedDisplay);
        weeklySetText('[data-cmn-weekly-earnings-last-week]', weeklyMoney(payload.last_week_total || 0));
        if (weeklyNoteEl && payload && payload.payout_state_note) {
          weeklyNoteEl.textContent = String(payload.payout_state_note);
        }
        weeklySetChip(
          (payload && payload.payout_state_label) ? String(payload.payout_state_label) : 'Estimate',
          (payload && payload.payout_state_chip_class) ? String(payload.payout_state_chip_class) : 'is-pending'
        );
        weeklySetError('');
      }).catch(function (error) {
        weeklySetError(error && error.message ? error.message : 'Unable to load weekly earnings.');
      });
    };

    fetchWeeklyEarnings();
  }

  var payrollQueryCard = document.querySelector('.cmn-candidate-weekly-earnings-card');
  if (payrollQueryCard) {
    var payrollQueryOpenBtn = payrollQueryCard.querySelector('[data-cmn-payroll-query-open]');
    var payrollQueryPanel = payrollQueryCard.querySelector('[data-cmn-payroll-query-panel]');
    var payrollQueryForm = payrollQueryCard.querySelector('[data-cmn-payroll-query-form]');
    if (payrollQueryOpenBtn && payrollQueryPanel && payrollQueryForm) {
      var payrollQueryCancelBtn = payrollQueryForm.querySelector('[data-cmn-payroll-query-cancel]');
      var payrollQuerySubmitBtn = payrollQueryForm.querySelector('[data-cmn-payroll-query-submit]');
      var payrollQueryPeriodSelect = payrollQueryForm.querySelector('[data-cmn-payroll-query-period]');
      var payrollQueryBookingSelect = payrollQueryForm.querySelector('[data-cmn-payroll-query-bookings]');
      var payrollQueryBookingEmpty = payrollQueryForm.querySelector('[data-cmn-payroll-query-bookings-empty]');
      var payrollQueryFeedback = payrollQueryForm.querySelector('[data-cmn-payroll-query-feedback]');
      var payrollQueryMessageField = payrollQueryForm.querySelector('textarea[name="message"]');
      var payrollQueryAjaxUrl = (window.cmnPortal && window.cmnPortal.ajaxUrl) ? String(window.cmnPortal.ajaxUrl) : '';
      var payrollQueryBookingMapRaw = payrollQueryForm.getAttribute('data-cmn-payroll-query-bookings-map') || '{}';
      var payrollQueryBookingMap = {};

      try {
        payrollQueryBookingMap = JSON.parse(payrollQueryBookingMapRaw);
      } catch (error) {
        payrollQueryBookingMap = {};
      }
      if (!payrollQueryBookingMap || typeof payrollQueryBookingMap !== 'object') {
        payrollQueryBookingMap = {};
      }

      var setPayrollQueryFeedback = function (message, state) {
        if (!payrollQueryFeedback) {
          return;
        }
        var text = String(message || '');
        payrollQueryFeedback.textContent = text;
        payrollQueryFeedback.hidden = text === '';
        payrollQueryFeedback.classList.remove('cmn-register-warning', 'cmn-register-success', 'cmn-register-error');
        if (text === '') {
          payrollQueryFeedback.classList.add('cmn-register-warning');
          return;
        }
        if (state === 'success') {
          payrollQueryFeedback.classList.add('cmn-register-success');
          return;
        }
        if (state === 'error') {
          payrollQueryFeedback.classList.add('cmn-register-error');
          return;
        }
        payrollQueryFeedback.classList.add('cmn-register-warning');
      };

      var setPayrollQueryPanelOpen = function (isOpen) {
        payrollQueryPanel.hidden = !isOpen;
        if (isOpen) {
          setPayrollQueryFeedback('', '');
        }
      };

      var renderPayrollQueryBookingOptions = function () {
        if (!payrollQueryBookingSelect || !payrollQueryPeriodSelect) {
          return;
        }
        var periodId = String(payrollQueryPeriodSelect.value || '');
        var optionRows = Array.isArray(payrollQueryBookingMap[periodId]) ? payrollQueryBookingMap[periodId] : [];
        while (payrollQueryBookingSelect.firstChild) {
          payrollQueryBookingSelect.removeChild(payrollQueryBookingSelect.firstChild);
        }

        optionRows.forEach(function (row) {
          if (!row || typeof row !== 'object') {
            return;
          }
          var value = String(row.value || '');
          var label = String(row.label || '');
          if (!value || !label) {
            return;
          }
          var optionEl = document.createElement('option');
          optionEl.value = value;
          optionEl.textContent = label;
          payrollQueryBookingSelect.appendChild(optionEl);
        });

        var hasOptions = payrollQueryBookingSelect.options.length > 0;
        payrollQueryBookingSelect.disabled = !hasOptions;
        if (payrollQueryBookingEmpty) {
          payrollQueryBookingEmpty.hidden = hasOptions;
        }
      };

      payrollQueryOpenBtn.addEventListener('click', function () {
        setPayrollQueryPanelOpen(true);
        renderPayrollQueryBookingOptions();
      });

      if (payrollQueryCancelBtn) {
        payrollQueryCancelBtn.addEventListener('click', function () {
          setPayrollQueryPanelOpen(false);
          setPayrollQueryFeedback('', '');
        });
      }

      if (payrollQueryPeriodSelect) {
        payrollQueryPeriodSelect.addEventListener('change', function () {
          renderPayrollQueryBookingOptions();
        });
      }

      renderPayrollQueryBookingOptions();

      payrollQueryForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!payrollQueryAjaxUrl) {
          setPayrollQueryFeedback('Unable to submit payroll query right now.', 'error');
          return;
        }

        var periodId = payrollQueryPeriodSelect ? String(payrollQueryPeriodSelect.value || '').trim() : '';
        var message = payrollQueryMessageField ? String(payrollQueryMessageField.value || '').trim() : '';
        if (!periodId) {
          setPayrollQueryFeedback('Select a pay period before submitting.', 'error');
          return;
        }
        if (message.length < 10) {
          setPayrollQueryFeedback('Message must be at least 10 characters.', 'error');
          return;
        }

        setPayrollQueryFeedback('Submitting payroll query...', '');
        if (payrollQuerySubmitBtn) {
          payrollQuerySubmitBtn.disabled = true;
        }

        var formData = new FormData(payrollQueryForm);
        fetch(payrollQueryAjaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: formData
        }).then(function (response) {
          return response.json().catch(function () {
            return {};
          }).then(function (payload) {
            if (!response.ok || !payload || !payload.success || !payload.data || payload.data.ok === false) {
              var errorMessage = (payload && payload.data && payload.data.error && payload.data.error.message)
                ? String(payload.data.error.message)
                : ((payload && payload.data && payload.data.message)
                  ? String(payload.data.message)
                  : 'Unable to submit payroll query.');
              throw new Error(errorMessage);
            }
            return payload.data || {};
          });
        }).then(function (data) {
          var ticketRef = (data && data.ticket && data.ticket.ref) ? String(data.ticket.ref) : '';
          var successMessage = ticketRef
            ? ('Payroll query submitted. Reference: ' + ticketRef)
            : 'Payroll query submitted.';
          setPayrollQueryFeedback(successMessage, 'success');

          if (payrollQueryMessageField) {
            payrollQueryMessageField.value = '';
          }
          if (payrollQueryBookingSelect) {
            Array.prototype.forEach.call(payrollQueryBookingSelect.options, function (optionEl) {
              optionEl.selected = false;
            });
          }
        }).catch(function (error) {
          setPayrollQueryFeedback(error && error.message ? error.message : 'Unable to submit payroll query.', 'error');
        }).finally(function () {
          if (payrollQuerySubmitBtn) {
            payrollQuerySubmitBtn.disabled = false;
          }
        });
      });
    }
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
    var notificationsApiReady = !!(window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.notificationNonce);
    var bellGetRows = function (bell) {
      if (!bell) {
        return [];
      }
      return Array.prototype.slice.call(bell.querySelectorAll('[data-notification-item]'));
    };
    var bellGetSelectedRows = function (bell) {
      return bellGetRows(bell).filter(function (row) {
        var input = row.querySelector('[data-notification-select]');
        return !!(input && input.checked);
      });
    };
    var bellGetSelectedIds = function (bell) {
      return bellGetSelectedRows(bell)
        .map(function (row) {
          return parseInt(row.getAttribute('data-notification-id') || '0', 10);
        })
        .filter(function (id) {
          return id > 0;
        });
    };
    var bellEnsureEmptyState = function (bell) {
      if (!bell) {
        return;
      }
      var panel = bell.querySelector('[data-bell-panel]');
      if (!panel) {
        return;
      }
      var list = bell.querySelector('[data-bell-list]');
      var hasRows = bellGetRows(bell).length > 0;
      var empty = panel.querySelector('.cmn-empty');
      if (!hasRows) {
        if (list) {
          list.innerHTML = '';
        }
        if (!empty) {
          empty = document.createElement('div');
          empty.className = 'cmn-empty';
          empty.textContent = 'No notifications yet.';
          panel.appendChild(empty);
        }
      } else if (empty) {
        empty.remove();
      }
    };
    var runBellAction = function (actionName, payload) {
      if (!notificationsApiReady) {
        return Promise.reject(new Error('Notifications API unavailable.'));
      }
      var formData = new FormData();
      formData.append('action', actionName);
      formData.append('nonce', window.cmnPortal.notificationNonce || '');
      if (payload && typeof payload === 'object') {
        Object.keys(payload).forEach(function (key) {
          if (!Object.prototype.hasOwnProperty.call(payload, key)) {
            return;
          }
          var value = payload[key];
          if (key === 'notification_ids' && Array.isArray(value)) {
            formData.append('notification_ids', JSON.stringify(value));
            return;
          }
          if (typeof value === 'undefined' || value === null) {
            return;
          }
          formData.append(key, String(value));
        });
      }
      return fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
      }).then(function (response) {
        return response.json();
      });
    };
    var bellEsc = function (value) {
      return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    };
    var renderBellItems = function (bell, payload) {
      if (!bell || !payload || !Array.isArray(payload.items)) {
        return;
      }
      var panel = bell.querySelector('[data-bell-panel]');
      if (!panel) {
        return;
      }
      var list = bell.querySelector('[data-bell-list]');
      if (!list) {
        list = document.createElement('div');
        list.className = 'cmn-bell-list';
        list.setAttribute('data-bell-list', '1');
        panel.appendChild(list);
      }

      var selectedMap = {};
      bellGetSelectedIds(bell).forEach(function (selectedId) {
        selectedMap[selectedId] = true;
      });

      list.innerHTML = '';
      payload.items.forEach(function (item) {
        var itemId = parseInt(item && item.id ? item.id : '0', 10) || 0;
        if (itemId < 1) {
          return;
        }
        var isUnread = parseInt(item && item.is_read ? item.is_read : '0', 10) !== 1;
        var linkUrl = String(item && item.link_url ? item.link_url : '').trim();
        var article = document.createElement('article');
        article.className = 'cmn-bell-item' + (isUnread ? ' is-unread' : '');
        article.setAttribute('data-notification-item', '1');
        article.setAttribute('data-notification-id', String(itemId));

        var bodyHtml = '';
        if (linkUrl) {
          bodyHtml = '<a class="cmn-bell-link" href="' + bellEsc(linkUrl) + '" data-notification-id="' + String(itemId) + '" data-notification-link>' +
            '<strong>' + bellEsc(item && item.title ? item.title : '') + '</strong>' +
            (item && item.message ? ('<span>' + bellEsc(item.message) + '</span>') : '') +
          '</a>';
        } else {
          bodyHtml = '<div class="cmn-bell-link cmn-bell-link-static" data-notification-id="' + String(itemId) + '">' +
            '<strong>' + bellEsc(item && item.title ? item.title : '') + '</strong>' +
            (item && item.message ? ('<span>' + bellEsc(item.message) + '</span>') : '') +
          '</div>';
        }

        article.innerHTML =
          '<label class="cmn-bell-select" aria-label="Select notification">' +
            '<input type="checkbox" data-notification-select value="' + String(itemId) + '"' + (selectedMap[itemId] ? ' checked' : '') + '>' +
            '<span aria-hidden="true"></span>' +
          '</label>' +
          '<div class="cmn-bell-item-body">' + bodyHtml + '</div>' +
          '<div class="cmn-bell-item-actions">' +
            '<button class="cmn-ghost cmn-btn-mini" type="button" data-notification-mark-read' + (isUnread ? '' : ' disabled') + '>Mark as read</button>' +
            '<button class="cmn-ghost cmn-btn-mini" type="button" data-notification-delete>Delete</button>' +
          '</div>';
        list.appendChild(article);
      });
      bellEnsureEmptyState(bell);
    };
    var refreshBellActionState = function (bell, payload) {
      if (!bell) {
        return;
      }
      var markBtn = bell.querySelector('[data-bell-mark]');
      var markSelectedBtn = bell.querySelector('[data-bell-mark-selected]');
      var deleteSelectedBtn = bell.querySelector('[data-bell-delete-selected]');
      var clearBtn = bell.querySelector('[data-bell-clear]');
      if (!markBtn && !markSelectedBtn && !deleteSelectedBtn && !clearBtn) {
        return;
      }
      var unread = 0;
      var itemCountPayload = 0;
      if (payload && typeof payload.unread !== 'undefined') {
        unread = parseInt(payload.unread || 0, 10);
      } else {
        unread = bell.querySelectorAll('.cmn-bell-item.is-unread').length;
      }
      if (payload && Array.isArray(payload.items)) {
        itemCountPayload = payload.items.length;
      }
      var rows = bellGetRows(bell);
      var itemCount = Math.max(rows.length, itemCountPayload);
      var selectedRows = bellGetSelectedRows(bell);
      var selectedUnreadCount = selectedRows.filter(function (row) {
        return row.classList.contains('is-unread');
      }).length;
      if (markBtn) {
        markBtn.disabled = unread < 1;
      }
      if (markSelectedBtn) {
        markSelectedBtn.disabled = selectedUnreadCount < 1;
      }
      if (deleteSelectedBtn) {
        deleteSelectedBtn.disabled = selectedRows.length < 1;
      }
      if (clearBtn) {
        clearBtn.disabled = itemCount < 1;
      }
    };
    var refreshBellState = function (bell, payload) {
      if (!bell) {
        return;
      }
      if (payload && Array.isArray(payload.items)) {
        renderBellItems(bell, payload);
      }
      var count = bell.querySelector('.cmn-bell-count');
      var unread = 0;
      if (payload && typeof payload.unread !== 'undefined') {
        unread = parseInt(payload.unread || 0, 10);
      } else {
        unread = bell.querySelectorAll('.cmn-bell-item.is-unread').length;
      }
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
      refreshBellActionState(bell, payload);
    };
    var markBellRowRead = function (row) {
      if (!row) {
        return;
      }
      row.classList.remove('is-unread');
      var markBtn = row.querySelector('[data-notification-mark-read]');
      if (markBtn) {
        markBtn.disabled = true;
      }
    };
    var removeBellRowsByIds = function (bell, ids) {
      if (!bell || !Array.isArray(ids) || !ids.length) {
        return;
      }
      var idMap = {};
      ids.forEach(function (id) {
        var normalized = parseInt(id || 0, 10);
        if (normalized > 0) {
          idMap[normalized] = true;
        }
      });
      if (!Object.keys(idMap).length) {
        return;
      }
      bellGetRows(bell).forEach(function (row) {
        var rowId = parseInt(row.getAttribute('data-notification-id') || '0', 10);
        if (rowId > 0 && idMap[rowId]) {
          row.remove();
        }
      });
      bellEnsureEmptyState(bell);
      refreshBellActionState(bell, null);
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
      var markSelectedBtn = bell.querySelector('[data-bell-mark-selected]');
      var deleteSelectedBtn = bell.querySelector('[data-bell-delete-selected]');
      var clearBtn = bell.querySelector('[data-bell-clear]');
      bellEnsureEmptyState(bell);
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
      if (panel) {
        panel.addEventListener('change', function (event) {
          if (event.target && event.target.matches('[data-notification-select]')) {
            refreshBellActionState(bell, null);
          }
        });
      }
      if (markBtn && notificationsApiReady) {
        markBtn.addEventListener('click', function (event) {
          event.preventDefault();
          runBellAction('cmn_notifications_mark_all_read')
            .then(function (response) {
              if (response && response.success) {
                bellGetRows(bell).forEach(function (row) {
                  markBellRowRead(row);
                  var input = row.querySelector('[data-notification-select]');
                  if (input) {
                    input.checked = false;
                  }
                });
                refreshBellState(bell, response.data || {});
              }
            });
        });
      }
      if (markSelectedBtn && notificationsApiReady) {
        markSelectedBtn.addEventListener('click', function (event) {
          event.preventDefault();
          var selectedIds = bellGetSelectedIds(bell);
          if (!selectedIds.length) {
            refreshBellActionState(bell, null);
            return;
          }
          runBellAction('cmn_notifications_mark_selected_read', {
            notification_ids: selectedIds,
          }).then(function (response) {
            if (!(response && response.success)) {
              return;
            }
            selectedIds.forEach(function (id) {
              var row = bell.querySelector('[data-notification-item][data-notification-id="' + String(id) + '"]');
              if (!row) {
                return;
              }
              markBellRowRead(row);
              var input = row.querySelector('[data-notification-select]');
              if (input) {
                input.checked = false;
              }
            });
            refreshBellState(bell, response.data || {});
          });
        });
      }
      if (deleteSelectedBtn && notificationsApiReady) {
        deleteSelectedBtn.addEventListener('click', function (event) {
          event.preventDefault();
          var selectedIds = bellGetSelectedIds(bell);
          if (!selectedIds.length) {
            refreshBellActionState(bell, null);
            return;
          }
          runBellAction('cmn_notifications_delete_selected', {
            notification_ids: selectedIds,
          }).then(function (response) {
            if (!(response && response.success)) {
              return;
            }
            removeBellRowsByIds(bell, selectedIds);
            refreshBellState(bell, response.data || {});
          });
        });
      }
      if (clearBtn && notificationsApiReady) {
        clearBtn.addEventListener('click', function (event) {
          event.preventDefault();
          runBellAction('cmn_notifications_clear_all')
            .then(function (response) {
              if (response && response.success) {
                bellGetRows(bell).forEach(function (row) {
                  row.remove();
                });
                bellEnsureEmptyState(bell);
                refreshBellState(bell, response.data || { unread: 0, items: [] });
              }
            });
        });
      }
      if (panel) {
        panel.addEventListener('click', function (event) {
          var markOneBtn = event.target.closest('[data-notification-mark-read]');
          if (markOneBtn) {
            event.preventDefault();
            var markRow = markOneBtn.closest('[data-notification-item]');
            var markNotificationId = parseInt(markRow ? markRow.getAttribute('data-notification-id') : '0', 10);
            if (!markNotificationId || !notificationsApiReady) {
              return;
            }
            runBellAction('cmn_notifications_mark_read', {
              notification_id: markNotificationId,
            }).then(function (response) {
              if (!(response && response.success)) {
                return;
              }
              markBellRowRead(markRow);
              refreshBellState(bell, response.data || {});
            });
            return;
          }

          var deleteOneBtn = event.target.closest('[data-notification-delete]');
          if (deleteOneBtn) {
            event.preventDefault();
            var deleteRow = deleteOneBtn.closest('[data-notification-item]');
            var deleteNotificationId = parseInt(deleteRow ? deleteRow.getAttribute('data-notification-id') : '0', 10);
            if (!deleteNotificationId || !notificationsApiReady) {
              return;
            }
            runBellAction('cmn_notifications_delete_selected', {
              notification_ids: [deleteNotificationId],
            }).then(function (response) {
              if (!(response && response.success)) {
                return;
              }
              removeBellRowsByIds(bell, [deleteNotificationId]);
              refreshBellState(bell, response.data || {});
            });
            return;
          }

          var link = event.target.closest('[data-notification-link]');
          if (!link) {
            return;
          }
          var notificationId = parseInt(link.getAttribute('data-notification-id') || '0', 10);
          var href = link.getAttribute('href') || '';
          if (!notificationsApiReady || !notificationId) {
            if (href) {
              window.location.href = href;
            }
            return;
          }
          event.preventDefault();
          runBellAction('cmn_notifications_mark_read', {
            notification_id: notificationId,
          })
            .then(function (response) {
              if (response && response.success) {
                var linkedRow = link.closest('[data-notification-item]');
                markBellRowRead(linkedRow);
                refreshBellState(bell, response.data || {});
              }
            })
            .finally(function () {
              if (href) {
                window.location.href = href;
              }
            });
        });
      }
    });

    if (notificationsApiReady) {
      var pollNotifications = function () {
        return runBellAction('cmn_notifications_poll')
          .then(function (data) {
            if (!(data && data.success)) {
              return;
            }
            bellContainers.forEach(function (bell) {
              refreshBellState(bell, data.data || {});
            });
          })
          .catch(function () {
            // Ignore transient polling failures.
          });
      };
      if (cmnPollManager) {
        cmnPollManager.register({
          key: 'cmn-notifications-poll',
          intervalMs: 4000,
          maxIntervalMs: 20000,
          callback: pollNotifications,
          visibleOnly: true,
          triggerOnFocus: true,
          triggerOnVisibility: true,
          backoffOnError: true,
          immediate: true,
        });
      } else {
        pollNotifications();
        window.setInterval(pollNotifications, 4000);
        document.addEventListener('visibilitychange', function () {
          if (!document.hidden) {
            pollNotifications();
          }
        });
        window.addEventListener('focus', function () {
          pollNotifications();
        });
      }
    }
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
    var headerSaveButtons = document.querySelectorAll('[data-calendar-header-save]');
    var headerClearButtons = document.querySelectorAll('[data-calendar-header-clear]');
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

    headerSaveButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (typeof calendarForm.requestSubmit === 'function') {
          calendarForm.requestSubmit();
        } else {
          calendarForm.submit();
        }
      });
    });

    headerClearButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var clearButton = calendarForm.querySelector('[data-calendar-set="clear"]');
        if (clearButton) {
          clearButton.click();
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
              var statusLabel = item.status === 'sent' ? '\u2714' : '\u2716';
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

  var staffAddModal = document.querySelector('[data-staff-add-modal]');
  if (staffAddModal) {
    var openAddModal = function () {
      staffAddModal.classList.add('is-open');
    };
    var closeAddModal = function () {
      staffAddModal.classList.remove('is-open');
    };
    document.querySelectorAll('[data-staff-add-open]').forEach(function (button) {
      button.addEventListener('click', openAddModal);
    });
    staffAddModal.querySelectorAll('[data-staff-add-close]').forEach(function (button) {
      button.addEventListener('click', closeAddModal);
    });
    staffAddModal.addEventListener('click', function (event) {
      if (event.target === staffAddModal) {
        closeAddModal();
      }
    });
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
      var submitButton = staffForm.querySelector('button[type="submit"]');
      if (!name || !email || !role) {
        return;
      }
      if (submitButton && submitButton.disabled) {
        return;
      }
      if (submitButton) {
        submitButton.disabled = true;
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
          if (submitButton) {
            submitButton.disabled = false;
          }
          if (!data || !data.success) {
            if (staffMsg) {
              staffMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to add staff.';
            }
            return;
          }
          if (staffMsg) {
            staffMsg.textContent = 'Staff added. Reloading...';
          }
          if (staffAddModal) {
            staffAddModal.classList.remove('is-open');
          }
          window.location.reload();
        })
        .catch(function () {
          if (submitButton) {
            submitButton.disabled = false;
          }
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
    var modalTitle = staffModal.querySelector('[data-staff-modal-title]');
    var editSubmitBtn = staffModal.querySelector('[data-staff-edit-submit]');
    var currentRow = null;

    var roleValue = function (role) {
      var normalizedRole = String(role || '').toLowerCase();
      if (normalizedRole === 'administrator' || normalizedRole === 'admin') {
        return 'cmn_admin';
      }
      if (normalizedRole === 'cmn_admin') {
        return 'cmn_admin';
      }
      if (normalizedRole === 'cmn_account_manager' || normalizedRole === 'account manager') {
        return 'cmn_account_manager';
      }
      return 'cmn_staff';
    };

    var roleDisplay = function (role) {
      if (role === 'administrator' || role === 'cmn_admin') {
        return 'Admin';
      }
      if (role === 'cmn_account_manager') {
        return 'Account Manager';
      }
      return 'Staff';
    };

    var setStaffModalMode = function (mode) {
      if (!editForm) {
        return;
      }
      var safeMode = mode === 'view' ? 'view' : 'edit';
      editForm.setAttribute('data-mode', safeMode);
      var isView = safeMode === 'view';
      var nameInput = editForm.querySelector('input[name="staff_name"]');
      var emailInput = editForm.querySelector('input[name="staff_email"]');
      var usernameInput = editForm.querySelector('input[name="staff_username"]');
      var roleSelect = editForm.querySelector('select[name="staff_role"]');
      if (nameInput) {
        nameInput.readOnly = isView;
      }
      if (emailInput) {
        emailInput.readOnly = isView;
      }
      if (usernameInput) {
        usernameInput.readOnly = true;
      }
      if (roleSelect) {
        roleSelect.disabled = isView;
      }
      if (modalTitle) {
        modalTitle.textContent = isView ? 'Staff Profile' : 'Edit Staff Member';
      }
      if (editSubmitBtn) {
        editSubmitBtn.hidden = isView;
        editSubmitBtn.disabled = isView;
      }
    };

    var openStaffModal = function (button, mode) {
      currentRow = button.closest('tr');
      if (!editForm) {
        return;
      }
      editForm.querySelector('input[name="staff_id"]').value = button.getAttribute('data-staff-id') || '';
      editForm.querySelector('input[name="staff_name"]').value = button.getAttribute('data-staff-name') || '';
      editForm.querySelector('input[name="staff_username"]').value = button.getAttribute('data-staff-username') || '';
      editForm.querySelector('input[name="staff_email"]').value = button.getAttribute('data-staff-email') || '';
      editForm.querySelector('select[name="staff_role"]').value = roleValue(button.getAttribute('data-staff-role') || 'cmn_staff');
      setStaffModalMode(mode);
      if (editMsg) {
        editMsg.textContent = '';
      }
      staffModal.classList.add('is-open');
    };
    setStaffModalMode('edit');

    document.querySelectorAll('[data-staff-edit]').forEach(function (button) {
      button.addEventListener('click', function () {
        openStaffModal(button, 'edit');
      });
    });
    document.querySelectorAll('[data-staff-view]').forEach(function (button) {
      button.addEventListener('click', function () {
        openStaffModal(button, 'view');
      });
    });

    if (modalClose) {
      modalClose.addEventListener('click', function () {
        setStaffModalMode('edit');
        staffModal.classList.remove('is-open');
      });
    }
    staffModal.addEventListener('click', function (event) {
      if (event.target === staffModal) {
        setStaffModalMode('edit');
        staffModal.classList.remove('is-open');
      }
    });

    if (editForm) {
      editForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (editForm.getAttribute('data-mode') === 'view') {
          return;
        }
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
                var nameStrong = nameCell.querySelector('strong');
                if (nameStrong) {
                  nameStrong.textContent = user.name;
                } else {
                  nameCell.textContent = user.name;
                }
                nameCell.setAttribute('title', user.name || '');
              }
              if (emailCell) {
                emailCell.textContent = user.email;
                emailCell.setAttribute('title', user.email || '');
              }
              if (roleCell) {
                roleCell.textContent = roleDisplay(user.role);
              }
              currentRow.querySelectorAll('[data-staff-edit],[data-staff-view]').forEach(function (actionBtn) {
                actionBtn.setAttribute('data-staff-name', user.name);
                actionBtn.setAttribute('data-staff-email', user.email);
                actionBtn.setAttribute('data-staff-role', roleValue(user.role));
              });
            }
            if (editMsg) {
              editMsg.textContent = 'Saved.';
            }
            setStaffModalMode('edit');
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
            var statusChip = statusCell ? statusCell.querySelector('.cmn-status-chip') : null;
            if (!statusChip && userId) {
              statusChip = document.querySelector('[data-staff-status-chip][data-staff-id="' + userId + '"]');
            }
            if (statusChip) {
              statusChip.classList.remove('is-declined', 'is-approved');
              statusChip.classList.add(isDeactivated ? 'is-declined' : 'is-approved');
              statusChip.textContent = isDeactivated ? 'Deactivated' : 'Active';
            }
            button.textContent = isDeactivated ? 'Activate' : 'Deactivate';
            button.setAttribute('data-staff-active', isDeactivated ? '0' : '1');
            button.classList.toggle('is-danger', !isDeactivated);
          })
          .catch(function () {
            alert('Unable to update status.');
          });
      });
    });
  }

  var supportRoots = document.querySelectorAll('[data-support-root]');
  if (supportRoots.length && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.supportNonce) {
    supportRoots.forEach(function (root, supportRootIndex) {
      var mode = root.getAttribute('data-support-mode') || 'user';
      var listEl = root.querySelector('[data-support-list]');
      var threadEl = root.querySelector('[data-support-thread]');
      var messagesEl = root.querySelector('[data-support-messages]');
      var replyForm = root.querySelector('[data-support-reply]');
      var infoToggleBtn = threadEl ? threadEl.querySelector('[data-support-info-toggle]') : null;
      var infoPanel = threadEl ? threadEl.querySelector('[data-support-thread-info]') : null;
      var openBtn = root.querySelector('[data-support-open]');
      var modal = root.parentElement.querySelector('[data-support-modal]') || document.querySelector('[data-support-modal]');
      var modalClose = modal ? modal.querySelector('[data-support-modal-close]') : null;
      var modalForm = modal ? modal.querySelector('[data-support-form]') : null;
      var modalMsg = modal ? modal.querySelector('[data-support-form-msg]') : null;
      var supportRole = root.getAttribute('data-support-role') || 'user';
      var activeTicketId = null;
      var activeTicket = null;
      var activeTicketLastMessageId = 0;
      var supportRealtimeTickInFlight = false;
      var supportLastListRefreshAt = 0;
      var supportParams = new URLSearchParams(window.location.search);
      var initialSupportFilter = (supportParams.get('support_filter') || '').toLowerCase();
      var initialSupportPriority = (supportParams.get('support_priority') || '').toLowerCase();
      var supportFeedbackParam = (supportParams.get('support_feedback') || '').toLowerCase();
      var supportShouldPromptFeedback = ['1', 'true', 'yes', 'on'].indexOf(supportFeedbackParam) !== -1;
      var supportOpenPrefill = (supportParams.get('support_open') || '').toLowerCase();
      var supportShouldAutoOpen = supportOpenPrefill === '1' || supportOpenPrefill === 'true' || supportOpenPrefill === 'yes';
      var supportPrefillCategory = supportParams.get('support_prefill_category') || '';
      var supportPrefillSubject = supportParams.get('support_prefill_subject') || '';
      var supportPrefillMessage = supportParams.get('support_prefill_message') || '';
      if (supportShouldPromptFeedback && !initialSupportFilter) {
        initialSupportFilter = 'needs_feedback';
      }
      var filter = initialSupportFilter || (mode === 'admin' ? 'active' : 'all');
      var priorityFilter = initialSupportPriority || 'all';
      var priorityFilterControl = root.querySelector('[data-support-priority-filter]');
      var searchControl = root.querySelector('[data-support-search]');
      var searchQuery = '';
      var supportTicketsCache = [];
      var dashboard = root.querySelector('[data-support-dashboard]');
      var openInsightsBtn = root.querySelector('[data-support-open-insights]');
      var feedbackViewBtn = root.querySelector('[data-support-open-feedback]');
      var feedbackViewModal = root.querySelector('[data-support-feedback-view-modal]');
      var feedbackModal = root.parentElement.querySelector('[data-support-feedback-modal]') || root.querySelector('[data-support-feedback-modal]');
      var feedbackModalForm = feedbackModal ? feedbackModal.querySelector('[data-support-feedback-modal-form]') : null;
      var feedbackModalMsg = feedbackModal ? feedbackModal.querySelector('[data-support-feedback-modal-msg]') : null;
      var feedbackSubmitBtn = feedbackModalForm ? feedbackModalForm.querySelector('button[type="submit"]') : null;
      var insightsModal = root.parentElement.querySelector('[data-support-insights-modal]') || root.querySelector('[data-support-insights-modal]');
      var insightsList = insightsModal ? insightsModal.querySelector('[data-support-insights-list]') : null;
      var isFeedbackModalOpen = false;
      var isFeedbackViewModalOpen = false;
      var isInsightsModalOpen = false;
      var feedbackSubmitInFlight = false;
      var feedbackSubmittedTicketIds = {};
      var feedbackCacheByTicket = {};
      var feedbackSuppressUntilByTicket = {};
      var dashboardData = { counts: {}, recent_feedback: [], recent_feedback_avg: 0 };
      var insightsFilter = 'recent';
      var threadInfoOpen = false;
      var saveDraftBtn = root.querySelector('[data-support-save-draft]');
      var replyTextarea = replyForm ? replyForm.querySelector('textarea[name="message"]') : null;
      if (saveDraftBtn) {
        saveDraftBtn.disabled = true;
      }

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

      var clearSupportFeedbackIntentParam = function () {
        if (!supportShouldPromptFeedback) {
          return;
        }
        supportShouldPromptFeedback = false;
        var url = new URL(window.location.href);
        if (url.searchParams.has('support_feedback')) {
          url.searchParams.delete('support_feedback');
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

      var getLatestSupportMessageId = function (messages) {
        if (!Array.isArray(messages) || !messages.length) {
          return 0;
        }
        var latestId = 0;
        messages.forEach(function (msg) {
          var msgId = parseInt(msg && msg.id ? msg.id : '0', 10) || 0;
          if (msgId > latestId) {
            latestId = msgId;
          }
        });
        return latestId;
      };

      var supportEsc = function (value) {
        return String(value == null ? '' : value)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      };

      var normalizeSupportSearchQuery = function (value) {
        return String(value || '')
          .toLowerCase()
          .replace(/\s+/g, ' ')
          .trim();
      };

      var getSupportRequesterName = function (ticket) {
        if (!ticket || typeof ticket !== 'object') {
          return 'Portal user';
        }
        var livechatName = String(ticket.livechat_guest_name || '').trim();
        if (livechatName) {
          return livechatName;
        }
        var requesterName = String(ticket.requester_name || '').trim();
        if (requesterName) {
          return requesterName;
        }
        return 'Portal user';
      };

      var getSupportRequesterEmail = function (ticket) {
        if (!ticket || typeof ticket !== 'object') {
          return '';
        }
        var livechatEmail = String(ticket.livechat_guest_email || '').trim();
        if (livechatEmail) {
          return livechatEmail;
        }
        return String(ticket.requester_email || '').trim();
      };

      var getSupportStatusChipData = function (ticket) {
        var statusKey = String((ticket && ticket.status) || 'open').toLowerCase();
        if (statusKey === 'closed') {
          if (parseInt((ticket && ticket.requires_feedback) || '0', 10) === 1) {
            return { label: 'Needs feedback', className: 'is-pending' };
          }
          return { label: 'Closed', className: 'is-approved' };
        }
        if (statusKey === 'new') {
          return { label: 'New', className: 'is-pending' };
        }
        return { label: 'Open', className: 'is-muted' };
      };

      var getSupportListStatusBadgeData = function (ticket) {
        var statusKey = String((ticket && ticket.status) || 'open').toLowerCase();
        if (statusKey === 'closed') {
          if (parseInt((ticket && ticket.requires_feedback) || '0', 10) === 1) {
            return { label: 'FEEDBACK', className: 'is-feedback' };
          }
          return { label: 'CLOSED', className: 'is-closed' };
        }
        return { label: 'OPEN', className: 'is-open' };
      };

      var buildSupportSearchHaystack = function (ticket) {
        if (!ticket || typeof ticket !== 'object') {
          return '';
        }
        var requesterEmail = getSupportRequesterEmail(ticket);
        return normalizeSupportSearchQuery([
          ticket.id,
          ticket.ref,
          ticket.ticket_ref,
          requesterEmail,
        ].join(' '));
      };

      var getSupportDraftStorageKey = function () {
        var ticketPart = activeTicketId ? String(activeTicketId) : 'none';
        return 'cmn_support_reply_draft_' + String(mode) + '_' + ticketPart;
      };

      var restoreSupportDraft = function () {
        if (!replyTextarea) {
          return;
        }
        try {
          var stored = window.localStorage.getItem(getSupportDraftStorageKey());
          if (stored !== null) {
            replyTextarea.value = String(stored);
          }
        } catch (_draftErr) {
          // Ignore localStorage failures.
        }
      };

      var persistSupportDraft = function () {
        if (!replyTextarea) {
          return;
        }
        try {
          var value = String(replyTextarea.value || '');
          if (value.trim() === '') {
            window.localStorage.removeItem(getSupportDraftStorageKey());
          } else {
            window.localStorage.setItem(getSupportDraftStorageKey(), value);
          }
        } catch (_draftErr) {
          // Ignore localStorage failures.
        }
      };

      var setThreadInfoOpen = function (open) {
        threadInfoOpen = !!open;
        if (infoPanel) {
          infoPanel.hidden = !threadInfoOpen;
        }
        if (infoToggleBtn) {
          infoToggleBtn.setAttribute('aria-expanded', threadInfoOpen ? 'true' : 'false');
          infoToggleBtn.classList.toggle('is-active', threadInfoOpen);
        }
      };
      if (infoToggleBtn) {
        infoToggleBtn.addEventListener('click', function () {
          if (infoToggleBtn.disabled) {
            return;
          }
          setThreadInfoOpen(!threadInfoOpen);
        });
      }
      setThreadInfoOpen(false);

      var applySupportTicketPrefill = function () {
        if (!modalForm) {
          return;
        }
        var categoryInput = modalForm.querySelector('select[name="category"]');
        var subjectInput = modalForm.querySelector('input[name="subject"]');
        var messageInput = modalForm.querySelector('textarea[name="message"]');
        if (categoryInput && supportPrefillCategory) {
          var wanted = String(supportPrefillCategory).toLowerCase();
          var matched = false;
          Array.prototype.slice.call(categoryInput.options || []).forEach(function (opt) {
            if (String(opt.value || '').toLowerCase() === wanted) {
              categoryInput.value = opt.value;
              matched = true;
            }
          });
          if (!matched) {
            categoryInput.value = '';
          }
        }
        if (subjectInput && supportPrefillSubject) {
          subjectInput.value = supportPrefillSubject;
        }
        if (messageInput && supportPrefillMessage) {
          messageInput.value = supportPrefillMessage;
        }
      };

      var forceCloseFeedbackModals = function () {
        document.querySelectorAll('[data-support-feedback-modal]').forEach(function (modalEl) {
          modalEl.hidden = true;
          modalEl.classList.remove('is-open');
        });
        document.querySelectorAll('[data-support-feedback-view-modal]').forEach(function (modalEl) {
          modalEl.hidden = true;
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
        isFeedbackViewModalOpen = false;
        syncSupportModalLock();
      };

      var syncSupportModalLock = function () {
        var shouldLock = isFeedbackModalOpen || isFeedbackViewModalOpen || isInsightsModalOpen;
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

      var setFeedbackViewModalOpen = function (open) {
        if (!feedbackViewModal) {
          return;
        }
        isFeedbackViewModalOpen = !!open;
        feedbackViewModal.hidden = !isFeedbackViewModalOpen;
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
          star.textContent = '\u2605';
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
          icon: '\u25CB',
          iconClass: 'is-open',
          statusLabel: 'Open',
        };
        if (normalized === 'closed') {
          state.icon = '\u2713';
          state.iconClass = 'is-closed';
          state.statusLabel = 'Closed';
          return state;
        }
        if (normalized === 'new' || (mode === 'admin' && parseInt(ticket.is_new_for_admin || 0, 10) === 1)) {
          state.icon = '\u25CF';
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
          if (value === '' || value === 'open' || value === 'new' || value === 'new_open' || value === 'all' || value === 'active') {
            return 'active';
          }
          if (['closed', 'needs_feedback'].indexOf(value) === -1) {
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

      var normalizeSupportPriority = function (nextPriority) {
        var value = String(nextPriority || '').toLowerCase();
        if (['all', 'low', 'normal', 'high', 'urgent'].indexOf(value) === -1) {
          return 'all';
        }
        return value;
      };

      var getSupportTicketPriority = function (ticket) {
        var value = String(ticket && ticket.priority ? ticket.priority : 'normal').toLowerCase();
        if (['low', 'normal', 'high', 'urgent'].indexOf(value) === -1) {
          return 'normal';
        }
        return value;
      };

      filter = normalizeSupportFilterForMode(filter);
      priorityFilter = normalizeSupportPriority(priorityFilter);

      var syncFilterUiState = function () {
        var activeFilter = normalizeSupportFilterForMode(filter);
        root.querySelectorAll('[data-support-filter]').forEach(function (btn) {
          var btnFilter = String(btn.getAttribute('data-support-filter') || '').toLowerCase();
          var expectedFilter = btnFilter;
          if (mode === 'admin' && btnFilter === 'open') {
            expectedFilter = 'active';
          }
          btn.classList.toggle('is-active', expectedFilter === activeFilter);
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
        if (priorityFilterControl) {
          priorityFilterControl.value = normalizeSupportPriority(priorityFilter);
        }
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
          item.innerHTML = '<strong>' + (row.ticket_ref || ('#' + String(row.ticket_id || ''))) + '</strong><span>' + (row.subject || 'Support ticket') + '</span><em>Overall ' + (row.overall_satisfaction || 0) + '/5 - Resolved: ' + (String(row.issue_resolved || '0') === '1' ? 'Yes' : 'No') + ' - ' + (row.created_at || '') + '</em>';
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
        if (mode === 'admin') {
          item.classList.add('cmn-support-ticket--admin');
          var requesterEmail = getSupportRequesterEmail(ticket);
          var listStatus = getSupportListStatusBadgeData(ticket);
          var lineTwoRequester = requesterEmail || 'No email';
          var lineTwo = lineTwoRequester + ' \u00b7 ' + String(ticket.updated_at || '\u2014');
          var topRightLabel = String(ticket.channel_label || ticket.subject || ticket.category || 'Support ticket');
          item.innerHTML = ''
            + '<div class="cmn-support-ticket-main">'
              + '<div class="cmn-support-ticket-line1"><strong>' + supportEsc(ticket.ref || ('#' + String(ticket.id || ''))) + '</strong><span>' + supportEsc(topRightLabel) + '</span></div>'
              + '<div class="cmn-support-ticket-line2">' + supportEsc(lineTwo) + '</div>'
            + '</div>'
            + '<span class="cmn-support-list-status ' + supportEsc(listStatus.className) + '">' + supportEsc(listStatus.label) + '</span>';
        } else {
          var priorityKey = getSupportTicketPriority(ticket);
          var priorityLabels = {
            low: 'Low',
            normal: 'Normal',
            high: 'High',
            urgent: 'Urgent'
          };
          var priorityLabel = priorityLabels[priorityKey] || 'Normal';
          var priorityBadge = '<span class="cmn-support-ticket-badge cmn-support-ticket-badge--priority is-' + supportEsc(priorityKey) + '">' + supportEsc(priorityLabel) + '</span>';
          item.innerHTML = '<strong><span class="cmn-ticket-status-icon ' + visual.iconClass + '">' + visual.icon + '</span>' + supportEsc(ticket.ref || '') + '</strong><em>' + supportEsc(visual.statusLabel) + '</em>' + priorityBadge;
        }
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
        var filteredTickets = Array.isArray(tickets) ? tickets.slice() : [];
        if (normalizeSupportPriority(priorityFilter) !== 'all') {
          filteredTickets = filteredTickets.filter(function (ticket) {
            return getSupportTicketPriority(ticket) === normalizeSupportPriority(priorityFilter);
          });
        }
        var normalizedSearch = normalizeSupportSearchQuery(searchQuery);
        if (normalizedSearch) {
          filteredTickets = filteredTickets.filter(function (ticket) {
            return buildSupportSearchHaystack(ticket).indexOf(normalizedSearch) !== -1;
          });
        }
        if (!filteredTickets.length) {
          listEl.innerHTML = hasPendingSelection ? '<div class="cmn-empty">Loading selected ticket...</div>' : '<div class="cmn-empty">No tickets in this view.</div>';
          return;
        }
        var ul = document.createElement('div');
        ul.className = 'cmn-support-ticket-list';
        filteredTickets.forEach(function (ticket) {
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
          activeTicketLastMessageId = 0;
          messagesEl.innerHTML = '<div class="cmn-empty">No messages yet.</div>';
          return;
        }
        var isSystemEventMessage = function (msg) {
          var text = String((msg && msg.message) || '').trim().toLowerCase();
          if (!text) {
            return false;
          }
          return /ticket (closed|reopened) by support/.test(text);
        };
        messages.forEach(function (msg) {
          if (isSystemEventMessage(msg)) {
            var systemPill = document.createElement('div');
            systemPill.className = 'cmn-support-event-pill';
            systemPill.textContent = String(msg.message || '').trim() + (msg.created_at ? (' \u00b7 ' + String(msg.created_at)) : '');
            messagesEl.appendChild(systemPill);
            return;
          }
          var bubble = document.createElement('div');
          bubble.className = 'cmn-support-bubble ' + (msg.sender_type === 'admin' ? 'is-admin' : 'is-user');
          var meta = document.createElement('div');
          meta.className = 'cmn-support-meta';
          var senderLabel = '';
          if (mode === 'admin') {
            if (msg.sender_type === 'admin') {
              senderLabel = msg.sender_name || 'Support';
            } else {
              senderLabel = msg.sender_name || 'Visitor';
            }
          } else {
            senderLabel = msg.sender_type === 'admin' ? 'Support' : 'You';
          }
          meta.textContent = senderLabel + (msg.created_at ? (' \u00b7 ' + String(msg.created_at)) : '');
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
        activeTicketLastMessageId = getLatestSupportMessageId(messages);
        messagesEl.scrollTop = messagesEl.scrollHeight;
      };

      var updateThreadHeader = function (ticket) {
        if (!threadEl) {
          return;
        }
        var titleEl = threadEl.querySelector('[data-support-thread-title]');
        var refEl = threadEl.querySelector('[data-support-thread-ref]');
        var requesterEl = threadEl.querySelector('[data-support-thread-requester]');
        var metaEl = threadEl.querySelector('[data-support-thread-meta]');
        var statusEl = threadEl.querySelector('[data-support-thread-status]');
        var requesterName = getSupportRequesterName(ticket);
        var requesterEmail = getSupportRequesterEmail(ticket);
        var statusChip = ticket ? getSupportStatusChipData(ticket) : null;
        if (titleEl) {
          titleEl.textContent = ticket ? String(ticket.ticket_ref || ticket.ref || ('#' + String(ticket.id || ''))) : 'Ticket Conversation';
        }
        if (refEl) {
          refEl.textContent = ticket ? ('Type: ' + String(ticket.category || 'General')) : 'Select a ticket to view the conversation.';
        }
        if (requesterEl) {
          if (ticket) {
            requesterEl.textContent = requesterEmail ? (requesterName + ' \u00b7 ' + requesterEmail) : requesterName;
            requesterEl.hidden = false;
          } else {
            requesterEl.textContent = '';
            requesterEl.hidden = true;
          }
        }
        if (metaEl) {
          if (ticket) {
            metaEl.textContent = 'Created ' + String(ticket.created_at || '\u2014') + ' \u00b7 Last updated ' + String(ticket.updated_at || '\u2014');
            metaEl.hidden = false;
          } else {
            metaEl.textContent = '';
            metaEl.hidden = true;
          }
        }
        if (statusEl) {
          if (ticket && statusChip) {
            statusEl.hidden = false;
            statusEl.textContent = statusChip.label;
            statusEl.classList.remove('is-approved', 'is-pending', 'is-muted', 'is-declined');
            statusEl.classList.add(statusChip.className);
          } else {
            statusEl.hidden = true;
            statusEl.textContent = '';
            statusEl.classList.remove('is-approved', 'is-pending', 'is-muted', 'is-declined');
          }
        }
        if (infoToggleBtn) {
          infoToggleBtn.disabled = !ticket;
          if (!ticket) {
            setThreadInfoOpen(false);
          }
        }
      };

      var updateSupportTicketInfoPanel = function (ticket) {
        var panel = root.querySelector('[data-support-ticket-info]');
        if (!panel) {
          return;
        }
        var statusCell = panel.querySelector('[data-support-info-status]');
        var requesterCell = panel.querySelector('[data-support-info-requester]');
        var emailCell = panel.querySelector('[data-support-info-email]');
        var channelCell = panel.querySelector('[data-support-info-channel]');
        var typeCell = panel.querySelector('[data-support-info-type]');
        var createdCell = panel.querySelector('[data-support-info-created]');
        var updatedCell = panel.querySelector('[data-support-info-updated]');
        if (!ticket) {
          [statusCell, requesterCell, emailCell, channelCell, typeCell, createdCell, updatedCell].forEach(function (el) {
            if (el) {
              el.textContent = '-';
            }
          });
          return;
        }
        var statusChip = getSupportStatusChipData(ticket);
        var channelLabel = String(ticket.channel_label || '').trim();
        if (!channelLabel) {
          channelLabel = String(ticket.channel_key || '').trim() === 'website_live_chat' ? 'Website Live Chat' : 'Portal';
        }
        if (statusCell) {
          statusCell.textContent = statusChip.label;
        }
        if (requesterCell) {
          requesterCell.textContent = getSupportRequesterName(ticket);
        }
        if (emailCell) {
          emailCell.textContent = getSupportRequesterEmail(ticket) || '-';
        }
        if (channelCell) {
          channelCell.textContent = channelLabel;
        }
        if (typeCell) {
          typeCell.textContent = String(ticket.category || 'General');
        }
        if (createdCell) {
          createdCell.textContent = String(ticket.created_at || '-');
        }
        if (updatedCell) {
          updatedCell.textContent = String(ticket.updated_at || '-');
        }
      };

      var normalizeSupportActionLabels = function () {
        var reopenBtn = root.querySelector('[data-support-reopen-ticket]');
        var saveTranscriptBtn = root.querySelector('[data-support-save-transcript]');
        var emailTranscriptBtn = root.querySelector('[data-support-email-transcript]');
        if (reopenBtn) {
          reopenBtn.textContent = 'Reopen';
        }
        if (saveTranscriptBtn) {
          saveTranscriptBtn.textContent = 'Save';
        }
        if (emailTranscriptBtn) {
          emailTranscriptBtn.textContent = 'Email Transcript';
        }
      };

      var toggleAdminButtons = function (ticket, feedback) {
        normalizeSupportActionLabels();
        var closeBtn = root.querySelector('[data-support-close-ticket]');
        var reopenBtn = root.querySelector('[data-support-reopen-ticket]');
        var requestFeedbackBtn = root.querySelector('[data-support-request-feedback]');
        var openFeedbackBtn = root.querySelector('[data-support-open-feedback]');
        var saveTranscriptBtn = root.querySelector('[data-support-save-transcript]');
        var emailTranscriptBtn = root.querySelector('[data-support-email-transcript]');
        var feedbackCount = ticket ? parseInt(ticket.feedback_count || '0', 10) : 0;
        if (isNaN(feedbackCount)) {
          feedbackCount = 0;
        }
        var hasFeedback = !!feedback || feedbackCount > 0;
        var requestActive = ticket ? parseInt(ticket.feedback_request_active || '0', 10) === 1 : false;
        var canSendRequest = ticket ? parseInt(ticket.feedback_request_can_send || '0', 10) === 1 : false;
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
            closeBtn.hidden = false;
            closeBtn.classList.add('cmn-primary');
            closeBtn.classList.remove('cmn-ghost');
          }
          if (reopenBtn) {
            reopenBtn.disabled = true;
            reopenBtn.hidden = true;
            reopenBtn.classList.add('cmn-ghost');
            reopenBtn.classList.remove('cmn-primary');
          }
          if (requestFeedbackBtn) {
            requestFeedbackBtn.disabled = true;
            requestFeedbackBtn.textContent = 'Request feedback';
          }
          if (openFeedbackBtn) {
            openFeedbackBtn.hidden = true;
            openFeedbackBtn.disabled = true;
          }
          setFeedbackViewModalOpen(false);
          return;
        }
        if (closeBtn) {
          closeBtn.disabled = ticket.status === 'closed';
          closeBtn.hidden = ticket.status === 'closed';
          closeBtn.classList.add('cmn-primary');
          closeBtn.classList.remove('cmn-ghost');
        }
        if (reopenBtn) {
          reopenBtn.disabled = ticket.status !== 'closed';
          reopenBtn.hidden = ticket.status !== 'closed';
          if (ticket.status === 'closed') {
            reopenBtn.classList.add('cmn-primary');
            reopenBtn.classList.remove('cmn-ghost');
          } else {
            reopenBtn.classList.add('cmn-ghost');
            reopenBtn.classList.remove('cmn-primary');
          }
        }
        if (openFeedbackBtn) {
          var canViewFeedback = ticket.status === 'closed' && (hasFeedback || requestActive);
          openFeedbackBtn.hidden = !canViewFeedback;
          openFeedbackBtn.disabled = !canViewFeedback;
          openFeedbackBtn.textContent = hasFeedback ? 'Feedback' : 'Feedback status';
          if (!canViewFeedback) {
            setFeedbackViewModalOpen(false);
          }
        }
        if (requestFeedbackBtn) {
          requestFeedbackBtn.disabled = !canSendRequest || hasFeedback;
          requestFeedbackBtn.textContent = requestActive ? 'Feedback requested' : 'Request feedback';
        }
      };

      var renderFeedback = function (ticket, feedback) {
        var feedbackRoot = root.querySelector('[data-support-feedback]');
        var feedbackBadge = root.querySelector('[data-support-feedback-badge]');
        var shouldForcePrompt = !!(
          supportShouldPromptFeedback &&
          ticket &&
          ticket.status === 'closed' &&
          (supportRole === 'candidate' || supportRole === 'school')
        );
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
          clearSupportFeedbackIntentParam();
          var summary = document.createElement('details');
          summary.className = 'cmn-support-feedback-summary';
          summary.innerHTML = '<summary>Feedback</summary><span>Support: ' + feedback.support_rating + '/5 \u2022 Response: ' + feedback.response_time_rating + '/5 \u2022 Overall: ' + feedback.overall_satisfaction + '/5 \u2022 Resolved: ' + (String(feedback.issue_resolved) === '1' ? 'Yes' : 'No') + '</span>';
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
            if (!feedback && ticket && ticket.status === 'closed' && parseInt(ticket.feedback_request_active || '0', 10) === 1) {
              var waiting = document.createElement('div');
              waiting.className = 'cmn-muted';
              waiting.textContent = 'Feedback requested. Waiting for response until ' + (ticket.feedback_request_expires_label || ticket.feedback_request_expires_at || 'the expiry window ends') + '.';
              feedbackRoot.appendChild(waiting);
            }
            return;
        }
        if ((supportRole === 'candidate' || supportRole === 'school') && (parseInt(ticket.requires_feedback || '0', 10) === 1 || shouldForcePrompt)) {
          feedbackRoot.innerHTML = '<div class="cmn-muted">Please submit feedback for this closed ticket.</div>';
          maybeOpenFeedbackModal(ticket, null);
          clearSupportFeedbackIntentParam();
          return;
        }
        clearSupportFeedbackIntentParam();
      };

      var renderPayrollContext = function (ticket, payrollQuery, payrollContext) {
        var contextRoot = root.querySelector('[data-support-payroll-context]');
        if (!contextRoot) {
          return;
        }

        var hasContextPayload = !!(payrollQuery || payrollContext);
        var category = String((ticket && ticket.category) ? ticket.category : '').toLowerCase();
        var queueKey = String((ticket && ticket.queue_key) ? ticket.queue_key : '').toLowerCase();
        var isPayrollTicket = hasContextPayload || category === 'payroll' || queueKey === 'payroll';
        if (!ticket || !isPayrollTicket) {
          contextRoot.hidden = true;
          contextRoot.innerHTML = '';
          return;
        }

        payrollQuery = (payrollQuery && typeof payrollQuery === 'object') ? payrollQuery : {};
        payrollContext = (payrollContext && typeof payrollContext === 'object') ? payrollContext : {};

        var bankStatus = String(payrollContext.bank_status || '').toLowerCase();
        var bankStatusLabel = String(payrollContext.bank_status_label || (bankStatus === 'complete' ? 'Complete' : (bankStatus === 'missing' ? 'Missing' : '—')));
        var bankStatusChip = bankStatus === 'complete' ? 'is-approved' : (bankStatus === 'missing' ? 'is-pending' : 'is-muted');

        var payoutStatus = String(payrollContext.payout_item_status || '').toLowerCase();
        var payoutStatusLabel = String(payrollContext.payout_item_status_label || (payoutStatus === 'ready' ? 'Ready' : (payoutStatus === 'hold' ? 'Hold' : '—')));
        var payoutStatusChip = payoutStatus === 'ready' ? 'is-approved' : (payoutStatus === 'hold' ? 'is-declined' : 'is-muted');

        var shiftsCount = parseInt(payrollContext.shifts_count || '0', 10);
        if (Number.isNaN(shiftsCount) || shiftsCount < 0) {
          shiftsCount = 0;
        }

        var grossAmount = Number(payrollContext.gross_amount || 0);
        var grossLabel = Number.isNaN(grossAmount) ? '—' : ('GBP ' + grossAmount.toFixed(2));

        var adjustmentsTotal = Number(payrollContext.adjustments_total || 0);
        if (Number.isNaN(adjustmentsTotal)) {
          adjustmentsTotal = 0;
        }
        var adjustmentsCount = parseInt(payrollContext.adjustments_count || '0', 10);
        if (Number.isNaN(adjustmentsCount) || adjustmentsCount < 0) {
          adjustmentsCount = 0;
        }
        var adjustmentsLabel = 'None';
        if (adjustmentsCount > 0 || Math.abs(adjustmentsTotal) > 0.0009) {
          adjustmentsLabel = (adjustmentsTotal >= 0 ? '+' : '-') + 'GBP ' + Math.abs(adjustmentsTotal).toFixed(2);
          if (adjustmentsCount > 0) {
            adjustmentsLabel += ' (' + adjustmentsCount + ')';
          }
        }

        var periodLabel = String(payrollContext.period_label || '');
        if (!periodLabel) {
          var periodStart = String(payrollQuery.period_start || '');
          var periodEnd = String(payrollQuery.period_end || '');
          if (periodStart && periodEnd) {
            periodLabel = periodStart + ' -> ' + periodEnd;
          } else {
            periodLabel = String(payrollQuery.period_id || '—');
          }
        }

        var issueTypeLabel = String(payrollContext.issue_type_label || payrollQuery.issue_type_label || 'Payroll');
        contextRoot.innerHTML = '' +
          '<div class="cmn-support-payroll-context__header">' +
            '<strong>Payroll context</strong>' +
            '<span class="cmn-muted">Read-only</span>' +
          '</div>' +
          '<div class="cmn-support-payroll-context__grid">' +
            '<div class="cmn-support-payroll-context__row"><span>Bank status</span><strong><span class="cmn-status-chip ' + supportEsc(bankStatusChip) + '">' + supportEsc(bankStatusLabel) + '</span></strong></div>' +
            '<div class="cmn-support-payroll-context__row"><span>Shifts count in period</span><strong>' + supportEsc(String(shiftsCount)) + '</strong></div>' +
            '<div class="cmn-support-payroll-context__row"><span>Gross amount</span><strong>' + supportEsc(grossLabel) + '</strong></div>' +
            '<div class="cmn-support-payroll-context__row"><span>Payout item status</span><strong><span class="cmn-status-chip ' + supportEsc(payoutStatusChip) + '">' + supportEsc(payoutStatusLabel) + '</span></strong></div>' +
            '<div class="cmn-support-payroll-context__row"><span>Applied adjustments</span><strong>' + supportEsc(adjustmentsLabel) + '</strong></div>' +
            '<div class="cmn-support-payroll-context__row"><span>Period</span><strong>' + supportEsc(periodLabel) + '</strong></div>' +
            '<div class="cmn-support-payroll-context__row"><span>Issue type</span><strong>' + supportEsc(issueTypeLabel) + '</strong></div>' +
          '</div>';
        contextRoot.hidden = false;
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
              feedbackSuppressUntilByTicket[String(feedbackTicketId)] = Date.now() + 120000;
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
            activeTicket = null;
            activeTicketId = null;
            updateThreadHeader(null);
            updateSupportTicketInfoPanel(null);
            renderPayrollContext(null, null, null);
            activeTicketLastMessageId = 0;
            if (messagesEl) {
              messagesEl.innerHTML = '<div class="cmn-empty">' + ((data && data.data && data.data.message) ? data.data.message : 'Ticket not found.') + '</div>';
            }
            if (saveDraftBtn) {
              saveDraftBtn.disabled = true;
            }
            return;
          }
          var ticket = data.data.ticket;
          activeTicket = ticket;
          activeTicketId = ticket && ticket.id ? ticket.id : activeTicketId;
          setDeepTicketParam(activeTicketId);
          syncSelectedTicketRow();
          updateThreadHeader(ticket);
          updateSupportTicketInfoPanel(ticket);
          restoreSupportDraft();
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
            if (saveDraftBtn) {
              saveDraftBtn.disabled = !!isClosed;
            }
          }
          renderMessages(ticket, data.data.messages || []);
          var ticketFeedback = data.data.feedback || (ticket && ticket.id ? feedbackCacheByTicket[String(ticket.id)] : null) || null;
          toggleAdminButtons(ticket, ticketFeedback);
          renderFeedback(ticket, ticketFeedback);
          renderPayrollContext(ticket, data.data.payroll_query || null, data.data.payroll_context || null);
          if (listEl && activeTicketId) {
            var selectedRow = listEl.querySelector('.cmn-support-ticket[data-ticket-id="' + String(activeTicketId) + '"]');
            if (!selectedRow) {
              var fallbackTicket = {
                id: activeTicketId,
                ref: ticket.ticket_ref || ticket.ref || ('#' + String(activeTicketId)),
                subject: ticket.subject || 'Support ticket',
                status: ticket.status || 'open',
                category: ticket.category || '',
                priority: ticket.priority || 'normal',
                requester_name: ticket.requester_name || '',
                requester_email: ticket.requester_email || '',
                created_at: ticket.created_at || '',
                updated_at: ticket.updated_at || '',
                is_new_for_admin: ticket.is_new_for_admin || 0,
                feedback_count: 0,
                requires_feedback: parseInt(ticket.requires_feedback || '0', 10) || 0
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
        if (normalizeSupportPriority(priorityFilter) !== 'all') {
          payload.priority = normalizeSupportPriority(priorityFilter);
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
          if (data.data && data.data.priority_filter) {
            priorityFilter = normalizeSupportPriority(data.data.priority_filter);
          }
          syncFilterUiState();
          if (data.data && data.data.selected_ticket_id) {
            activeTicketId = parseInt(data.data.selected_ticket_id, 10) || activeTicketId;
          }
          var ticketsPayload = Array.isArray(data.data.tickets) ? data.data.tickets : [];
          supportTicketsCache = ticketsPayload.slice();
          var filteredTicketsPayload = ticketsPayload.slice();
          if (normalizeSupportPriority(priorityFilter) !== 'all') {
            filteredTicketsPayload = filteredTicketsPayload.filter(function (ticket) {
              return getSupportTicketPriority(ticket) === normalizeSupportPriority(priorityFilter);
            });
          }
          var normalizedSearch = normalizeSupportSearchQuery(searchQuery);
          if (normalizedSearch) {
            filteredTicketsPayload = filteredTicketsPayload.filter(function (ticket) {
              return buildSupportSearchHaystack(ticket).indexOf(normalizedSearch) !== -1;
            });
          }
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
          } else if (mode === 'admin' && filteredTicketsPayload.length) {
            loadTicket(filteredTicketsPayload[0].id);
          } else if (mode !== 'admin' && filteredTicketsPayload.length) {
            loadTicket(filteredTicketsPayload[0].id);
          } else {
            forceCloseFeedbackModals();
            activeTicketId = null;
            activeTicket = null;
            activeTicketLastMessageId = 0;
            updateThreadHeader(null);
            updateSupportTicketInfoPanel(null);
            renderPayrollContext(null, null, null);
            if (messagesEl) {
              messagesEl.innerHTML = '<div class="cmn-empty">Select a ticket to view messages.</div>';
            }
            if (saveDraftBtn) {
              saveDraftBtn.disabled = true;
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

      if (priorityFilterControl) {
        priorityFilterControl.addEventListener('change', function () {
          priorityFilter = normalizeSupportPriority(priorityFilterControl.value || 'all');
          syncFilterUiState();
          loadTickets();
        });
      }

      if (searchControl) {
        searchControl.addEventListener('input', function () {
          searchQuery = normalizeSupportSearchQuery(searchControl.value || '');
          renderList(supportTicketsCache, !!getDeepTicketParam());
        });
      }

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

      if (openInsightsBtn) {
        openInsightsBtn.addEventListener('click', function () {
          insightsFilter = 'recent';
          if (insightsModal) {
            insightsModal.querySelectorAll('[data-support-insights-filter]').forEach(function (btn, idx) {
              btn.classList.toggle('is-active', idx === 0);
            });
          }
          renderInsightsList();
          setInsightsModalOpen(true);
        });
      }

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

      if (feedbackViewBtn) {
        feedbackViewBtn.addEventListener('click', function () {
          if (feedbackViewBtn.disabled) {
            return;
          }
          setFeedbackViewModalOpen(true);
        });
      }

      if (feedbackViewModal) {
        feedbackViewModal.querySelectorAll('[data-support-feedback-view-close]').forEach(function (btn) {
          btn.addEventListener('click', function () {
            setFeedbackViewModalOpen(false);
          });
        });
      }

      if (mode === 'admin') {
        var closeBtn = root.querySelector('[data-support-close-ticket]');
        var reopenBtn = root.querySelector('[data-support-reopen-ticket]');
        var requestFeedbackBtn = root.querySelector('[data-support-request-feedback]');
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
        if (requestFeedbackBtn) {
          requestFeedbackBtn.addEventListener('click', function () {
            if (!activeTicketId || requestFeedbackBtn.disabled) {
              return;
            }
            requestFeedbackBtn.disabled = true;
            supportFetch('cmn_support_request_feedback', { ticket_id: activeTicketId }).then(function (data) {
              if (data && data.success && data.data && data.data.message) {
                alert(data.data.message);
              }
              loadTickets();
              loadTicket(activeTicketId);
            }).catch(function () {
              alert('Unable to send feedback request.');
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

      normalizeSupportActionLabels();

      if (replyTextarea) {
        replyTextarea.addEventListener('input', function () {
          persistSupportDraft();
        });
      }

      if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', function () {
          persistSupportDraft();
          var originalText = saveDraftBtn.textContent;
          saveDraftBtn.textContent = 'Draft Saved';
          window.setTimeout(function () {
            saveDraftBtn.textContent = originalText;
          }, 1200);
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
            try {
              window.localStorage.removeItem(getSupportDraftStorageKey());
            } catch (_draftErr) {
              // Ignore localStorage failures.
            }
            loadTicket(activeTicketId);
            loadTickets();
          });
        });
      }

      if (openBtn && modal) {
        openBtn.addEventListener('click', function () {
          applySupportTicketPrefill();
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

      var refreshActiveTicketRealtime = function () {
        if (!activeTicketId || document.hidden || supportRealtimeTickInFlight || isInsightsModalOpen) {
          return Promise.resolve();
        }
        if (isFeedbackModalOpen && (supportRole === 'candidate' || supportRole === 'school')) {
          return Promise.resolve();
        }
        supportRealtimeTickInFlight = true;
        return supportFetch('cmn_support_get_ticket', { ticket_id: activeTicketId }).then(function (data) {
          if (!data || !data.success || !data.data || !data.data.ticket) {
            return;
          }

          var latestTicket = data.data.ticket;
          var latestMessages = Array.isArray(data.data.messages) ? data.data.messages : [];
          var latestMessageId = getLatestSupportMessageId(latestMessages);
          var previousStatus = activeTicket && activeTicket.status ? String(activeTicket.status) : '';
          var statusChanged = String(latestTicket.status || '') !== previousStatus;
          var messageChanged = latestMessageId !== activeTicketLastMessageId;
          var ticketFeedback = data.data.feedback || (latestTicket && latestTicket.id ? feedbackCacheByTicket[String(latestTicket.id)] : null) || null;

          activeTicket = latestTicket;
          updateThreadHeader(latestTicket);
          updateSupportTicketInfoPanel(latestTicket);
          toggleAdminButtons(latestTicket, ticketFeedback);

          if (messageChanged) {
            renderMessages(latestTicket, latestMessages);
          }

          if (statusChanged || messageChanged) {
            renderFeedback(latestTicket, ticketFeedback);
            renderPayrollContext(latestTicket, data.data.payroll_query || null, data.data.payroll_context || null);
          }

          if (statusChanged || messageChanged) {
            var nowTs = Date.now();
            if ((nowTs - supportLastListRefreshAt) >= 1500) {
              supportLastListRefreshAt = nowTs;
              loadTickets();
            }
          }
        }).finally(function () {
          supportRealtimeTickInFlight = false;
        });
      };
      if (cmnPollManager) {
        cmnPollManager.register({
          key: 'cmn-support-realtime-' + String(supportRootIndex || 0),
          intervalMs: 3000,
          maxIntervalMs: 15000,
          callback: function () {
            return refreshActiveTicketRealtime();
          },
          visibleOnly: true,
          triggerOnFocus: true,
          triggerOnVisibility: true,
          backoffOnError: true,
          immediate: true,
        });
      } else {
        window.setInterval(function () {
          refreshActiveTicketRealtime();
        }, 3000);
        document.addEventListener('visibilitychange', function () {
          if (!document.hidden) {
            refreshActiveTicketRealtime();
          }
        });
        window.addEventListener('focus', function () {
          refreshActiveTicketRealtime();
        });
      }

      syncFilterUiState();
      loadTickets();

      if (supportShouldAutoOpen && modal) {
        applySupportTicketPrefill();
        modal.classList.add('is-open');
      }
    });
  }

  var staffLoungeRoots = document.querySelectorAll('[data-staff-lounge]');
  if (staffLoungeRoots.length && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.staffLoungeNonce) {
    staffLoungeRoots.forEach(function (root, loungeIndex) {
      var threadType = root.getAttribute('data-thread-type') || 'staff_lounge';
      var listEl = root.querySelector('[data-staff-lounge-messages]');
      var formEl = root.querySelector('[data-staff-lounge-form]');
      var msgEl = root.querySelector('[data-staff-lounge-message]');
      var focusButtons = document.querySelectorAll('[data-staff-lounge-focus-compose]');
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
          meta.textContent = (row.sender_name || 'Staff') + ' - ' + (row.created_at || '');
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

      if (focusButtons.length && formEl) {
        var focusComposer = function () {
          var input = formEl.querySelector('textarea[name="message"]');
          if (!input) {
            return;
          }
          input.focus();
          try {
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
          } catch (error) {
            input.scrollIntoView();
          }
        };
        focusButtons.forEach(function (button) {
          button.addEventListener('click', focusComposer);
        });
      }

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

      if (cmnPollManager) {
        cmnPollManager.register({
          key: 'cmn-staff-lounge-' + String(threadType) + '-' + String(loungeIndex || 0),
          intervalMs: 5000,
          maxIntervalMs: 20000,
          callback: function () {
            return fetchRows();
          },
          visibleOnly: true,
          triggerOnFocus: true,
          triggerOnVisibility: true,
          backoffOnError: true,
          immediate: true,
        });
      } else {
        fetchRows();
        window.setInterval(fetchRows, 5000);
      }
    });
  }

  var marketingRoot = document.querySelector('[data-marketing-root]');
  if (marketingRoot && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.staffNonce) {
    var marketingState = {
      rows: [],
      selectedIds: {},
      activeCampaignId: 0
    };
    var initialMarketingTab = (new URLSearchParams(window.location.search).get('marketing_tab') || 'lead_finder').toLowerCase();
    var activeTab = initialMarketingTab;
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
    var templatesBody = marketingRoot.querySelector('[data-marketing-templates]');
    var templateMsg = marketingRoot.querySelector('[data-marketing-template-message]');
    var templateValidation = marketingRoot.querySelector('[data-marketing-template-validation]');
    var sendLogBody = marketingRoot.querySelector('[data-marketing-sendlog]');
    var sendLogMsg = marketingRoot.querySelector('[data-marketing-sendlog-message]');
    var unsubBody = marketingRoot.querySelector('[data-marketing-unsubscribes]');
    var unsubMsg = marketingRoot.querySelector('[data-marketing-unsub-message]');
    var segmentBody = marketingRoot.querySelector('[data-marketing-segments]');
    var segmentPreviewBody = marketingRoot.querySelector('[data-marketing-segment-preview]');
    var segmentMsg = marketingRoot.querySelector('[data-marketing-segment-message]');
    var quickMsg = marketingRoot.querySelector('[data-marketing-quick-message]');

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

    var escHtml = function (value) {
      return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    };

    var knownSmartTags = [
      'school_name', 'school_location', 'school_town', 'contact_name', 'contact_role',
      'email', 'portal_link', 'unsubscribe_link', 'email_name', 'first_name', 'last_name',
      'location', 'postcode', 'distance', 'account_manager_name', 'pipeline_stage',
      'last_contacted_date', 'company_name'
    ];

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
    setTab(activeTab);

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
          '<td>' + (row.account_manager_name || '-') + '</td>' +
          '<td>' + (row.last_contacted || '-') + '</td>' +
          '<td>' + (row.last_replied || '-') + '</td>' +
          '<td>' + (row.distance_miles || '-') + '</td>';
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
        queueSummary.textContent = 'Queued: ' + String(queue.queued || 0) + ' - Sent: ' + String(queue.sent || 0) + ' - Failed: ' + String(queue.failed || 0);
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
        repliesSummary.textContent = 'Replies: ' + String(counts.total || 0) + ' - Matched: ' + String(counts.matched || 0) + ' - Unmatched: ' + String(counts.unmatched || 0);
      }
      repliesBody.innerHTML = '';
      if (!rows.length) {
        repliesBody.innerHTML = '<tr><td colspan="6">No replies yet.</td></tr>';
        return;
      }
      rows.forEach(function (row) {
        var tr = document.createElement('tr');
        tr.innerHTML = '' +
          '<td>' + (row.received_at_label || row.received_at || '-') + '</td>' +
          '<td>' + (row.school_name || 'Unmatched') + '</td>' +
          '<td>' + (row.from_email || '-') + '</td>' +
          '<td>' + (row.subject || '-') + '</td>' +
          '<td>' + (row.snippet || '-') + '</td>' +
          '<td>' + (row.campaign_id ? ('#' + row.campaign_id) : '-') + '</td>';
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

    var validateTemplateTags = function (subjectValue, bodyValue) {
      var regex = /{{\s*([a-z0-9_]+)(?:\|[^}]+)?\s*}}/gi;
      var source = String(subjectValue || '') + '\n' + String(bodyValue || '');
      var detected = {};
      var unknown = {};
      var match = null;
      while ((match = regex.exec(source)) !== null) {
        var tag = String(match[1] || '').toLowerCase();
        if (!tag) {
          continue;
        }
        detected[tag] = 1;
        if (knownSmartTags.indexOf(tag) === -1) {
          unknown[tag] = 1;
        }
      }
      return {
        detected: Object.keys(detected),
        unknown: Object.keys(unknown)
      };
    };

    var renderTemplates = function (templates) {
      if (!templatesBody) {
        return;
      }
      templatesBody.innerHTML = '';
      if (!Array.isArray(templates) || !templates.length) {
        templatesBody.innerHTML = '<tr><td colspan="4">No templates saved yet.</td></tr>';
        return;
      }
      templates.forEach(function (row) {
        var id = parseInt(row.id || '0', 10) || 0;
        var tr = document.createElement('tr');
        tr.innerHTML = '' +
          '<td>' + escHtml(row.name || '') + '</td>' +
          '<td>' + escHtml(row.subject_template || '') + '</td>' +
          '<td>' + escHtml(row.updated_at || '') + '</td>' +
          '<td><button type="button" class="cmn-ghost cmn-btn-mini" data-marketing-template-delete="' + id + '">Delete</button></td>';
        templatesBody.appendChild(tr);
      });
      templatesBody.querySelectorAll('[data-marketing-template-delete]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var templateId = parseInt(btn.getAttribute('data-marketing-template-delete') || '0', 10);
          if (!templateId || !window.confirm('Delete this template?')) {
            return;
          }
          mFetch('cmn_marketing_delete_template', { template_id: templateId }).then(function (data) {
            if (!data || !data.success || !data.data) {
              if (templateMsg) {
                templateMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to delete template.';
              }
              return;
            }
            if (templateMsg) {
              templateMsg.textContent = data.data.message || 'Template deleted.';
            }
            renderTemplates(data.data.templates || []);
          });
        });
      });
    };

    var refreshTemplates = function () {
      return mFetch('cmn_marketing_get_templates', {}).then(function (data) {
        if (data && data.success && data.data) {
          renderTemplates(data.data.templates || []);
        }
      });
    };

    var readSendLogFilters = function () {
      var get = function (key, fallback) {
        var el = marketingRoot.querySelector('[data-marketing-sendlog-filter="' + key + '"]');
        return el ? el.value : fallback;
      };
      return {
        days: parseInt(get('days', '30'), 10) || 30,
        status: get('status', ''),
        campaign_id: parseInt(get('campaign_id', '0'), 10) || 0,
        template_id: parseInt(get('template_id', '0'), 10) || 0
      };
    };

    var renderSendLog = function (rows) {
      if (!sendLogBody) {
        return;
      }
      sendLogBody.innerHTML = '';
      if (!Array.isArray(rows) || !rows.length) {
        sendLogBody.innerHTML = '<tr><td colspan="6">No send log rows.</td></tr>';
        return;
      }
      rows.forEach(function (row) {
        var tr = document.createElement('tr');
        tr.innerHTML = '' +
          '<td>' + escHtml(row.campaign_name || '') + '</td>' +
          '<td>' + escHtml(row.template_name || '') + '</td>' +
          '<td>' + escHtml(row.to_email || '') + '</td>' +
          '<td>' + escHtml(row.status || '') + '</td>' +
          '<td>' + escHtml(row.error_message || '') + '</td>' +
          '<td>' + escHtml(row.sent_at || '') + '</td>';
        sendLogBody.appendChild(tr);
      });
    };

    var refreshSendLog = function () {
      return mFetch('cmn_marketing_get_send_log', readSendLogFilters()).then(function (data) {
        if (!data || !data.success || !data.data) {
          if (sendLogMsg) {
            sendLogMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to load send log.';
          }
          renderSendLog([]);
          return;
        }
        renderSendLog(data.data.rows || []);
      });
    };

    var renderUnsubscribes = function (rows) {
      if (!unsubBody) {
        return;
      }
      unsubBody.innerHTML = '';
      if (!Array.isArray(rows) || !rows.length) {
        unsubBody.innerHTML = '<tr><td colspan="5">No unsubscribed emails.</td></tr>';
        return;
      }
      rows.forEach(function (row) {
        var email = row.email || '';
        var tr = document.createElement('tr');
        tr.innerHTML = '' +
          '<td>' + escHtml(email) + '</td>' +
          '<td>' + escHtml((row.entity_type || '') + '#' + (row.entity_id || 0)) + '</td>' +
          '<td>' + escHtml(row.reason || '') + '</td>' +
          '<td>' + escHtml(row.unsubscribed_at || '') + '</td>' +
          '<td><button type="button" class="cmn-ghost cmn-btn-mini" data-marketing-unsub-remove="' + escHtml(email) + '">Remove</button></td>';
        unsubBody.appendChild(tr);
      });
      unsubBody.querySelectorAll('[data-marketing-unsub-remove]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var email = btn.getAttribute('data-marketing-unsub-remove') || '';
          if (!email) {
            return;
          }
          mFetch('cmn_marketing_remove_unsubscribe', { email: email }).then(function (data) {
            if (unsubMsg) {
              unsubMsg.textContent = (data && data.data && data.data.message) ? data.data.message : (data && data.success ? 'Removed.' : 'Unable to remove.');
            }
            refreshUnsubscribes();
          });
        });
      });
    };

    var refreshUnsubscribes = function () {
      return mFetch('cmn_marketing_get_unsubscribes', {}).then(function (data) {
        if (data && data.success && data.data) {
          renderUnsubscribes(data.data.rows || []);
        }
      });
    };

    var renderSegments = function (segments) {
      if (!segmentBody) {
        return;
      }
      segmentBody.innerHTML = '';
      if (!Array.isArray(segments) || !segments.length) {
        segmentBody.innerHTML = '<tr><td colspan="3">No segments saved.</td></tr>';
        return;
      }
      segments.forEach(function (row) {
        var id = parseInt(row.id || '0', 10) || 0;
        var tr = document.createElement('tr');
        tr.innerHTML = '' +
          '<td>' + escHtml(row.name || '') + '</td>' +
          '<td>' + escHtml(row.updated_at || '') + '</td>' +
          '<td>' +
            '<button type="button" class="cmn-ghost cmn-btn-mini" data-marketing-segment-run="' + id + '">Run segment</button> ' +
            '<button type="button" class="cmn-ghost cmn-btn-mini" data-marketing-segment-to-list="' + id + '">Convert to list</button>' +
          '</td>';
        segmentBody.appendChild(tr);
      });
      segmentBody.querySelectorAll('[data-marketing-segment-run]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var segmentId = parseInt(btn.getAttribute('data-marketing-segment-run') || '0', 10);
          mFetch('cmn_marketing_run_segment', { segment_id: segmentId }).then(function (data) {
            if (!data || !data.success || !data.data) {
              if (segmentMsg) {
                segmentMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to run segment.';
              }
              return;
            }
            if (segmentMsg) {
              segmentMsg.textContent = 'Matches: ' + String(data.data.count || 0);
            }
            if (!segmentPreviewBody) {
              return;
            }
            segmentPreviewBody.innerHTML = '';
            var rows = data.data.rows || [];
            if (!rows.length) {
              segmentPreviewBody.innerHTML = '<tr><td colspan="4">No matching rows.</td></tr>';
              return;
            }
            rows.forEach(function (row) {
              var tr = document.createElement('tr');
              tr.innerHTML = '<td>' + escHtml(row.school_name || '') + '</td>' +
                '<td>' + escHtml(row.school_email || '') + '</td>' +
                '<td>' + escHtml(row.location || '') + '</td>' +
                '<td>' + escHtml(row.status || '') + '</td>';
              segmentPreviewBody.appendChild(tr);
            });
          });
        });
      });
      segmentBody.querySelectorAll('[data-marketing-segment-to-list]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var segmentId = parseInt(btn.getAttribute('data-marketing-segment-to-list') || '0', 10);
          var listName = window.prompt('New list name');
          if (!listName) {
            return;
          }
          mFetch('cmn_marketing_segment_to_list', { segment_id: segmentId, list_name: listName }).then(function (data) {
            if (segmentMsg) {
              segmentMsg.textContent = (data && data.data && data.data.message) ? data.data.message : (data && data.success ? 'Converted.' : 'Unable to convert segment.');
            }
            refreshLists();
          });
        });
      });
    };

    var refreshSegments = function () {
      return mFetch('cmn_marketing_get_segments', {}).then(function (data) {
        if (data && data.success && data.data) {
          renderSegments(data.data.segments || []);
        }
      });
    };

    var refreshOverview = function () {
      return mFetch('cmn_marketing_get_overview', {}).then(function (data) {
        if (!data || !data.success || !data.data) {
          return;
        }
        var overview = data.data.overview || {};
        Object.keys(overview).forEach(function (key) {
          if (typeof overview[key] === 'object') {
            return;
          }
          var el = marketingRoot.querySelector('[data-marketing-kpi="' + key + '"]');
          if (el) {
            el.textContent = String(overview[key]);
          }
        });
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
            setTab('send_log');
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
            setTab('send_log');
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
          return;
        }
        if (action === 'save-template') {
          mFetch('cmn_marketing_save_template', {
            name: (marketingRoot.querySelector('[data-marketing-template="name"]') || {}).value || '',
            subject_template: (marketingRoot.querySelector('[data-marketing-template="subject_template"]') || {}).value || '',
            body_template_html: (marketingRoot.querySelector('[data-marketing-template="body_template_html"]') || {}).value || '',
            body_template_text: (marketingRoot.querySelector('[data-marketing-template="body_template_text"]') || {}).value || ''
          }).then(function (data) {
            if (!data || !data.success || !data.data) {
              if (templateMsg) {
                templateMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to save template.';
              }
              return;
            }
            if (templateMsg) {
              templateMsg.textContent = data.data.message || 'Template saved.';
            }
            renderTemplates(data.data.templates || []);
          });
          return;
        }
        if (action === 'validate-template-tags') {
          var subjectValue = ((marketingRoot.querySelector('[data-marketing-template="subject_template"]') || {}).value || '');
          var bodyValue = ((marketingRoot.querySelector('[data-marketing-template="body_template_html"]') || {}).value || '');
          var report = validateTemplateTags(subjectValue, bodyValue);
          if (templateValidation) {
            templateValidation.textContent = 'Tags: ' + (report.detected.length ? report.detected.join(', ') : 'none')
              + (report.unknown.length ? (' | Unknown: ' + report.unknown.join(', ')) : '');
          }
          return;
        }
        if (action === 'preview-template') {
          var previewType = ((marketingRoot.querySelector('[data-marketing-template-preview-type]') || {}).value || 'school');
          var previewEntityId = parseInt(((marketingRoot.querySelector('[data-marketing-template-preview-id]') || {}).value || '0'), 10) || 0;
          if (previewType !== 'generic' && previewEntityId < 1) {
            if (templateMsg) {
              templateMsg.textContent = 'Provide sample entity ID.';
            }
            return;
          }
          mFetch('cmn_marketing_preview_campaign', {
            school_id: previewType === 'school' ? previewEntityId : 0,
            subject: ((marketingRoot.querySelector('[data-marketing-template="subject_template"]') || {}).value || ''),
            html_body: ((marketingRoot.querySelector('[data-marketing-template="body_template_html"]') || {}).value || ''),
            text_body: ((marketingRoot.querySelector('[data-marketing-template="body_template_text"]') || {}).value || '')
          }).then(function (data) {
            if (!data || !data.success || !data.data) {
              if (templateMsg) {
                templateMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to preview template.';
              }
              return;
            }
            if (previewSubject) {
              previewSubject.textContent = data.data.subject || '(No subject)';
            }
            if (previewBody) {
              previewBody.innerHTML = data.data.body_html || '';
            }
            if (templateMsg) {
              templateMsg.textContent = 'Template preview rendered.';
            }
          });
          return;
        }
        if (action === 'test-send-template') {
          mFetch('cmn_marketing_test_send', {
            to_email: ((marketingRoot.querySelector('[data-marketing-template-test-email]') || {}).value || ''),
            entity_type: ((marketingRoot.querySelector('[data-marketing-template-preview-type]') || {}).value || 'school') === 'contact' ? 'contact' : 'school',
            entity_id: parseInt(((marketingRoot.querySelector('[data-marketing-template-preview-id]') || {}).value || '0'), 10) || 0,
            subject_template: ((marketingRoot.querySelector('[data-marketing-template="subject_template"]') || {}).value || ''),
            body_template_html: ((marketingRoot.querySelector('[data-marketing-template="body_template_html"]') || {}).value || ''),
            body_template_text: ((marketingRoot.querySelector('[data-marketing-template="body_template_text"]') || {}).value || '')
          }).then(function (data) {
            if (templateMsg) {
              templateMsg.textContent = (data && data.data && data.data.message) ? data.data.message : (data && data.success ? 'Test sent.' : 'Test send failed.');
            }
          });
          return;
        }
        if (action === 'refresh-send-log') {
          refreshSendLog();
          return;
        }
        if (action === 'export-send-log') {
          var f = readSendLogFilters();
          var params = new URLSearchParams();
          params.set('action', 'cmn_marketing_export_send_log');
          params.set('nonce', window.cmnPortal.staffNonce || '');
          params.set('status', f.status || '');
          if (f.campaign_id > 0) {
            params.set('campaign_id', String(f.campaign_id));
          }
          window.location.href = window.cmnPortal.ajaxUrl + '?' + params.toString();
          return;
        }
        if (action === 'resend-failed') {
          var resendFilters = readSendLogFilters();
          if (!resendFilters.campaign_id) {
            if (sendLogMsg) {
              sendLogMsg.textContent = 'Campaign ID is required.';
            }
            return;
          }
          mFetch('cmn_marketing_resend_failed', { campaign_id: resendFilters.campaign_id }).then(function (data) {
            if (sendLogMsg) {
              sendLogMsg.textContent = (data && data.data && data.data.message) ? data.data.message : (data && data.success ? 'Failed sends requeued.' : 'Unable to requeue failed sends.');
            }
            refreshSendLog();
          });
          return;
        }
        if (action === 'refresh-unsubscribes') {
          refreshUnsubscribes();
          return;
        }
        if (action === 'add-unsubscribe') {
          mFetch('cmn_marketing_add_unsubscribe', {
            email: ((marketingRoot.querySelector('[data-marketing-unsub-email]') || {}).value || '')
          }).then(function (data) {
            if (unsubMsg) {
              unsubMsg.textContent = (data && data.data && data.data.message) ? data.data.message : (data && data.success ? 'Email unsubscribed.' : 'Unable to unsubscribe email.');
            }
            refreshUnsubscribes();
          });
          return;
        }
        if (action === 'save-segment') {
          var segmentName = window.prompt('Segment name');
          if (!segmentName) {
            return;
          }
          mFetch('cmn_marketing_save_segment', {
            name: segmentName,
            filter_json: JSON.stringify(readLeadFilters())
          }).then(function (data) {
            if (!data || !data.success || !data.data) {
              if (segmentMsg) {
                segmentMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to save segment.';
              }
              return;
            }
            if (segmentMsg) {
              segmentMsg.textContent = data.data.message || 'Segment saved.';
            }
            renderSegments(data.data.segments || []);
          });
          return;
        }
        if (action === 'save-quick-campaign') {
          var qGet = function (key) {
            return marketingRoot.querySelector('[data-marketing-quick="' + key + '"]');
          };
          mFetch('cmn_marketing_save_quick_campaign', {
            name: (qGet('name') ? qGet('name').value : ''),
            template_id: (qGet('template_id') ? qGet('template_id').value : ''),
            target_type: (qGet('target_type') ? qGet('target_type').value : 'list'),
            list_id: (qGet('list_id') ? qGet('list_id').value : ''),
            segment_id: (qGet('segment_id') ? qGet('segment_id').value : ''),
            filter_json: JSON.stringify(readLeadFilters()),
            cooldown_days: (qGet('cooldown_days') ? qGet('cooldown_days').value : '30'),
            only_new_since_last_run: (qGet('only_new_since_last_run') && qGet('only_new_since_last_run').checked) ? 1 : 0
          }).then(function (data) {
            if (!data || !data.success || !data.data) {
              if (quickMsg) {
                quickMsg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to save quick campaign.';
              }
              return;
            }
            if (quickMsg) {
              quickMsg.textContent = data.data.message || 'Quick campaign saved.';
            }
            var pick = marketingRoot.querySelector('[data-marketing-quick-pick]');
            if (pick) {
              pick.innerHTML = '<option value="">Select saved quick campaign</option>';
              (data.data.quick_campaigns || []).forEach(function (row) {
                var option = document.createElement('option');
                option.value = String(row.id || 0);
                option.textContent = row.name || ('Quick #' + String(row.id || 0));
                pick.appendChild(option);
              });
            }
          });
          return;
        }
        if (action === 'run-quick-campaign') {
          var quickPick = marketingRoot.querySelector('[data-marketing-quick-pick]');
          var quickId = parseInt((quickPick ? quickPick.value : '0'), 10) || 0;
          if (!quickId) {
            if (quickMsg) {
              quickMsg.textContent = 'Select a quick campaign.';
            }
            return;
          }
          mFetch('cmn_marketing_run_quick_campaign', { id: quickId }).then(function (data) {
            if (quickMsg) {
              quickMsg.textContent = (data && data.data && data.data.message) ? data.data.message : (data && data.success ? 'Quick campaign completed.' : 'Unable to run quick campaign.');
            }
            refreshSendLog();
            refreshOverview();
          });
          return;
        }
        if (action === 'save-marketing-settings') {
          mFetch('cmn_marketing_save_settings', {
            max_per_hour: ((marketingRoot.querySelector('[data-marketing-setting="max_per_hour"]') || {}).value || 200),
            sending_paused: (marketingRoot.querySelector('[data-marketing-setting="sending_paused"]') || {}).checked ? 1 : 0
          }).then(function (data) {
            if (unsubMsg) {
              unsubMsg.textContent = (data && data.data && data.data.message) ? data.data.message : (data && data.success ? 'Marketing settings saved.' : 'Unable to save settings.');
            }
            refreshOverview();
          });
          return;
        }
        if (action === 'refresh-overview') {
          refreshOverview();
          return;
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
    refreshTemplates();
    refreshSendLog();
    refreshUnsubscribes();
    refreshSegments();
    refreshOverview();
  }

  document.querySelectorAll('.cmn-system-health-tabs').forEach(function (tabWrap) {
    var tabButtons = Array.prototype.slice.call(tabWrap.querySelectorAll('[data-health-tab]'));
    if (!tabButtons.length) {
      return;
    }
    var scopeRoot = tabWrap.parentElement || document;
    var panels = Array.prototype.slice.call(scopeRoot.querySelectorAll('[data-health-panel]'));
    if (!panels.length) {
      return;
    }
    tabButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = btn.getAttribute('data-health-tab');
        tabButtons.forEach(function (tb) {
          tb.classList.toggle('is-active', tb === btn);
        });
        panels.forEach(function (panel) {
          panel.classList.toggle('is-active', panel.getAttribute('data-health-panel') === target);
        });
      });
    });
  });

  var bookingChatRoots = document.querySelectorAll('[data-booking-chat]');
  if (bookingChatRoots.length && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.bookingChatNonce) {
    bookingChatRoots.forEach(function (chatRoot, bookingChatIndex) {
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
      var defaultStarFields = ['stars_1', 'stars_2', 'stars_3', 'stars_overall'];
      var activeStarFields = defaultStarFields.slice();

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
          meta.textContent = label + ' - ' + (msg.created_at || '');
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
            star.textContent = '\u2605';
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
        activeStarFields.forEach(function (fieldName) {
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
          var ownScoreValue = Number(own.average_rating || own.stars_overall || 0);
          if (isNaN(ownScoreValue)) {
            ownScoreValue = 0;
          }
          var ownScoreLabel = (typeof own.average_rating !== 'undefined')
            ? ('Average: ' + ownScoreValue.toFixed(1) + '/5')
            : ('Overall: ' + Math.round(ownScoreValue) + '/5');
          summary.innerHTML = '<strong>Feedback submitted</strong><span>' + ownScoreLabel + '</span>';
          if (Array.isArray(own.module_scores) && own.module_scores.length) {
            var moduleSummary = own.module_scores.map(function (moduleRow) {
              var moduleLabel = String((moduleRow && moduleRow.label) || 'Module');
              var moduleScore = parseInt((moduleRow && moduleRow.score) || '0', 10);
              if (isNaN(moduleScore)) {
                moduleScore = 0;
              }
              return moduleLabel + ': ' + moduleScore + '/5';
            }).join(' | ');
            var moduleLine = document.createElement('span');
            moduleLine.textContent = moduleSummary;
            summary.appendChild(moduleLine);
          } else {
            var tagsLine = document.createElement('span');
            tagsLine.textContent = 'Tags: ' + (Array.isArray(own.tags) && own.tags.length ? own.tags.join(', ') : 'None');
            summary.appendChild(tagsLine);
          }
          if (own.comment) {
            var ownComment = document.createElement('p');
            ownComment.textContent = own.comment;
            summary.appendChild(ownComment);
          } else if (own.comments) {
            var ownComments = document.createElement('p');
            ownComments.textContent = own.comments;
            summary.appendChild(ownComments);
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
          var otherScoreValue = Number(other.average_rating || other.stars_overall || 0);
          if (isNaN(otherScoreValue)) {
            otherScoreValue = 0;
          }
          var otherScoreLabel = (typeof other.average_rating !== 'undefined')
            ? ('Average: ' + otherScoreValue.toFixed(1) + '/5')
            : ('Overall: ' + Math.round(otherScoreValue) + '/5');
          received.innerHTML = '<strong>Feedback received</strong><span>' + otherScoreLabel + '</span><span>Tags: ' + (Array.isArray(other.tags) && other.tags.length ? other.tags.join(', ') : 'None') + '</span>';
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
        activeStarFields = Array.isArray(formConfig.star_fields) && formConfig.star_fields.length
          ? formConfig.star_fields.map(function (fieldName) {
            return String(fieldName || '').trim();
          }).filter(function (fieldName) {
            return fieldName !== '';
          })
          : defaultStarFields.slice();
        if (!activeStarFields.length) {
          activeStarFields = defaultStarFields.slice();
        }
        feedbackForm.querySelectorAll('[data-booking-feedback-stars]').forEach(function (pickerEl) {
          var fieldName = String(pickerEl.getAttribute('data-booking-feedback-stars') || '');
          var questionRow = pickerEl.closest('.cmn-feedback-question');
          if (questionRow) {
            questionRow.hidden = activeStarFields.indexOf(fieldName) === -1;
          }
        });
        activeStarFields.forEach(function (fieldName) {
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
          var requiredStars = activeStarFields.slice();
          for (var i = 0; i < requiredStars.length; i += 1) {
            var field = requiredStars[i];
            var value = parseInt((feedbackForm.querySelector('input[name="' + field + '"]') || {}).value || '0', 10);
            if (value < 1) {
              if (feedbackMsg) {
                feedbackMsg.textContent = 'Please complete all ratings.';
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

      if (cmnPollManager) {
        cmnPollManager.register({
          key: 'cmn-booking-chat-' + String(threadId) + '-' + String(bookingChatIndex || 0),
          intervalMs: 5000,
          maxIntervalMs: 20000,
          callback: function () {
            return fetchBookingChat();
          },
          visibleOnly: true,
          triggerOnFocus: true,
          triggerOnVisibility: true,
          backoffOnError: true,
          immediate: true,
        });
      } else {
        fetchBookingChat();
        window.setInterval(function () {
          if (document.hidden) {
            return;
          }
          fetchBookingChat();
        }, 5000);
      }
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
    var feedbackTimer = null;

    if (feedback && portalOverlayRoot) {
      feedback.classList.add('cmn-portal-toast');
      feedback.classList.add('cmn-calendar-feedback-toast');
      feedback.setAttribute('data-cmn-toast', 'calendar-feedback');
      portalOverlayRoot.appendChild(feedback);
    }

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

    var setFeedback = function (text, timeout, tone) {
      if (!feedback) {
        return;
      }
      if (feedbackTimer) {
        window.clearTimeout(feedbackTimer);
        feedbackTimer = null;
      }
      var message = String(text || '').trim();
      feedback.classList.remove('is-success', 'is-warning', 'is-error', 'is-visible');
      feedback.textContent = message;
      if (!message) {
        return;
      }
      var normalizedTone = String(tone || '').toLowerCase();
      if (normalizedTone !== 'error' && normalizedTone !== 'warning') {
        normalizedTone = 'success';
      }
      feedback.classList.add('is-' + normalizedTone);
      feedback.classList.add('is-visible');
      if (timeout) {
        feedbackTimer = window.setTimeout(function () {
          feedback.textContent = '';
          feedback.classList.remove('is-success', 'is-warning', 'is-error', 'is-visible');
          feedbackTimer = null;
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
            setFeedback(res && res.data && res.data.message ? res.data.message : 'Unable to save.', 2400, 'error');
            return;
          }
          if (onSuccess) {
            onSuccess(res.data || {});
          }
        })
        .catch(function () {
          setFeedback('Unable to save.', 2400, 'error');
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
      setFeedback('Saving...', 0, 'warning');
      requestCalendar(formData, function (payload) {
        updateSummary(payload.summary);
        setFeedback('Saved', 1400, 'success');
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
            setFeedback('Select a valid start and end date.', 2200, 'warning');
            return;
          }
          var formData = new FormData();
          formData.append('action', 'cmn_bulk_update_calendar');
          formData.append('nonce', window.cmnPortal.calendarBulkNonce || '');
          formData.append('start_date', range.start);
          formData.append('end_date', range.end);
          formData.append('status', status);
          setFeedback('Saving...', 0, 'warning');
          requestCalendar(formData, function (payload) {
            replaceCalendarData(payload.calendar || {});
            updateSummary(payload.summary);
            setFeedback(payload.message || 'Saved', 1500, 'success');
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
              setFeedback('Select a valid date range to clear.', 2200, 'warning');
              return;
            }
            formData.append('start_date', range.start);
            formData.append('end_date', range.end);
          }
          setFeedback('Clearing...', 0, 'warning');
          requestCalendar(formData, function (payload) {
            replaceCalendarData(payload.calendar || {});
            updateSummary(payload.summary);
            setFeedback(payload.message || 'Cleared', 1500, 'success');
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
    var notifyToggleInputs = candidateSettingsRoot.querySelectorAll('[data-settings-notify]');
    var notifyTimeInputs = candidateSettingsRoot.querySelectorAll('[data-settings-notify-time]');
    var saveButtons = candidateSettingsRoot.querySelectorAll('[data-settings-save]');
    var settingsMessageEls = candidateSettingsRoot.querySelectorAll('[data-settings-message]');
    var settingsTabButtons = candidateSettingsRoot.querySelectorAll('[data-candidate-settings-tab]');
    var settingsPanels = candidateSettingsRoot.querySelectorAll('[data-candidate-settings-panel]');
    var deleteEmailInput = candidateSettingsRoot.querySelector('[data-delete-confirm-email]');
    var deleteRequestBtn = candidateSettingsRoot.querySelector('[data-delete-request-btn]');
    var deleteRequestMsg = candidateSettingsRoot.querySelector('[data-delete-request-msg]');
    var activeSettingsTab = candidateSettingsRoot.getAttribute('data-candidate-settings-active-tab') || 'appearance';
    var getActiveSettingsPanel = function () {
      return candidateSettingsRoot.querySelector('.cmn-candidate-settings-panel.is-active');
    };
    var getActiveSettingsMessageEl = function () {
      var panel = getActiveSettingsPanel();
      return panel ? panel.querySelector('[data-settings-message]') : candidateSettingsRoot.querySelector('[data-settings-message]');
    };
    var setSettingsMessage = function (message, silent, clearAfterMs) {
      if (silent) {
        return;
      }
      settingsMessageEls.forEach(function (el) {
        el.textContent = '';
      });
      var target = getActiveSettingsMessageEl();
      if (!target) {
        return;
      }
      var text = String(message || '');
      target.textContent = text;
      if (clearAfterMs && clearAfterMs > 0) {
        window.setTimeout(function () {
          if (target.textContent === text) {
            target.textContent = '';
          }
        }, clearAfterMs);
      }
    };
    var activateSettingsTab = function (tabKey, updateUrl) {
      var nextTab = String(tabKey || '').trim();
      if (!nextTab) {
        return;
      }
      var matched = false;
      settingsTabButtons.forEach(function (btn) {
        var key = btn.getAttribute('data-candidate-settings-tab') || '';
        var isActive = key === nextTab;
        btn.classList.toggle('is-active', isActive);
        btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        if (isActive) {
          matched = true;
        }
      });
      settingsPanels.forEach(function (panel) {
        var key = panel.getAttribute('data-candidate-settings-panel') || '';
        var isActive = key === nextTab;
        panel.classList.toggle('is-active', isActive);
        panel.hidden = !isActive;
      });
      if (!matched) {
        return;
      }
      activeSettingsTab = nextTab;
      candidateSettingsRoot.setAttribute('data-candidate-settings-active-tab', nextTab);
      if (!updateUrl || !window.history || typeof window.history.replaceState !== 'function') {
        return;
      }
      try {
        var nextUrl = new URL(window.location.href);
        nextUrl.searchParams.set('cmn_candidate_settings_tab', nextTab);
        window.history.replaceState(window.history.state || {}, '', nextUrl.toString());
      } catch (e) {}
    };
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

    var collectNotificationPrefs = function () {
      var prefs = {};
      notifyToggleInputs.forEach(function (input) {
        var key = input.getAttribute('data-settings-notify');
        if (!key) {
          return;
        }
        prefs[key] = input.checked ? '1' : '0';
      });
      notifyTimeInputs.forEach(function (input) {
        var key = input.getAttribute('data-settings-notify-time');
        if (!key) {
          return;
        }
        prefs[key] = String(input.value || '').trim();
      });
      return prefs;
    };

    var saveSettings = function (silent) {
      var payload = {
        theme: themeSelect ? themeSelect.value : 'default',
        preferences: JSON.stringify(collectPrefs()),
        notification_preferences: JSON.stringify(collectNotificationPrefs()),
      };
      var fd = new FormData();
      fd.append('action', 'cmn_save_candidate_settings');
      fd.append('nonce', window.cmnPortal.candidateSettingsNonce);
      fd.append('theme', payload.theme);
      var prefs = collectPrefs();
      Object.keys(prefs).forEach(function (key) {
        fd.append('preferences[' + key + ']', prefs[key]);
      });
      var notificationPrefs = collectNotificationPrefs();
      Object.keys(notificationPrefs).forEach(function (key) {
        fd.append('notification_preferences[' + key + ']', notificationPrefs[key]);
      });
      setSettingsMessage('Saving...', silent);
      return fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd,
      }).then(function (response) { return response.json(); }).then(function (data) {
        if (!data || !data.success) {
          setSettingsMessage((data && data.data && data.data.message) ? data.data.message : 'Unable to save settings.', silent);
          return;
        }
        cmnApplyThemeClass((data.data && data.data.theme) ? data.data.theme : payload.theme);
        setSettingsMessage('Settings saved.', silent, 1500);
      }).catch(function () {
        setSettingsMessage('Unable to save settings.', silent);
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
      var notificationPreferences = settings.notification_preferences || {};
      prefInputs.forEach(function (input) {
        var key = input.getAttribute('data-settings-pref');
        if (!key) {
          return;
        }
        input.checked = String(preferences[key] || 0) === '1';
      });
      notifyToggleInputs.forEach(function (input) {
        var key = input.getAttribute('data-settings-notify');
        if (!key) {
          return;
        }
        input.checked = String(notificationPreferences[key] || 0) === '1';
      });
      notifyTimeInputs.forEach(function (input) {
        var key = input.getAttribute('data-settings-notify-time');
        if (!key) {
          return;
        }
        input.value = String(notificationPreferences[key] || input.value || '');
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

    activateSettingsTab(activeSettingsTab, false);
    settingsTabButtons.forEach(function (tabBtn) {
      tabBtn.addEventListener('click', function () {
        var tabKey = tabBtn.getAttribute('data-candidate-settings-tab') || '';
        activateSettingsTab(tabKey, true);
      });
    });

    if (themeSelect) {
      themeSelect.addEventListener('change', function () {
        cmnApplyThemeClass(themeSelect.value);
        saveSettings(true);
      });
    }

    saveButtons.forEach(function (saveBtn) {
      saveBtn.addEventListener('click', function () {
        saveSettings(false);
      });
    });

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

  var notificationPreferenceRoots = document.querySelectorAll('[data-notification-preferences]');
  if (notificationPreferenceRoots.length && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.notificationPreferencesNonce) {
    notificationPreferenceRoots.forEach(function (root) {
      var saveBtn = root.querySelector('[data-notify-settings-save]');
      var msgEl = root.querySelector('[data-notify-settings-message]');
      var toggleInputs = root.querySelectorAll('[data-notify-pref]');
      var timeInputs = root.querySelectorAll('[data-notify-time]');
      if (!saveBtn || !toggleInputs.length) {
        return;
      }

      var collect = function () {
        var payload = {};
        toggleInputs.forEach(function (input) {
          var key = input.getAttribute('data-notify-pref');
          if (!key) {
            return;
          }
          payload[key] = input.checked ? '1' : '0';
        });
        timeInputs.forEach(function (input) {
          var key = input.getAttribute('data-notify-time');
          if (!key) {
            return;
          }
          payload[key] = String(input.value || '').trim();
        });
        return payload;
      };

      var applyPrefs = function (prefs) {
        var data = prefs || {};
        toggleInputs.forEach(function (input) {
          var key = input.getAttribute('data-notify-pref');
          if (!key) {
            return;
          }
          input.checked = String(data[key] || 0) === '1';
        });
        timeInputs.forEach(function (input) {
          var key = input.getAttribute('data-notify-time');
          if (!key) {
            return;
          }
          var nextValue = String(data[key] || '').trim();
          if (nextValue) {
            input.value = nextValue;
          }
        });
      };

      var fetchPrefs = function () {
        var fd = new FormData();
        fd.append('action', 'cmn_get_notification_preferences');
        fd.append('nonce', window.cmnPortal.notificationPreferencesNonce);
        return fetch(window.cmnPortal.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
        }).then(function (response) { return response.json(); });
      };

      var savePrefs = function () {
        var payload = collect();
        var fd = new FormData();
        fd.append('action', 'cmn_save_notification_preferences');
        fd.append('nonce', window.cmnPortal.notificationPreferencesNonce);
        Object.keys(payload).forEach(function (key) {
          fd.append('notification_preferences[' + key + ']', payload[key]);
        });
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
              msgEl.textContent = (data && data.data && data.data.message) ? data.data.message : 'Unable to save notification preferences.';
            }
            return;
          }
          applyPrefs((data.data && data.data.preferences) || payload);
          if (msgEl) {
            msgEl.textContent = 'Saved.';
            setTimeout(function () { msgEl.textContent = ''; }, 1800);
          }
        }).catch(function () {
          if (msgEl) {
            msgEl.textContent = 'Unable to save notification preferences.';
          }
        });
      };

      fetchPrefs().then(function (data) {
        if (!data || !data.success || !data.data) {
          return;
        }
        applyPrefs(data.data.preferences || {});
      });

      saveBtn.addEventListener('click', function () {
        savePrefs();
      });
    });
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
          var targetUrl = data.data.converter_url || data.data.portal_url || '';
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
    var converterResizeTimer = null;
    var getConverterMinHeight = function () {
      return window.matchMedia('(max-width: 1100px)').matches ? 560 : 720;
    };
    var applyConverterDocumentScroll = function (doc) {
      if (!doc) {
        return;
      }
      if (doc.documentElement) {
        doc.documentElement.style.height = 'auto';
        doc.documentElement.style.overflowY = 'auto';
      }
      if (doc.body) {
        doc.body.style.height = 'auto';
        doc.body.style.overflowY = 'auto';
      }
    };
    var resizeConverterFrame = function () {
      var minHeight = getConverterMinHeight();
      try {
        var doc = converterFrame.contentDocument || (converterFrame.contentWindow && converterFrame.contentWindow.document);
        if (!doc) {
          converterFrame.style.minHeight = minHeight + 'px';
          return;
        }
        applyConverterDocumentScroll(doc);
        var body = doc.body;
        var html = doc.documentElement;
        var measured = Math.max(
          body ? body.scrollHeight : 0,
          body ? body.offsetHeight : 0,
          html ? html.clientHeight : 0,
          html ? html.scrollHeight : 0,
          html ? html.offsetHeight : 0
        );
        if (measured > 0) {
          converterFrame.style.height = Math.max(minHeight, measured + 24) + 'px';
        } else {
          converterFrame.style.minHeight = minHeight + 'px';
        }
      } catch (_error) {
        converterFrame.style.minHeight = minHeight + 'px';
      }
    };
    var scheduleConverterResize = function () {
      if (converterResizeTimer) {
        window.clearTimeout(converterResizeTimer);
      }
      converterResizeTimer = window.setTimeout(resizeConverterFrame, 80);
    };
    var isConverterErrorDocument = function (doc) {
      if (!doc || !doc.body) {
        return false;
      }
      var titleText = (doc.title || '').toLowerCase();
      var bodyText = (doc.body.textContent || '').toLowerCase();
      return (
        titleText.indexOf('404') !== -1 ||
        bodyText.indexOf("doesn't seem to exist") !== -1 ||
        bodyText.indexOf('link pointing here was faulty') !== -1
      );
    };
    var showConverterFallback = function (force) {
      if (converterLoaded && !force) {
        return;
      }
      converterFallback.hidden = false;
    };
    converterFrame.addEventListener('load', function () {
      converterLoaded = true;
      converterFallback.hidden = true;
      resizeConverterFrame();
      window.setTimeout(resizeConverterFrame, 200);
      window.setTimeout(resizeConverterFrame, 900);
      window.setTimeout(resizeConverterFrame, 1800);
      try {
        var loadedDoc = converterFrame.contentDocument || (converterFrame.contentWindow && converterFrame.contentWindow.document);
        if (isConverterErrorDocument(loadedDoc)) {
          showConverterFallback(true);
        }
      } catch (_error) {}
    });
    converterFrame.addEventListener('error', function () {
      showConverterFallback(true);
    });
    window.addEventListener('resize', scheduleConverterResize);
    window.setTimeout(function () {
      showConverterFallback(false);
    }, 9000);
  }

  var candidateDocsRoot = document.querySelector('[data-candidate-docs]');
  var candidateProfileRoot = document.querySelector('[data-profile-root]');
  if (candidateProfileRoot && window.cmnPortal && window.cmnPortal.ajaxUrl && window.cmnPortal.candidateProfileNonce) {
    var profileMain = candidateProfileRoot.closest('.cmn-candidate-main');
    var profileGlobalEditButton = candidateProfileRoot.querySelector('[data-profile-global-edit]');
    var profileGlobalActions = document.querySelector('[data-profile-global-actions]');
    var profileGlobalSave = document.querySelector('[data-profile-global-save]');
    var profileGlobalCancel = document.querySelector('[data-profile-global-cancel]');
    var profileGlobalMsg = document.querySelector('[data-profile-global-msg]');
    var profileForms = Array.prototype.slice.call(document.querySelectorAll('[data-profile-form]'));
    var profileViews = Array.prototype.slice.call(document.querySelectorAll('[data-profile-view]'));
    var profileReadonlyCards = Array.prototype.slice.call(document.querySelectorAll('[data-profile-readonly-only]'));
    var profileEditOnlyCards = Array.prototype.slice.call(document.querySelectorAll('[data-profile-edit-only]'));
    var profileIsEditing = false;
    var profileSaveInFlight = false;
    var profileEditSnapshot = '';

    var setProfileMessage = function (text) {
      var msg = document.querySelector('[data-doc-message]');
      if (msg) {
        msg.textContent = text || '';
      }
    };

    var serializeProfileForm = function (form) {
      if (!form) {
        return '';
      }
      return Array.prototype.map.call(form.elements || [], function (el) {
        if (!el || !el.name) {
          return '';
        }
        if (el.type === 'checkbox' || el.type === 'radio') {
          return el.name + '=' + (el.checked ? '1' : '0') + ':' + (el.value || '');
        }
        return el.name + '=' + (el.value || '');
      }).join('|');
    };

    var serializeAllProfileForms = function () {
      return profileForms.map(function (form) {
        return serializeProfileForm(form);
      }).join('||');
    };

    var commitProfileFormDefaults = function (form) {
      if (!form) {
        return;
      }
      Array.prototype.forEach.call(form.elements || [], function (el) {
        if (!el || !el.name) {
          return;
        }
        if (el.type === 'checkbox' || el.type === 'radio') {
          el.defaultChecked = !!el.checked;
        } else {
          el.defaultValue = el.value || '';
        }
      });
    };

    var setProfileGlobalMessage = function (text, isError) {
      if (!profileGlobalMsg) {
        return;
      }
      profileGlobalMsg.textContent = text || '';
      profileGlobalMsg.classList.toggle('is-error', !!isError);
    };

    var refreshProfileGlobalActions = function (message, forceError) {
      var isDirty = profileIsEditing && serializeAllProfileForms() !== profileEditSnapshot;
      if (profileGlobalSave) {
        profileGlobalSave.disabled = !profileIsEditing || !isDirty || profileSaveInFlight;
      }
      if (profileGlobalCancel) {
        profileGlobalCancel.disabled = profileSaveInFlight;
      }
      if (typeof message === 'string') {
        setProfileGlobalMessage(message, !!forceError);
      } else if (!profileIsEditing) {
        setProfileGlobalMessage('', false);
      }
    };

    var toggleProfileEditMode = function (editing, preserveValues) {
      profileIsEditing = !!editing;
      if (!profileIsEditing && !preserveValues) {
        profileForms.forEach(function (form) {
          if (form && typeof form.reset === 'function') {
            form.reset();
          }
        });
      }
      profileViews.forEach(function (view) {
        view.hidden = profileIsEditing;
      });
      profileForms.forEach(function (form) {
        form.hidden = !profileIsEditing;
      });
      profileReadonlyCards.forEach(function (card) {
        card.hidden = profileIsEditing;
      });
      profileEditOnlyCards.forEach(function (card) {
        card.hidden = !profileIsEditing;
      });
      if (profileGlobalEditButton) {
        profileGlobalEditButton.hidden = profileIsEditing;
      }
      if (profileGlobalActions) {
        profileGlobalActions.hidden = !profileIsEditing;
      }
      if (profileMain) {
        profileMain.classList.toggle('cmn-profile-is-editing', profileIsEditing);
      }
      if (profileIsEditing) {
        profileEditSnapshot = serializeAllProfileForms();
      } else {
        profileEditSnapshot = '';
      }
      refreshProfileGlobalActions();
    };

    var saveProfile = function () {
      if (!profileIsEditing || profileSaveInFlight) {
        return;
      }
      profileSaveInFlight = true;
      refreshProfileGlobalActions('Saving...', false);

      var fd = new FormData();
      fd.append('action', 'cmn_candidate_update_profile');
      fd.append('nonce', window.cmnPortal.candidateProfileNonce);

      var getFieldInput = function (field) {
        return document.querySelector('[data-profile-form] [name="' + field + '"]');
      };

      ['email', 'first_name', 'last_name', 'phone', 'nationality', 'role_type', 'roles_other', 'teacher_subject_specialism', 'travel_radius', 'location', 'driving_licence', 'car_owner', 'qts_status', 'no_dbs', 'dbs_update_service', 'house_number', 'address_line1', 'address_line2', 'address_line3', 'town', 'county', 'postcode', 'notes'].forEach(function (field) {
        var input = getFieldInput(field);
        if (input) {
          fd.append(field, String(input.value || '').trim());
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

      fetch(window.cmnPortal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      }).then(function (response) {
        return response.json();
      }).then(function (data) {
        if (!data || !data.success) {
          var saveErrorMessage = data && data.data && data.data.message ? data.data.message : 'Unable to save profile.';
          setProfileMessage(saveErrorMessage);
          refreshProfileGlobalActions(saveErrorMessage, true);
          return;
        }

        var profile = data.data && data.data.profile ? data.data.profile : {};
        var fullName = (profile.full_name || '').trim();

        var fullNameEl = document.querySelector('[data-profile-full-name]');
        var emailEl = document.querySelector('[data-profile-email]');
        var phoneEl = document.querySelector('[data-profile-phone]');
        var nationalityEl = document.querySelector('[data-profile-nationality]');
        var roleEl = document.querySelector('[data-profile-role]');
        var rolesEl = document.querySelector('[data-profile-roles]');
        var rolesOtherEl = document.querySelector('[data-profile-roles-other]');
        var teacherSubjectEl = document.querySelector('[data-profile-teacher-subject]');
        var travelEl = document.querySelector('[data-profile-travel]');
        var locationEl = document.querySelector('[data-profile-location]');
        var drivingEl = document.querySelector('[data-profile-driving]');
        var carEl = document.querySelector('[data-profile-car]');
        var qtsEl = document.querySelector('[data-profile-qts]');
        var hasDbsEl = document.querySelector('[data-profile-has-dbs]');
        var dbsUpdateEl = document.querySelector('[data-profile-dbs-update]');
        var daysEl = document.querySelector('[data-profile-days]');
        var addressEl = document.querySelector('[data-profile-address]');
        var houseNumberEl = document.querySelector('[data-profile-house-number]');
        var line1El = document.querySelector('[data-profile-address-line1]');
        var line2El = document.querySelector('[data-profile-address-line2]');
        var line3El = document.querySelector('[data-profile-address-line3]');
        var townEl = document.querySelector('[data-profile-town]');
        var countyEl = document.querySelector('[data-profile-county]');
        var postcodeEl = document.querySelector('[data-profile-postcode]');
        var notesEl = document.querySelector('[data-profile-notes]');

        if (fullNameEl) {
          fullNameEl.textContent = fullName || 'Candidate';
        }
        if (emailEl) {
          emailEl.textContent = profile.email || 'Not set';
        }
        if (phoneEl) {
          phoneEl.textContent = profile.phone || 'Not set';
        }
        if (nationalityEl) {
          nationalityEl.textContent = profile.nationality || 'Not set';
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
        if (teacherSubjectEl) {
          teacherSubjectEl.textContent = profile.teacher_subject_specialism || 'Not set';
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
        if (qtsEl) {
          qtsEl.textContent = profile.qts_status_label || 'Not set';
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
        if (houseNumberEl) {
          houseNumberEl.textContent = profile.house_number || 'Not set';
        }
        if (line1El) {
          line1El.textContent = profile.address_line1 || 'Not set';
        }
        if (line2El) {
          line2El.textContent = profile.address_line2 || 'Not set';
        }
        if (line3El) {
          line3El.textContent = profile.address_line3 || 'Not set';
        }
        if (townEl) {
          townEl.textContent = profile.town || 'Not set';
        }
        if (countyEl) {
          countyEl.textContent = profile.county || 'Not set';
        }
        if (postcodeEl) {
          postcodeEl.textContent = profile.postcode || 'Not set';
        }
        if (notesEl) {
          notesEl.textContent = profile.notes || 'Not set';
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

        profileForms.forEach(function (form) {
          commitProfileFormDefaults(form);
        });
        toggleProfileEditMode(false, true);
        refreshProfileGlobalActions('Saved.', false);
        setProfileMessage('Profile updated.');
      }).catch(function () {
        setProfileMessage('Unable to save profile.');
        refreshProfileGlobalActions('Unable to save profile.', true);
      }).finally(function () {
        profileSaveInFlight = false;
        refreshProfileGlobalActions();
      });
    };

    if (profileGlobalEditButton) {
      profileGlobalEditButton.addEventListener('click', function () {
        toggleProfileEditMode(true);
      });
    }
    if (profileGlobalCancel) {
      profileGlobalCancel.addEventListener('click', function () {
        toggleProfileEditMode(false, false);
      });
    }
    if (profileGlobalSave) {
      profileGlobalSave.addEventListener('click', function () {
        saveProfile();
      });
    }

    profileForms.forEach(function (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        saveProfile();
      });
      ['change', 'input'].forEach(function (evtName) {
        form.addEventListener(evtName, function () {
          refreshProfileGlobalActions();
        });
      });
    });

    toggleProfileEditMode(false, true);
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
      if (summaryFields.lastRun) summaryFields.lastRun.textContent = run.started_at || '-';
      if (summaryFields.lastDuration) summaryFields.lastDuration.textContent = run.duration_ms ? (Math.round((run.duration_ms / 1000) * 100) / 100) + 's' : '-';
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
        var entityId = issue.entity_id ? String(issue.entity_id) : '-';
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
          '<td>' + (run.duration_ms ? (Math.round((run.duration_ms / 1000) * 100) / 100) + 's' : '-') + '</td>',
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
          '<td>' + (row.entity_id ? String(row.entity_id) : '-') + '</td>',
          '<td>' + (row.issue_id ? String(row.issue_id) : '-') + '</td>',
          '<td>' + (row.performed_by ? String(row.performed_by) : '-') + '</td>',
          '<td>' + (parseInt(row.dry_run || 0, 10) === 1 ? 'Yes' : 'No') + '</td>',
          '<td>' + (row.status || '') + '</td>',
          '<td>' + (row.notes || '-') + '</td>',
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
          modalTitle.textContent = (issue.issue_code || 'Issue') + ' - ' + (issue.severity || '').toUpperCase();
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
      { key: 'support', nav: true, title: 'Support', text: 'Open a ticket whenever you need help.' },
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
