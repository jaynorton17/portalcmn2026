# Phase 0 Baseline Audit

- Source: `covermenowone-one.php`
- Generated at: `2026-03-02T08:01:25Z`
- Plugin version header: `0.1.25` (`covermenowone-one.php:5`)
- Schema constants:
  - `SCHEMA_BASE_VERSION=38` (`covermenowone-one.php:607`)
  - `SCHEMA_VERSION=75` (`covermenowone-one.php:608`)

## Registered Entrypoints

### wp_ajax (138)
- `wp_ajax_cmn_mark_available` -> `handle_mark_available` (`covermenowone-one.php:851`, `covermenowone-one.php:92047`)
- `wp_ajax_cmn_mark_unavailable_morning` -> `handle_mark_unavailable_morning` (`covermenowone-one.php:852`, `covermenowone-one.php:92153`)
- `wp_ajax_cmn_update_calendar_day` -> `handle_update_calendar_day` (`covermenowone-one.php:853`, `covermenowone-one.php:92196`)
- `wp_ajax_cmn_get_calendar_availability` -> `handle_get_calendar_availability` (`covermenowone-one.php:854`, `covermenowone-one.php:92254`)
- `wp_ajax_cmn_bulk_import_schools_run` -> `handle_bulk_import_schools_run` (`covermenowone-one.php:855`, `covermenowone-one.php:101333`)
- `wp_ajax_cmn_bulk_update_calendar` -> `handle_bulk_update_calendar` (`covermenowone-one.php:856`, `covermenowone-one.php:92274`)
- `wp_ajax_cmn_clear_calendar` -> `handle_clear_calendar` (`covermenowone-one.php:857`, `covermenowone-one.php:92349`)
- `wp_ajax_cmn_dismiss_candidate_tour` -> `handle_dismiss_candidate_tour` (`covermenowone-one.php:858`, `covermenowone-one.php:92391`)
- `wp_ajax_cmn_get_candidate_settings` -> `handle_get_candidate_settings` (`covermenowone-one.php:859`, `covermenowone-one.php:92402`)
- `wp_ajax_cmn_save_candidate_settings` -> `handle_save_candidate_settings` (`covermenowone-one.php:860`, `covermenowone-one.php:92429`)
- `wp_ajax_cmn_get_notification_preferences` -> `handle_get_notification_preferences` (`covermenowone-one.php:861`, `covermenowone-one.php:92484`)
- `wp_ajax_cmn_save_notification_preferences` -> `handle_save_notification_preferences` (`covermenowone-one.php:862`, `covermenowone-one.php:92500`)
- `wp_ajax_cmn_get_theme_settings` -> `handle_get_theme_settings` (`covermenowone-one.php:863`, `covermenowone-one.php:92536`)
- `wp_ajax_cmn_save_theme_settings` -> `handle_save_theme_settings` (`covermenowone-one.php:864`, `covermenowone-one.php:92550`)
- `wp_ajax_cmn_candidate_request_delete_account` -> `handle_candidate_request_delete_account` (`covermenowone-one.php:865`, `covermenowone-one.php:92567`)
- `wp_ajax_cmn_admin_delete_candidate_account` -> `handle_admin_delete_candidate_account` (`covermenowone-one.php:866`, `covermenowone-one.php:92618`)
- `wp_ajax_cmn_request_candidate` -> `handle_request_candidate` (`covermenowone-one.php:867`, `covermenowone-one.php:95535`)
- `wp_ajax_cmn_school_live_match_action` -> `handle_school_live_match_action` (`covermenowone-one.php:868`, `covermenowone-one.php:95362`)
- `wp_ajax_cmn_school_live_match_presence` -> `handle_school_live_match_presence` (`covermenowone-one.php:869`, `covermenowone-one.php:95458`)
- `wp_ajax_cmn_mark_notifications_read` -> `handle_mark_notifications_read` (`covermenowone-one.php:870`, `covermenowone-one.php:23546`)
- `wp_ajax_cmn_notifications_mark_all_read` -> `handle_notifications_mark_all_read` (`covermenowone-one.php:871`, `covermenowone-one.php:23558`)
- `wp_ajax_cmn_notifications_clear_all` -> `handle_notifications_clear_all` (`covermenowone-one.php:872`, `covermenowone-one.php:23570`)
- `wp_ajax_cmn_notifications_mark_read` -> `handle_notifications_mark_read` (`covermenowone-one.php:873`, `covermenowone-one.php:23582`)
- `wp_ajax_cmn_school_contact_search` -> `handle_school_contact_search_ajax` (`covermenowone-one.php:874`, `covermenowone-one.php:24609`)
- `wp_ajax_cmn_notifications_mark_selected_read` -> `handle_notifications_mark_selected_read` (`covermenowone-one.php:875`, `covermenowone-one.php:23598`)
- `wp_ajax_cmn_notifications_delete_selected` -> `handle_notifications_delete_selected` (`covermenowone-one.php:876`, `covermenowone-one.php:23614`)
- `wp_ajax_cmn_notifications_poll` -> `handle_notifications_poll` (`covermenowone-one.php:877`, `covermenowone-one.php:23630`)
- `wp_ajax_cmn_portal_heartbeat` -> `handle_portal_heartbeat` (`covermenowone-one.php:878`, `covermenowone-one.php:24203`)
- `wp_ajax_cmn_thread_get` -> `handle_thread_get` (`covermenowone-one.php:879`, `covermenowone-one.php:87591`)
- `wp_ajax_cmn_thread_post_message` -> `handle_thread_post_message` (`covermenowone-one.php:881`, `covermenowone-one.php:87620`)
- `wp_ajax_cmn_thread_mark_read` -> `handle_thread_mark_read` (`covermenowone-one.php:883`, `covermenowone-one.php:87713`)
- `wp_ajax_cmn_thread_set_status` -> `handle_thread_set_status` (`covermenowone-one.php:885`, `covermenowone-one.php:87741`)
- `wp_ajax_cmn_thread_download_attachment` -> `handle_thread_download_attachment` (`covermenowone-one.php:886`, `covermenowone-one.php:87778`)
- `wp_ajax_cmn_run_system_health` -> `handle_run_system_health` (`covermenowone-one.php:888`, `covermenowone-one.php:24783`)
- `wp_ajax_cmn_get_system_health_runs` -> `handle_get_system_health_runs` (`covermenowone-one.php:889`, `covermenowone-one.php:24812`)
- `wp_ajax_cmn_get_system_health_issues` -> `handle_get_system_health_issues` (`covermenowone-one.php:890`, `covermenowone-one.php:24835`)
- `wp_ajax_cmn_get_system_health_issue_detail` -> `handle_get_system_health_issue_detail` (`covermenowone-one.php:891`, `covermenowone-one.php:24977`)
- `wp_ajax_cmn_update_system_health_issue` -> `handle_update_system_health_issue` (`covermenowone-one.php:892`, `covermenowone-one.php:25007`)
- `wp_ajax_cmn_system_health_preview_fix` -> `handle_system_health_preview_fix` (`covermenowone-one.php:893`, `covermenowone-one.php:25038`)
- `wp_ajax_cmn_system_health_apply_fix` -> `handle_system_health_apply_fix` (`covermenowone-one.php:894`, `covermenowone-one.php:25065`)
- `wp_ajax_cmn_system_health_apply_all_safe_fixes` -> `handle_system_health_apply_all_safe_fixes` (`covermenowone-one.php:895`, `covermenowone-one.php:25727`)
- `wp_ajax_cmn_system_health_preview` -> `handle_system_health_preview` (`covermenowone-one.php:896`, `covermenowone-one.php:25791`)
- `wp_ajax_cmn_system_health_apply` -> `handle_system_health_apply` (`covermenowone-one.php:897`, `covermenowone-one.php:25827`)
- `wp_ajax_cmn_system_health_export_csv` -> `handle_system_health_export_csv` (`covermenowone-one.php:898`, `covermenowone-one.php:25868`)
- `wp_ajax_cmn_get_system_health_fixes` -> `handle_get_system_health_fixes` (`covermenowone-one.php:899`, `covermenowone-one.php:25102`)
- `wp_ajax_cmn_save_staff_nav_state` -> `handle_save_staff_nav_state` (`covermenowone-one.php:900`, `covermenowone-one.php:24664`)
- `wp_ajax_cmn_save_staff_nav_order` -> `handle_save_staff_nav_order` (`covermenowone-one.php:901`, `covermenowone-one.php:24691`)
- `wp_ajax_cmn_touch_staff_presence` -> `handle_touch_staff_presence` (`covermenowone-one.php:902`, `covermenowone-one.php:89438`)
- `wp_ajax_cmn_touch_candidate_presence` -> `handle_touch_candidate_presence` (`covermenowone-one.php:903`, `covermenowone-one.php:89535`)
- `wp_ajax_cmn_send_test_emails` -> `handle_send_test_emails` (`covermenowone-one.php:904`, `covermenowone-one.php:26216`)
- `wp_ajax_cmn_booking_chat_fetch` -> `handle_booking_chat_fetch` (`covermenowone-one.php:912`, `covermenowone-one.php:96804`)
- `wp_ajax_cmn_staff_lounge_fetch` -> `handle_staff_lounge_fetch` (`covermenowone-one.php:913`, `covermenowone-one.php:102532`)
- `wp_ajax_cmn_staff_lounge_post` -> `handle_staff_lounge_post` (`covermenowone-one.php:914`, `covermenowone-one.php:102548`)
- `wp_ajax_cmn_booking_feedback_fetch` -> `handle_booking_feedback_fetch` (`covermenowone-one.php:915`, `covermenowone-one.php:96892`)
- `wp_ajax_cmn_booking_feedback_submit` -> `handle_booking_feedback_submit` (`covermenowone-one.php:916`, `covermenowone-one.php:96918`)
- `wp_ajax_cmn_email_template_preview` -> `handle_email_template_preview` (`covermenowone-one.php:917`, `covermenowone-one.php:42757`)
- `wp_ajax_cmn_email_template_send_test` -> `handle_email_template_send_test` (`covermenowone-one.php:918`, `covermenowone-one.php:42787`)
- `wp_ajax_cmn_add_staff_user` -> `handle_add_staff_user_ajax` (`covermenowone-one.php:1011`, `covermenowone-one.php:101814`)
- `wp_ajax_cmn_update_staff_user` -> `handle_update_staff_user_ajax` (`covermenowone-one.php:1012`, `covermenowone-one.php:101878`)
- `wp_ajax_cmn_send_staff_reset_password` -> `handle_send_staff_reset_password_ajax` (`covermenowone-one.php:1013`, `covermenowone-one.php:101922`)
- `wp_ajax_cmn_toggle_staff_deactivated` -> `handle_toggle_staff_deactivated_ajax` (`covermenowone-one.php:1014`, `covermenowone-one.php:101944`)
- `wp_ajax_cmn_support_create_ticket` -> `handle_support_create_ticket` (`covermenowone-one.php:1015`, `covermenowone-one.php:102452`)
- `wp_ajax_cmn_support_list_tickets` -> `handle_support_list_tickets` (`covermenowone-one.php:1016`, `covermenowone-one.php:102600`)
- `wp_ajax_support_list_tickets_unfiltered_admin` -> `handle_support_list_tickets_unfiltered_admin` (`covermenowone-one.php:1017`, `covermenowone-one.php:102901`)
- `wp_ajax_cmn_support_list_tickets_unfiltered_admin` -> `handle_support_list_tickets_unfiltered_admin` (`covermenowone-one.php:1018`, `covermenowone-one.php:102901`)
- `wp_ajax_cmn_support_get_ticket` -> `handle_support_get_ticket` (`covermenowone-one.php:1019`, `covermenowone-one.php:102934`)
- `wp_ajax_cmn_support_post_message` -> `handle_support_post_message` (`covermenowone-one.php:1020`, `covermenowone-one.php:103041`)
- `wp_ajax_cmn_support_close_ticket` -> `handle_support_close_ticket` (`covermenowone-one.php:1021`, `covermenowone-one.php:103152`)
- `wp_ajax_cmn_support_request_feedback` -> `handle_support_request_feedback` (`covermenowone-one.php:1022`, `covermenowone-one.php:103287`)
- `wp_ajax_cmn_support_submit_feedback` -> `handle_support_submit_feedback` (`covermenowone-one.php:1023`, `covermenowone-one.php:103444`)
- `wp_ajax_cmn_support_save_transcript` -> `handle_support_save_transcript` (`covermenowone-one.php:1024`, `covermenowone-one.php:103519`)
- `wp_ajax_cmn_support_email_transcript` -> `handle_support_email_transcript` (`covermenowone-one.php:1025`, `covermenowone-one.php:103566`)
- `wp_ajax_cmn_school_open_account_manager_chat` -> `handle_school_open_account_manager_chat` (`covermenowone-one.php:1026`, `covermenowone-one.php:102380`)
- `wp_ajax_cmn_school_account_manager_chat_status` -> `handle_school_account_manager_chat_status` (`covermenowone-one.php:1027`, `covermenowone-one.php:102430`)
- `wp_ajax_cmn_candidate_submit_payroll_query` -> `handle_candidate_submit_payroll_query` (`covermenowone-one.php:1028`, `covermenowone-one.php:102092`)
- `wp_ajax_cmn_livechat_start` -> `handle_livechat_start` (`covermenowone-one.php:1029`, `covermenowone-one.php:16651`)
- `wp_ajax_cmn_livechat_send` -> `handle_livechat_send` (`covermenowone-one.php:1031`, `covermenowone-one.php:16848`)
- `wp_ajax_cmn_livechat_poll` -> `handle_livechat_poll` (`covermenowone-one.php:1033`, `covermenowone-one.php:16942`)
- `wp_ajax_cmn_livechat_feedback_submit` -> `handle_livechat_feedback_submit` (`covermenowone-one.php:1035`, `covermenowone-one.php:16995`)
- `wp_ajax_cmn_candidate_upload_doc` -> `handle_candidate_upload_doc` (`covermenowone-one.php:1037`, `covermenowone-one.php:93705`)
- `wp_ajax_cmn_candidate_delete_doc` -> `handle_candidate_delete_doc` (`covermenowone-one.php:1038`, `covermenowone-one.php:93865`)
- `wp_ajax_cmn_candidate_remove_doc` -> `handle_candidate_delete_doc` (`covermenowone-one.php:1039`, `covermenowone-one.php:93865`)
- `wp_ajax_cmn_candidate_get_doc` -> `handle_candidate_get_doc` (`covermenowone-one.php:1040`, `covermenowone-one.php:93968`)
- `wp_ajax_cmn_get_compliance_status` -> `handle_get_compliance_status` (`covermenowone-one.php:1041`, `covermenowone-one.php:94048`)
- `wp_ajax_cmn_generate_cv_converter_token` -> `handle_generate_cv_converter_token` (`covermenowone-one.php:1042`, `covermenowone-one.php:93005`)
- `wp_ajax_cmn_get_candidate_original_cv` -> `handle_get_candidate_original_cv` (`covermenowone-one.php:1043`, `covermenowone-one.php:93058`)
- `wp_ajax_cmn_save_candidate_formatted_cv` -> `handle_save_candidate_formatted_cv` (`covermenowone-one.php:1044`, `covermenowone-one.php:93124`)
- `wp_ajax_cmn_cv_save_formatted` -> `handle_cv_save_formatted` (`covermenowone-one.php:1045`, `covermenowone-one.php:93231`)
- `wp_ajax_cmn_candidate_update_profile` -> `handle_candidate_update_profile` (`covermenowone-one.php:1047`, `covermenowone-one.php:93480`)
- `wp_ajax_cmn_candidate_profile_photo_upload` -> `handle_candidate_profile_photo_upload` (`covermenowone-one.php:1048`, `covermenowone-one.php:93373`)
- `wp_ajax_cmn_candidate_profile_photo_remove` -> `handle_candidate_profile_photo_remove` (`covermenowone-one.php:1049`, `covermenowone-one.php:93452`)
- `wp_ajax_cmn_candidate_contact_card_save` -> `handle_candidate_contact_card_save` (`covermenowone-one.php:1050`, `covermenowone-one.php:93661`)
- `wp_ajax_cmn_candidate_learning_opt_in` -> `handle_candidate_learning_opt_in` (`covermenowone-one.php:1051`, `covermenowone-one.php:93238`)
- `wp_ajax_cmn_candidate_learning_complete_course` -> `handle_candidate_learning_complete_course` (`covermenowone-one.php:1052`, `covermenowone-one.php:93259`)
- `wp_ajax_cmn_candidate_rewards_overview` -> `handle_candidate_rewards_overview` (`covermenowone-one.php:1053`, `covermenowone-one.php:73526`)
- `wp_ajax_cmn_candidate_rewards_open_appeal` -> `handle_candidate_rewards_open_appeal` (`covermenowone-one.php:1054`, `covermenowone-one.php:73595`)
- `wp_ajax_cmn_candidate_weekly_earnings_overview` -> `handle_candidate_weekly_earnings_overview` (`covermenowone-one.php:1055`, `covermenowone-one.php:73470`)
- `wp_ajax_cmn_marketing_lead_finder` -> `handle_marketing_lead_finder` (`covermenowone-one.php:1056`, `covermenowone-one.php:97956`)
- `wp_ajax_cmn_marketing_save_list` -> `handle_marketing_save_list` (`covermenowone-one.php:1057`, `covermenowone-one.php:98020`)
- `wp_ajax_cmn_marketing_get_lists` -> `handle_marketing_get_lists` (`covermenowone-one.php:1058`, `covermenowone-one.php:98078`)
- `wp_ajax_cmn_marketing_refresh_list` -> `handle_marketing_refresh_list` (`covermenowone-one.php:1059`, `covermenowone-one.php:98090`)
- `wp_ajax_cmn_marketing_duplicate_list` -> `handle_marketing_duplicate_list` (`covermenowone-one.php:1060`, `covermenowone-one.php:98118`)
- `wp_ajax_cmn_marketing_delete_list` -> `handle_marketing_delete_list` (`covermenowone-one.php:1061`, `covermenowone-one.php:98159`)
- `wp_ajax_cmn_marketing_get_list_members` -> `handle_marketing_get_list_members` (`covermenowone-one.php:1062`, `covermenowone-one.php:98184`)
- `wp_ajax_cmn_marketing_save_campaign` -> `handle_marketing_save_campaign` (`covermenowone-one.php:1063`, `covermenowone-one.php:98221`)
- `wp_ajax_cmn_marketing_preview_campaign` -> `handle_marketing_preview_campaign` (`covermenowone-one.php:1064`, `covermenowone-one.php:97987`)
- `wp_ajax_cmn_marketing_get_campaigns` -> `handle_marketing_get_campaigns` (`covermenowone-one.php:1065`, `covermenowone-one.php:98280`)
- `wp_ajax_cmn_marketing_queue_campaign` -> `handle_marketing_queue_campaign` (`covermenowone-one.php:1066`, `covermenowone-one.php:98292`)
- `wp_ajax_cmn_marketing_get_queue` -> `handle_marketing_get_queue` (`covermenowone-one.php:1067`, `covermenowone-one.php:98435`)
- `wp_ajax_cmn_marketing_process_queue` -> `handle_marketing_process_queue` (`covermenowone-one.php:1068`, `covermenowone-one.php:98447`)
- `wp_ajax_cmn_marketing_pause_campaign` -> `handle_marketing_pause_campaign` (`covermenowone-one.php:1069`, `covermenowone-one.php:98464`)
- `wp_ajax_cmn_marketing_resume_campaign` -> `handle_marketing_resume_campaign` (`covermenowone-one.php:1070`, `covermenowone-one.php:98490`)
- `wp_ajax_cmn_marketing_get_replies` -> `handle_marketing_get_replies` (`covermenowone-one.php:1071`, `covermenowone-one.php:98517`)
- `wp_ajax_cmn_marketing_poll_replies` -> `handle_marketing_poll_replies` (`covermenowone-one.php:1072`, `covermenowone-one.php:98530`)
- `wp_ajax_cmn_marketing_save_template` -> `handle_marketing_save_template` (`covermenowone-one.php:1073`, `covermenowone-one.php:98576`)
- `wp_ajax_cmn_marketing_get_templates` -> `handle_marketing_get_templates` (`covermenowone-one.php:1074`, `covermenowone-one.php:98615`)
- `wp_ajax_cmn_marketing_delete_template` -> `handle_marketing_delete_template` (`covermenowone-one.php:1075`, `covermenowone-one.php:98625`)
- `wp_ajax_cmn_marketing_test_send` -> `handle_marketing_test_send` (`covermenowone-one.php:1076`, `covermenowone-one.php:98641`)
- `wp_ajax_cmn_marketing_get_send_log` -> `handle_marketing_get_send_log` (`covermenowone-one.php:1077`, `covermenowone-one.php:98669`)
- `wp_ajax_cmn_marketing_get_unsubscribes` -> `handle_marketing_get_unsubscribes` (`covermenowone-one.php:1078`, `covermenowone-one.php:98807`)
- `wp_ajax_cmn_marketing_add_unsubscribe` -> `handle_marketing_add_unsubscribe` (`covermenowone-one.php:1079`, `covermenowone-one.php:98822`)
- `wp_ajax_cmn_marketing_remove_unsubscribe` -> `handle_marketing_remove_unsubscribe` (`covermenowone-one.php:1080`, `covermenowone-one.php:98837`)
- `wp_ajax_cmn_marketing_get_overview` -> `handle_marketing_get_overview` (`covermenowone-one.php:1081`, `covermenowone-one.php:98548`)
- `wp_ajax_cmn_marketing_save_segment` -> `handle_marketing_save_segment` (`covermenowone-one.php:1082`, `covermenowone-one.php:98853`)
- `wp_ajax_cmn_marketing_get_segments` -> `handle_marketing_get_segments` (`covermenowone-one.php:1083`, `covermenowone-one.php:98893`)
- `wp_ajax_cmn_marketing_run_segment` -> `handle_marketing_run_segment` (`covermenowone-one.php:1084`, `covermenowone-one.php:98903`)
- `wp_ajax_cmn_marketing_segment_to_list` -> `handle_marketing_segment_to_list` (`covermenowone-one.php:1085`, `covermenowone-one.php:98924`)
- `wp_ajax_cmn_marketing_save_quick_campaign` -> `handle_marketing_save_quick_campaign` (`covermenowone-one.php:1086`, `covermenowone-one.php:98975`)
- `wp_ajax_cmn_marketing_get_quick_campaigns` -> `handle_marketing_get_quick_campaigns` (`covermenowone-one.php:1087`, `covermenowone-one.php:99026`)
- `wp_ajax_cmn_marketing_run_quick_campaign` -> `handle_marketing_run_quick_campaign` (`covermenowone-one.php:1088`, `covermenowone-one.php:99036`)
- `wp_ajax_cmn_marketing_export_send_log` -> `handle_marketing_export_send_log` (`covermenowone-one.php:1089`, `covermenowone-one.php:98717`)
- `wp_ajax_cmn_marketing_resend_failed` -> `handle_marketing_resend_failed` (`covermenowone-one.php:1090`, `covermenowone-one.php:98768`)
- `wp_ajax_cmn_marketing_save_settings` -> `handle_marketing_save_settings` (`covermenowone-one.php:1091`, `covermenowone-one.php:98561`)
- `wp_ajax_cmn_match_get_context` -> `handle_match_get_context` (`covermenowone-one.php:1092`, `covermenowone-one.php:25950`)
- `wp_ajax_cmn_match_run_simulation` -> `handle_match_run_simulation` (`covermenowone-one.php:1093`, `covermenowone-one.php:25977`)
- `wp_ajax_cmn_match_save_weights` -> `handle_match_save_weights` (`covermenowone-one.php:1094`, `covermenowone-one.php:26006`)
- `wp_ajax_cmn_match_set_active_weights` -> `handle_match_set_active_weights` (`covermenowone-one.php:1095`, `covermenowone-one.php:26030`)
- `wp_ajax_cmn_match_rollback_weights` -> `handle_match_rollback_weights` (`covermenowone-one.php:1096`, `covermenowone-one.php:26067`)

### wp_ajax_nopriv (8)
- `wp_ajax_nopriv_cmn_thread_get` -> `handle_thread_get` (`covermenowone-one.php:880`, `covermenowone-one.php:87591`)
- `wp_ajax_nopriv_cmn_thread_post_message` -> `handle_thread_post_message` (`covermenowone-one.php:882`, `covermenowone-one.php:87620`)
- `wp_ajax_nopriv_cmn_thread_mark_read` -> `handle_thread_mark_read` (`covermenowone-one.php:884`, `covermenowone-one.php:87713`)
- `wp_ajax_nopriv_cmn_thread_download_attachment` -> `handle_thread_download_attachment` (`covermenowone-one.php:887`, `covermenowone-one.php:87778`)
- `wp_ajax_nopriv_cmn_livechat_start` -> `handle_livechat_start` (`covermenowone-one.php:1030`, `covermenowone-one.php:16651`)
- `wp_ajax_nopriv_cmn_livechat_send` -> `handle_livechat_send` (`covermenowone-one.php:1032`, `covermenowone-one.php:16848`)
- `wp_ajax_nopriv_cmn_livechat_poll` -> `handle_livechat_poll` (`covermenowone-one.php:1034`, `covermenowone-one.php:16942`)
- `wp_ajax_nopriv_cmn_livechat_feedback_submit` -> `handle_livechat_feedback_submit` (`covermenowone-one.php:1036`, `covermenowone-one.php:16995`)

### admin_post (124)
- `admin_post_cmn_add_activity` -> `handle_add_activity` (`covermenowone-one.php:800`, `covermenowone-one.php:86481`)
- `admin_post_cmn_complete_activity` -> `handle_complete_activity` (`covermenowone-one.php:801`, `covermenowone-one.php:86554`)
- `admin_post_cmn_save_admin_recipient_email` -> `handle_save_admin_recipient_email` (`covermenowone-one.php:802`, `covermenowone-one.php:43279`)
- `admin_post_cmn_register_school` -> `handle_register_school` (`covermenowone-one.php:804`, `covermenowone-one.php:88593`)
- `admin_post_cmn_process_school_request` -> `handle_process_school_request` (`covermenowone-one.php:805`, `covermenowone-one.php:88142`)
- `admin_post_cmn_approve_school_request` -> `handle_approve_school_request` (`covermenowone-one.php:806`, `covermenowone-one.php:88328`)
- `admin_post_cmn_reject_school_request` -> `handle_reject_school_request` (`covermenowone-one.php:807`, `covermenowone-one.php:88372`)
- `admin_post_cmn_register_candidate` -> `handle_register_candidate` (`covermenowone-one.php:811`, `covermenowone-one.php:88917`)
- `admin_post_cmn_import_schools` -> `handle_import_schools_portal` (`covermenowone-one.php:812`, `covermenowone-one.php:101510`)
- `admin_post_cmn_school_import_errors_csv` -> `handle_school_import_errors_csv` (`covermenowone-one.php:813`, `covermenowone-one.php:101469`)
- `admin_post_cmn_bulk_schools` -> `handle_bulk_schools` (`covermenowone-one.php:814`, `covermenowone-one.php:103962`)
- `admin_post_cmn_mark_school_import_resolved` -> `handle_mark_school_import_resolved` (`covermenowone-one.php:815`, `covermenowone-one.php:103917`)
- `admin_post_cmn_add_school` -> `handle_add_school_portal` (`covermenowone-one.php:816`, `covermenowone-one.php:101650`)
- `admin_post_cmn_add_staff` -> `handle_add_staff_portal` (`covermenowone-one.php:817`, `covermenowone-one.php:101768`)
- `admin_post_cmn_assign_account_manager` -> `handle_assign_account_manager` (`covermenowone-one.php:818`, `covermenowone-one.php:104448`)
- `admin_post_cmn_school_rebook_candidate` -> `handle_school_rebook_candidate` (`covermenowone-one.php:819`, `covermenowone-one.php:95729`)
- `admin_post_cmn_priority_allocation_send` -> `handle_priority_allocation_send` (`covermenowone-one.php:820`, `covermenowone-one.php:95084`)
- `admin_post_cmn_priority_interest_register` -> `handle_priority_interest_register` (`covermenowone-one.php:821`, `covermenowone-one.php:95254`)
- `admin_post_cmn_send_emergency_broadcast` -> `handle_send_emergency_broadcast` (`covermenowone-one.php:823`, `covermenowone-one.php:30348`)
- `admin_post_cmn_add_contact` -> `handle_add_contact_portal` (`covermenowone-one.php:824`, `covermenowone-one.php:103624`)
- `admin_post_cmn_update_contact` -> `handle_update_contact_portal` (`covermenowone-one.php:825`, `covermenowone-one.php:103698`)
- `admin_post_cmn_delete_contact` -> `handle_delete_contact_portal` (`covermenowone-one.php:826`, `covermenowone-one.php:103797`)
- `admin_post_cmn_import_contacts` -> `handle_import_contacts_portal` (`covermenowone-one.php:827`, `covermenowone-one.php:103839`)
- `admin_post_cmn_assign_contact_to_school` -> `handle_assign_contact_to_school` (`covermenowone-one.php:828`, `covermenowone-one.php:104385`)
- `admin_post_cmn_unassign_contact` -> `handle_unassign_contact` (`covermenowone-one.php:829`, `covermenowone-one.php:104418`)
- `admin_post_cmn_convert_client` -> `handle_convert_client` (`covermenowone-one.php:830`, `covermenowone-one.php:86665`)
- `admin_post_cmn_verify_candidate_email` -> `handle_verify_candidate_email` (`covermenowone-one.php:832`, `covermenowone-one.php:91909`)
- `admin_post_cmn_resend_candidate_verification` -> `handle_resend_candidate_verification` (`covermenowone-one.php:834`, `covermenowone-one.php:91921`)
- `admin_post_cmn_toggle_availability` -> `handle_toggle_availability` (`covermenowone-one.php:835`, `covermenowone-one.php:91942`)
- `admin_post_cmn_save_calendar` -> `handle_save_calendar` (`covermenowone-one.php:836`, `covermenowone-one.php:91997`)
- `admin_post_cmn_save_candidate_bank_details` -> `handle_save_candidate_bank_details` (`covermenowone-one.php:837`, `covermenowone-one.php:74267`)
- `admin_post_cmn_candidate_accept_compliance_ack` -> `handle_candidate_accept_compliance_ack` (`covermenowone-one.php:838`, `covermenowone-one.php:74484`)
- `admin_post_cmn_candidate_generate_remittance_pdf` -> `handle_candidate_generate_remittance_pdf` (`covermenowone-one.php:839`, `covermenowone-one.php:74603`)
- `admin_post_cmn_candidate_download_remittance_pdf` -> `handle_candidate_download_remittance_pdf` (`covermenowone-one.php:840`, `covermenowone-one.php:74673`)
- `admin_post_cmn_generate_payout_run` -> `handle_generate_payout_run` (`covermenowone-one.php:841`, `covermenowone-one.php:74765`)
- `admin_post_cmn_training_mode_update` -> `handle_training_mode_update` (`covermenowone-one.php:842`, `covermenowone-one.php:73711`)
- `admin_post_cmn_training_console_action` -> `handle_training_console_action` (`covermenowone-one.php:843`, `covermenowone-one.php:73815`)
- `admin_post_cmn_payroll_adjustment_create` -> `handle_payroll_adjustment_create` (`covermenowone-one.php:844`, `covermenowone-one.php:75028`)
- `admin_post_cmn_payroll_adjustment_decision` -> `handle_payroll_adjustment_decision` (`covermenowone-one.php:845`, `covermenowone-one.php:75171`)
- `admin_post_cmn_mark_pay_approval_disputed` -> `handle_mark_pay_approval_disputed` (`covermenowone-one.php:846`, `covermenowone-one.php:75300`)
- `admin_post_cmn_staff_payroll_ticket_quick_action` -> `handle_staff_payroll_ticket_quick_action` (`covermenowone-one.php:847`, `covermenowone-one.php:75425`)
- `admin_post_cmn_create_booking` -> `handle_create_booking` (`covermenowone-one.php:848`, `covermenowone-one.php:97664`)
- `admin_post_cmn_confirm_booking_day` -> `handle_confirm_booking_day` (`covermenowone-one.php:850`, `covermenowone-one.php:47226`)
- `admin_post_cmn_update_candidate_request` -> `handle_update_candidate_request` (`covermenowone-one.php:905`, `covermenowone-one.php:97279`)
- `admin_post_cmn_send_candidate_invite` -> `handle_send_candidate_invite` (`covermenowone-one.php:906`, `covermenowone-one.php:99139`)
- `admin_post_cmn_candidate_response` -> `handle_candidate_response` (`covermenowone-one.php:908`, `covermenowone-one.php:99199`)
- `admin_post_cmn_candidate_request_action` -> `handle_candidate_request_action` (`covermenowone-one.php:909`, `covermenowone-one.php:96089`)
- `admin_post_cmn_booking_chat_post` -> `handle_booking_chat_post` (`covermenowone-one.php:910`, `covermenowone-one.php:96743`)
- `admin_post_cmn_booking_chat_ack` -> `handle_booking_chat_ack` (`covermenowone-one.php:911`, `covermenowone-one.php:97133`)
- `admin_post_cmn_staff_update_candidate_pay` -> `handle_staff_update_candidate_pay` (`covermenowone-one.php:919`, `covermenowone-one.php:97156`)
- `admin_post_cmn_open_booking_thread` -> `handle_open_booking_thread` (`covermenowone-one.php:920`, `covermenowone-one.php:96497`)
- `admin_post_cmn_set_booking_thread_status` -> `handle_set_booking_thread_status` (`covermenowone-one.php:921`, `covermenowone-one.php:96589`)
- `admin_post_cmn_staff_save_candidate_role_rates` -> `handle_staff_save_candidate_role_rates` (`covermenowone-one.php:922`, `covermenowone-one.php:97236`)
- `admin_post_cmn_staff_review_candidate_doc` -> `handle_staff_review_candidate_doc` (`covermenowone-one.php:923`, `covermenowone-one.php:94371`)
- `admin_post_cmn_staff_delete_candidate_doc` -> `handle_staff_delete_candidate_doc` (`covermenowone-one.php:924`, `covermenowone-one.php:94272`)
- `admin_post_cmn_staff_set_candidate_doc_visibility` -> `handle_staff_set_candidate_doc_visibility` (`covermenowone-one.php:925`, `covermenowone-one.php:94236`)
- `admin_post_cmn_update_school_assignments` -> `handle_update_school_assignments` (`covermenowone-one.php:926`, `covermenowone-one.php:99501`)
- `admin_post_cmn_school_update_profile` -> `handle_school_update_profile` (`covermenowone-one.php:927`, `covermenowone-one.php:99572`)
- `admin_post_cmn_school_add_team_member` -> `handle_school_add_team_member` (`covermenowone-one.php:928`, `covermenowone-one.php:99700`)
- `admin_post_cmn_school_create_long_booking` -> `handle_school_create_long_booking` (`covermenowone-one.php:929`, `covermenowone-one.php:95873`)
- `admin_post_cmn_school_template_defaults_save` -> `handle_school_template_defaults_save` (`covermenowone-one.php:930`, `covermenowone-one.php:99524`)
- `admin_post_cmn_update_candidate_rate` -> `handle_update_candidate_rate` (`covermenowone-one.php:931`, `covermenowone-one.php:99881`)
- `admin_post_cmn_update_status` -> `handle_update_status` (`covermenowone-one.php:932`, `covermenowone-one.php:97768`)
- `admin_post_cmn_add_candidate_internal_note` -> `handle_add_candidate_internal_note` (`covermenowone-one.php:933`, `covermenowone-one.php:29947`)
- `admin_post_cmn_staff_candidate_compliance_decision` -> `handle_staff_candidate_compliance_decision` (`covermenowone-one.php:934`, `covermenowone-one.php:94085`)
- `admin_post_cmn_save_release_version` -> `handle_save_release_version` (`covermenowone-one.php:935`, `covermenowone-one.php:43409`)
- `admin_post_cmn_save_staff_availability_settings` -> `handle_save_staff_availability_settings` (`covermenowone-one.php:936`, `covermenowone-one.php:43333`)
- `admin_post_cmn_save_school_team_user_limit` -> `handle_save_school_team_user_limit` (`covermenowone-one.php:937`, `covermenowone-one.php:43305`)
- `admin_post_cmn_save_converter_settings` -> `handle_save_converter_settings` (`covermenowone-one.php:938`, `covermenowone-one.php:43447`)
- `admin_post_cmn_email_sender_add` -> `handle_email_sender_add` (`covermenowone-one.php:939`, `covermenowone-one.php:42204`)
- `admin_post_cmn_email_sender_toggle` -> `handle_email_sender_toggle` (`covermenowone-one.php:940`, `covermenowone-one.php:42267`)
- `admin_post_cmn_email_template_assign_sender` -> `handle_email_template_assign_sender` (`covermenowone-one.php:941`, `covermenowone-one.php:42355`)
- `admin_post_cmn_email_template_save` -> `handle_email_template_save` (`covermenowone-one.php:942`, `covermenowone-one.php:42498`)
- `admin_post_cmn_email_centre_send_test` -> `handle_email_centre_send_test` (`covermenowone-one.php:943`, `covermenowone-one.php:42852`)
- `admin_post_cmn_email_log_resend` -> `handle_email_log_resend` (`covermenowone-one.php:944`, `covermenowone-one.php:26101`)
- `admin_post_cmn_regenerate_marketing_runner_token` -> `handle_regenerate_marketing_runner_token` (`covermenowone-one.php:945`, `covermenowone-one.php:43503`)
- `admin_post_cmn_marketing_runner` -> `handle_marketing_runner` (`covermenowone-one.php:947`, `covermenowone-one.php:43529`)
- `admin_post_cmn_ready_response_save` -> `handle_ready_response_save` (`covermenowone-one.php:948`, `covermenowone-one.php:94472`)
- `admin_post_cmn_ready_response_delete` -> `handle_ready_response_delete` (`covermenowone-one.php:949`, `covermenowone-one.php:94544`)
- `admin_post_cmn_save_automation_rule` -> `handle_save_automation_rule` (`covermenowone-one.php:950`, `covermenowone-one.php:86222`)
- `admin_post_cmn_toggle_automation_rule` -> `handle_toggle_automation_rule` (`covermenowone-one.php:951`, `covermenowone-one.php:86364`)
- `admin_post_cmn_export_automation_logs` -> `handle_export_automation_logs` (`covermenowone-one.php:952`, `covermenowone-one.php:86390`)
- `admin_post_cmn_run_automation_smoke_test` -> `handle_run_automation_smoke_test` (`covermenowone-one.php:953`, `covermenowone-one.php:86443`)
- `admin_post_cmn_export_data_integrity_csv` -> `handle_export_data_integrity_csv` (`covermenowone-one.php:954`, `covermenowone-one.php:32519`)
- `admin_post_cmn_export_finance_invoices_csv` -> `handle_export_finance_invoices_csv` (`covermenowone-one.php:955`, `covermenowone-one.php:32552`)
- `admin_post_cmn_export_partner_programme_summary_csv` -> `handle_export_partner_programme_summary_csv` (`covermenowone-one.php:956`, `covermenowone-one.php:32681`)
- `admin_post_cmn_generate_monthly_invoices` -> `handle_generate_monthly_invoices` (`covermenowone-one.php:957`, `covermenowone-one.php:38162`)
- `admin_post_cmn_run_monthly_invoice_job_now` -> `handle_run_monthly_invoice_job_now` (`covermenowone-one.php:958`, `covermenowone-one.php:37827`)
- `admin_post_cmn_run_invoice_overdue_reminder_job_now` -> `handle_run_invoice_overdue_reminder_job_now` (`covermenowone-one.php:959`, `covermenowone-one.php:37890`)
- `admin_post_cmn_update_invoice_school_reminder_opt_out` -> `handle_update_invoice_school_reminder_opt_out` (`covermenowone-one.php:960`, `covermenowone-one.php:37938`)
- `admin_post_cmn_run_candidate_rewards_reset_now` -> `handle_run_candidate_rewards_reset_now` (`covermenowone-one.php:961`, `covermenowone-one.php:37984`)
- `admin_post_cmn_run_rewards_payroll_addon_processor` -> `handle_run_rewards_payroll_addon_processor` (`covermenowone-one.php:962`, `covermenowone-one.php:38037`)
- `admin_post_cmn_mark_rewards_addons_processed` -> `handle_mark_rewards_addons_processed` (`covermenowone-one.php:963`, `covermenowone-one.php:38104`)
- `admin_post_cmn_confirm_candidate_no_show` -> `handle_confirm_candidate_no_show` (`covermenowone-one.php:964`, `covermenowone-one.php:96610`)
- `admin_post_cmn_invoice_change_status` -> `handle_invoice_change_status` (`covermenowone-one.php:965`, `covermenowone-one.php:38265`)
- `admin_post_cmn_invoice_add_adjustment` -> `handle_invoice_add_adjustment` (`covermenowone-one.php:966`, `covermenowone-one.php:38303`)
- `admin_post_cmn_invoice_record_payment` -> `handle_invoice_record_payment` (`covermenowone-one.php:967`, `covermenowone-one.php:38386`)
- `admin_post_cmn_invoice_send_email` -> `handle_invoice_send_email` (`covermenowone-one.php:968`, `covermenowone-one.php:38582`)
- `admin_post_cmn_download_invoice_pdf` -> `handle_download_invoice_pdf` (`covermenowone-one.php:969`, `covermenowone-one.php:38957`)
- `admin_post_cmn_partner_programme_recalculate` -> `handle_partner_programme_recalculate` (`covermenowone-one.php:970`, `covermenowone-one.php:39042`)
- `admin_post_cmn_partner_programme_adjust_credits` -> `handle_partner_programme_adjust_credits` (`covermenowone-one.php:971`, `covermenowone-one.php:39078`)
- `admin_post_cmn_school_partner_admin_recalculate_days` -> `handle_school_partner_admin_recalculate_days` (`covermenowone-one.php:972`, `covermenowone-one.php:39126`)
- `admin_post_cmn_school_partner_admin_set_tier` -> `handle_school_partner_admin_set_tier` (`covermenowone-one.php:973`, `covermenowone-one.php:39176`)
- `admin_post_cmn_school_partner_admin_adjust_days` -> `handle_school_partner_admin_adjust_days` (`covermenowone-one.php:974`, `covermenowone-one.php:39227`)
- `admin_post_cmn_school_partner_admin_create_credit` -> `handle_school_partner_admin_create_credit` (`covermenowone-one.php:975`, `covermenowone-one.php:39278`)
- `admin_post_cmn_school_partner_admin_void_credit` -> `handle_school_partner_admin_void_credit` (`covermenowone-one.php:976`, `covermenowone-one.php:39340`)
- `admin_post_cmn_reconcile_school_lists` -> `handle_reconcile_school_lists` (`covermenowone-one.php:977`, `covermenowone-one.php:90640`)
- `admin_post_cmn_staff_candidate_rewards_recalculate` -> `handle_staff_candidate_rewards_recalculate` (`covermenowone-one.php:978`, `covermenowone-one.php:39391`)
- `admin_post_cmn_staff_candidate_rewards_flag_review` -> `handle_staff_candidate_rewards_flag_review` (`covermenowone-one.php:979`, `covermenowone-one.php:39448`)
- `admin_post_cmn_staff_candidate_rewards_terminate` -> `handle_staff_candidate_rewards_terminate` (`covermenowone-one.php:980`, `covermenowone-one.php:39505`)
- `admin_post_cmn_staff_candidate_rewards_reverse_no_show` -> `handle_staff_candidate_rewards_reverse_no_show` (`covermenowone-one.php:981`, `covermenowone-one.php:39567`)
- `admin_post_cmn_staff_candidate_rewards_adjust_shifts` -> `handle_staff_candidate_rewards_adjust_shifts` (`covermenowone-one.php:982`, `covermenowone-one.php:39635`)
- `admin_post_cmn_staff_candidate_rewards_set_tier` -> `handle_staff_candidate_rewards_set_tier` (`covermenowone-one.php:983`, `covermenowone-one.php:39748`)
- `admin_post_cmn_staff_candidate_rewards_suspend` -> `handle_staff_candidate_rewards_suspend` (`covermenowone-one.php:984`, `covermenowone-one.php:39851`)
- `admin_post_cmn_staff_candidate_rewards_reinstate` -> `handle_staff_candidate_rewards_reinstate` (`covermenowone-one.php:985`, `covermenowone-one.php:39929`)
- `admin_post_cmn_staff_candidate_rewards_mark_no_show` -> `handle_staff_candidate_rewards_mark_no_show` (`covermenowone-one.php:986`, `covermenowone-one.php:40021`)
- `admin_post_cmn_staff_candidate_rewards_resolve_appeal` -> `handle_staff_candidate_rewards_resolve_appeal` (`covermenowone-one.php:987`, `covermenowone-one.php:40115`)
- `admin_post_cmn_portal_login` -> `handle_portal_login` (`covermenowone-one.php:1001`, `covermenowone-one.php:101977`)
- `admin_post_cmn_portal_logout` -> `handle_portal_logout` (`covermenowone-one.php:1003`, `covermenowone-one.php:102002`)
- `admin_post_` -> `handle_portal_login_fallback_admin_post` (`covermenowone-one.php:1005`, `covermenowone-one.php:102016`)
- `admin_post_cmn_portal_forgot_password` -> `handle_portal_forgot_password` (`covermenowone-one.php:1007`, `covermenowone-one.php:102029`)
- `admin_post_cmn_portal_reset_password` -> `handle_portal_reset_password` (`covermenowone-one.php:1009`, `covermenowone-one.php:102048`)
- `admin_post_cmn_run_upgrade_runner` -> `handle_run_upgrade_runner` (`covermenowone-one.php:1010`, `covermenowone-one.php:9208`)
- `admin_post_cmn_candidate_download_doc` -> `handle_candidate_download_doc` (`covermenowone-one.php:1097`, `covermenowone-one.php:94140`)

### admin_post_nopriv (13)
- `admin_post_nopriv_cmn_register_school` -> `handle_register_school` (`covermenowone-one.php:803`, `covermenowone-one.php:88593`)
- `admin_post_nopriv_cmn_register_candidate` -> `handle_register_candidate` (`covermenowone-one.php:810`, `covermenowone-one.php:88917`)
- `admin_post_nopriv_cmn_priority_interest_register` -> `handle_priority_interest_register` (`covermenowone-one.php:822`, `covermenowone-one.php:95254`)
- `admin_post_nopriv_cmn_verify_candidate_email` -> `handle_verify_candidate_email` (`covermenowone-one.php:831`, `covermenowone-one.php:91909`)
- `admin_post_nopriv_cmn_resend_candidate_verification` -> `handle_resend_candidate_verification` (`covermenowone-one.php:833`, `covermenowone-one.php:91921`)
- `admin_post_nopriv_cmn_confirm_booking_day` -> `handle_confirm_booking_day` (`covermenowone-one.php:849`, `covermenowone-one.php:47226`)
- `admin_post_nopriv_cmn_candidate_response` -> `handle_candidate_response` (`covermenowone-one.php:907`, `covermenowone-one.php:99199`)
- `admin_post_nopriv_cmn_marketing_runner` -> `handle_marketing_runner` (`covermenowone-one.php:946`, `covermenowone-one.php:43529`)
- `admin_post_nopriv_cmn_portal_login` -> `handle_portal_login` (`covermenowone-one.php:1000`, `covermenowone-one.php:101977`)
- `admin_post_nopriv_cmn_portal_logout` -> `handle_portal_logout` (`covermenowone-one.php:1002`, `covermenowone-one.php:102002`)
- `admin_post_nopriv_` -> `handle_portal_login_fallback_admin_post` (`covermenowone-one.php:1004`, `covermenowone-one.php:102016`)
- `admin_post_nopriv_cmn_portal_forgot_password` -> `handle_portal_forgot_password` (`covermenowone-one.php:1006`, `covermenowone-one.php:102029`)
- `admin_post_nopriv_cmn_portal_reset_password` -> `handle_portal_reset_password` (`covermenowone-one.php:1008`, `covermenowone-one.php:102048`)

### cron (6)
- `cmn_send_school_registration_emails` -> `handle_deferred_school_registration_emails` (`covermenowone-one.php:808`, `covermenowone-one.php:88874`)
- `cmn_process_school_registration_side_effects` -> `handle_school_registration_side_effects` (`covermenowone-one.php:809`, `covermenowone-one.php:88832`)
- `cmn_expire_bookings` -> `expire_booking_requests` (`covermenowone-one.php:1117`, `covermenowone-one.php:99392`)
- `cmn_compliance_reminders` -> `run_compliance_reminders` (`covermenowone-one.php:1129`, `covermenowone-one.php:65597`)
- `cmn_availability_nudges` -> `run_availability_nudges` (`covermenowone-one.php:1132`, `covermenowone-one.php:65812`)
- `cmn_automation_runner` -> `run_automation_runner` (`covermenowone-one.php:1145`, `covermenowone-one.php:9046`)

### shortcode (14)
- `cmn_login` -> `render_login_shortcode` (`covermenowone-one.php:26297`, `covermenowone-one.php:26312`)
- `cmn_portal` -> `render_portal_shortcode` (`covermenowone-one.php:26298`, `covermenowone-one.php:26613`)
- `cmn_staff_dashboard` -> `render_staff_dashboard_shortcode` (`covermenowone-one.php:26299`, `covermenowone-one.php:26904`)
- `cmn_staff_schools` -> `render_staff_schools_shortcode` (`covermenowone-one.php:26300`, `covermenowone-one.php:27282`)
- `cmn_staff_candidates` -> `render_staff_candidates_shortcode` (`covermenowone-one.php:26301`, `covermenowone-one.php:28340`)
- `cmn_staff_bookings` -> `render_staff_bookings_shortcode` (`covermenowone-one.php:26302`, `covermenowone-one.php:31674`)
- `cmn_school_dashboard` -> `render_school_dashboard_shortcode` (`covermenowone-one.php:26303`, `covermenowone-one.php:67439`)
- `cmn_candidate_dashboard` -> `render_candidate_dashboard_shortcode` (`covermenowone-one.php:26304`, `covermenowone-one.php:69583`)
- `cmn_register_school` -> `render_register_school_shortcode` (`covermenowone-one.php:26305`, `covermenowone-one.php:66936`)
- `cmn_register_candidate` -> `render_register_candidate_shortcode` (`covermenowone-one.php:26306`, `covermenowone-one.php:67171`)
- `cmn_request_received` -> `render_request_received_shortcode` (`covermenowone-one.php:26307`, `covermenowone-one.php:67117`)
- `cmn_school_landing` -> `render_school_landing_shortcode` (`covermenowone-one.php:26308`, `covermenowone-one.php:67341`)
- `cmn_candidate_landing` -> `render_candidate_landing_shortcode` (`covermenowone-one.php:26309`, `covermenowone-one.php:67390`)
- `cmn_available_wall` -> `render_available_wall_shortcode` (`covermenowone-one.php:26310`, `covermenowone-one.php:82029`)

### template_route (8)
- `template_redirect` -> `handle_portal_route_fallback` (`covermenowone-one.php:1119`, `covermenowone-one.php:15163`)
- `template_redirect` -> `handle_cv_converter_api_endpoints` (`covermenowone-one.php:1120`, `covermenowone-one.php:10903`)
- `template_redirect` -> `redirect_legacy_portal_paths` (`covermenowone-one.php:1121`, `covermenowone-one.php:15088`)
- `template_redirect` -> `handle_marketing_unsubscribe` (`covermenowone-one.php:1122`, `covermenowone-one.php:15224`)
- `template_redirect` -> `handle_automation_runner_endpoint` (`covermenowone-one.php:1123`, `covermenowone-one.php:9095`)
- `template_redirect` -> `handle_set_release_version_endpoint` (`covermenowone-one.php:1124`, `covermenowone-one.php:9161`)
- `template_redirect` -> `handle_school_request_chat_route` (`covermenowone-one.php:1125`, `covermenowone-one.php:87901`)
- `template_redirect` -> `protect_candidate_doc_attachment_access` (`covermenowone-one.php:1126`, `covermenowone-one.php:65104`)

### rest (4)
- `cmn/v1/cv/original` -> `handle_rest_cv_original` (`covermenowone-one.php:92775`, `covermenowone-one.php:92846`)
- `cmn/v1/cv/formatted` -> `handle_rest_cv_formatted` (`covermenowone-one.php:92783`, `covermenowone-one.php:92893`)
- `cmn/v1/partner-programme/stats` -> `handle_rest_partner_programme_stats` (`covermenowone-one.php:92791`, `covermenowone-one.php:92747`)
- `cmn/v1/candidate/rewards/stats` -> `handle_rest_candidate_rewards_stats` (`covermenowone-one.php:92799`, `covermenowone-one.php:92797`)

## Endpoint Analysis (All Entrypoints)

| Type | Hook | Handler | Registration | Handler Def | Mutates State | Nonce | Guard Wrapper | Capability Enforced | Capability Signal | Rate Limit |
|---|---|---|---:|---:|---|---|---|---|---|---|
| `wp_ajax` | `wp_ajax_cmn_mark_available` | `handle_mark_available` | `851` | `92047` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_mark_unavailable_morning` | `handle_mark_unavailable_morning` | `852` | `92153` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_update_calendar_day` | `handle_update_calendar_day` | `853` | `92196` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_get_calendar_availability` | `handle_get_calendar_availability` | `854` | `92254` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_bulk_import_schools_run` | `handle_bulk_import_schools_run` | `855` | `101333` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_bulk_update_calendar` | `handle_bulk_update_calendar` | `856` | `92274` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_clear_calendar` | `handle_clear_calendar` | `857` | `92349` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_dismiss_candidate_tour` | `handle_dismiss_candidate_tour` | `858` | `92391` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_get_candidate_settings` | `handle_get_candidate_settings` | `859` | `92402` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_save_candidate_settings` | `handle_save_candidate_settings` | `860` | `92429` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_get_notification_preferences` | `handle_get_notification_preferences` | `861` | `92484` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_save_notification_preferences` | `handle_save_notification_preferences` | `862` | `92500` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_get_theme_settings` | `handle_get_theme_settings` | `863` | `92536` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_save_theme_settings` | `handle_save_theme_settings` | `864` | `92550` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_request_delete_account` | `handle_candidate_request_delete_account` | `865` | `92567` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_admin_delete_candidate_account` | `handle_admin_delete_candidate_account` | `866` | `92618` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_request_candidate` | `handle_request_candidate` | `867` | `95535` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_school_live_match_action` | `handle_school_live_match_action` | `868` | `95362` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_school_live_match_presence` | `handle_school_live_match_presence` | `869` | `95458` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_mark_notifications_read` | `handle_mark_notifications_read` | `870` | `23546` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_notifications_mark_all_read` | `handle_notifications_mark_all_read` | `871` | `23558` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_notifications_clear_all` | `handle_notifications_clear_all` | `872` | `23570` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_notifications_mark_read` | `handle_notifications_mark_read` | `873` | `23582` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_school_contact_search` | `handle_school_contact_search_ajax` | `874` | `24609` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_notifications_mark_selected_read` | `handle_notifications_mark_selected_read` | `875` | `23598` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_notifications_delete_selected` | `handle_notifications_delete_selected` | `876` | `23614` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_notifications_poll` | `handle_notifications_poll` | `877` | `23630` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_portal_heartbeat` | `handle_portal_heartbeat` | `878` | `24203` | `no` | `required` | `yes` | `yes` | `portal.logged_in` | `no` |
| `wp_ajax` | `wp_ajax_cmn_thread_get` | `handle_thread_get` | `879` | `87591` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_thread_post_message` | `handle_thread_post_message` | `881` | `87620` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_thread_mark_read` | `handle_thread_mark_read` | `883` | `87713` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_thread_set_status` | `handle_thread_set_status` | `885` | `87741` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_thread_download_attachment` | `handle_thread_download_attachment` | `886` | `87778` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_run_system_health` | `handle_run_system_health` | `888` | `24783` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_get_system_health_runs` | `handle_get_system_health_runs` | `889` | `24812` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_get_system_health_issues` | `handle_get_system_health_issues` | `890` | `24835` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_get_system_health_issue_detail` | `handle_get_system_health_issue_detail` | `891` | `24977` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_update_system_health_issue` | `handle_update_system_health_issue` | `892` | `25007` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_system_health_preview_fix` | `handle_system_health_preview_fix` | `893` | `25038` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_system_health_apply_fix` | `handle_system_health_apply_fix` | `894` | `25065` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_system_health_apply_all_safe_fixes` | `handle_system_health_apply_all_safe_fixes` | `895` | `25727` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_system_health_preview` | `handle_system_health_preview` | `896` | `25791` | `no` | `required` | `yes` | `yes` | `portal.staff.view` | `no` |
| `wp_ajax` | `wp_ajax_cmn_system_health_apply` | `handle_system_health_apply` | `897` | `25827` | `yes` | `required` | `yes` | `yes` | `portal.staff.view` | `no` |
| `wp_ajax` | `wp_ajax_cmn_system_health_export_csv` | `handle_system_health_export_csv` | `898` | `25868` | `no` | `required` | `yes` | `yes` | `portal.staff.view` | `no` |
| `wp_ajax` | `wp_ajax_cmn_get_system_health_fixes` | `handle_get_system_health_fixes` | `899` | `25102` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_save_staff_nav_state` | `handle_save_staff_nav_state` | `900` | `24664` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_save_staff_nav_order` | `handle_save_staff_nav_order` | `901` | `24691` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_touch_staff_presence` | `handle_touch_staff_presence` | `902` | `89438` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_touch_candidate_presence` | `handle_touch_candidate_presence` | `903` | `89535` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_send_test_emails` | `handle_send_test_emails` | `904` | `26216` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_booking_chat_fetch` | `handle_booking_chat_fetch` | `912` | `96804` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_staff_lounge_fetch` | `handle_staff_lounge_fetch` | `913` | `102532` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_staff_lounge_post` | `handle_staff_lounge_post` | `914` | `102548` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_booking_feedback_fetch` | `handle_booking_feedback_fetch` | `915` | `96892` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_booking_feedback_submit` | `handle_booking_feedback_submit` | `916` | `96918` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_email_template_preview` | `handle_email_template_preview` | `917` | `42757` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_email_template_send_test` | `handle_email_template_send_test` | `918` | `42787` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_add_staff_user` | `handle_add_staff_user_ajax` | `1011` | `101814` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_update_staff_user` | `handle_update_staff_user_ajax` | `1012` | `101878` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_send_staff_reset_password` | `handle_send_staff_reset_password_ajax` | `1013` | `101922` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_toggle_staff_deactivated` | `handle_toggle_staff_deactivated_ajax` | `1014` | `101944` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_support_create_ticket` | `handle_support_create_ticket` | `1015` | `102452` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_support_list_tickets` | `handle_support_list_tickets` | `1016` | `102600` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_support_list_tickets_unfiltered_admin` | `handle_support_list_tickets_unfiltered_admin` | `1017` | `102901` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_support_list_tickets_unfiltered_admin` | `handle_support_list_tickets_unfiltered_admin` | `1018` | `102901` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_support_get_ticket` | `handle_support_get_ticket` | `1019` | `102934` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_support_post_message` | `handle_support_post_message` | `1020` | `103041` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_support_close_ticket` | `handle_support_close_ticket` | `1021` | `103152` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_support_request_feedback` | `handle_support_request_feedback` | `1022` | `103287` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_support_submit_feedback` | `handle_support_submit_feedback` | `1023` | `103444` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `wp_ajax` | `wp_ajax_cmn_support_save_transcript` | `handle_support_save_transcript` | `1024` | `103519` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_support_email_transcript` | `handle_support_email_transcript` | `1025` | `103566` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_school_open_account_manager_chat` | `handle_school_open_account_manager_chat` | `1026` | `102380` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_school_account_manager_chat_status` | `handle_school_account_manager_chat_status` | `1027` | `102430` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_submit_payroll_query` | `handle_candidate_submit_payroll_query` | `1028` | `102092` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_livechat_start` | `handle_livechat_start` | `1029` | `16651` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `yes` |
| `wp_ajax` | `wp_ajax_cmn_livechat_send` | `handle_livechat_send` | `1031` | `16848` | `yes` | `required` | `no` | `no` | `none detected` | `yes` |
| `wp_ajax` | `wp_ajax_cmn_livechat_poll` | `handle_livechat_poll` | `1033` | `16942` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_livechat_feedback_submit` | `handle_livechat_feedback_submit` | `1035` | `16995` | `yes` | `required` | `no` | `no` | `none detected` | `yes` |
| `wp_ajax` | `wp_ajax_cmn_candidate_upload_doc` | `handle_candidate_upload_doc` | `1037` | `93705` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_delete_doc` | `handle_candidate_delete_doc` | `1038` | `93865` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_remove_doc` | `handle_candidate_delete_doc` | `1039` | `93865` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_get_doc` | `handle_candidate_get_doc` | `1040` | `93968` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_get_compliance_status` | `handle_get_compliance_status` | `1041` | `94048` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_generate_cv_converter_token` | `handle_generate_cv_converter_token` | `1042` | `93005` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_get_candidate_original_cv` | `handle_get_candidate_original_cv` | `1043` | `93058` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_save_candidate_formatted_cv` | `handle_save_candidate_formatted_cv` | `1044` | `93124` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_cv_save_formatted` | `handle_cv_save_formatted` | `1045` | `93231` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_update_profile` | `handle_candidate_update_profile` | `1047` | `93480` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_profile_photo_upload` | `handle_candidate_profile_photo_upload` | `1048` | `93373` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_profile_photo_remove` | `handle_candidate_profile_photo_remove` | `1049` | `93452` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_contact_card_save` | `handle_candidate_contact_card_save` | `1050` | `93661` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_learning_opt_in` | `handle_candidate_learning_opt_in` | `1051` | `93238` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_learning_complete_course` | `handle_candidate_learning_complete_course` | `1052` | `93259` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_rewards_overview` | `handle_candidate_rewards_overview` | `1053` | `73526` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_rewards_open_appeal` | `handle_candidate_rewards_open_appeal` | `1054` | `73595` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_candidate_weekly_earnings_overview` | `handle_candidate_weekly_earnings_overview` | `1055` | `73470` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_lead_finder` | `handle_marketing_lead_finder` | `1056` | `97956` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_save_list` | `handle_marketing_save_list` | `1057` | `98020` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_get_lists` | `handle_marketing_get_lists` | `1058` | `98078` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_refresh_list` | `handle_marketing_refresh_list` | `1059` | `98090` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_duplicate_list` | `handle_marketing_duplicate_list` | `1060` | `98118` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_delete_list` | `handle_marketing_delete_list` | `1061` | `98159` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_get_list_members` | `handle_marketing_get_list_members` | `1062` | `98184` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_save_campaign` | `handle_marketing_save_campaign` | `1063` | `98221` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_preview_campaign` | `handle_marketing_preview_campaign` | `1064` | `97987` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_get_campaigns` | `handle_marketing_get_campaigns` | `1065` | `98280` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_queue_campaign` | `handle_marketing_queue_campaign` | `1066` | `98292` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_get_queue` | `handle_marketing_get_queue` | `1067` | `98435` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_process_queue` | `handle_marketing_process_queue` | `1068` | `98447` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_pause_campaign` | `handle_marketing_pause_campaign` | `1069` | `98464` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_resume_campaign` | `handle_marketing_resume_campaign` | `1070` | `98490` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_get_replies` | `handle_marketing_get_replies` | `1071` | `98517` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_poll_replies` | `handle_marketing_poll_replies` | `1072` | `98530` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_save_template` | `handle_marketing_save_template` | `1073` | `98576` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_get_templates` | `handle_marketing_get_templates` | `1074` | `98615` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_delete_template` | `handle_marketing_delete_template` | `1075` | `98625` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_test_send` | `handle_marketing_test_send` | `1076` | `98641` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_get_send_log` | `handle_marketing_get_send_log` | `1077` | `98669` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_get_unsubscribes` | `handle_marketing_get_unsubscribes` | `1078` | `98807` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_add_unsubscribe` | `handle_marketing_add_unsubscribe` | `1079` | `98822` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_remove_unsubscribe` | `handle_marketing_remove_unsubscribe` | `1080` | `98837` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_get_overview` | `handle_marketing_get_overview` | `1081` | `98548` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_save_segment` | `handle_marketing_save_segment` | `1082` | `98853` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_get_segments` | `handle_marketing_get_segments` | `1083` | `98893` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_run_segment` | `handle_marketing_run_segment` | `1084` | `98903` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_segment_to_list` | `handle_marketing_segment_to_list` | `1085` | `98924` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_save_quick_campaign` | `handle_marketing_save_quick_campaign` | `1086` | `98975` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_get_quick_campaigns` | `handle_marketing_get_quick_campaigns` | `1087` | `99026` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_run_quick_campaign` | `handle_marketing_run_quick_campaign` | `1088` | `99036` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_export_send_log` | `handle_marketing_export_send_log` | `1089` | `98717` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_resend_failed` | `handle_marketing_resend_failed` | `1090` | `98768` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_marketing_save_settings` | `handle_marketing_save_settings` | `1091` | `98561` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax` | `wp_ajax_cmn_match_get_context` | `handle_match_get_context` | `1092` | `25950` | `no` | `required` | `yes` | `yes` | `portal.staff.view` | `no` |
| `wp_ajax` | `wp_ajax_cmn_match_run_simulation` | `handle_match_run_simulation` | `1093` | `25977` | `no` | `required` | `yes` | `yes` | `portal.staff.view` | `no` |
| `wp_ajax` | `wp_ajax_cmn_match_save_weights` | `handle_match_save_weights` | `1094` | `26006` | `yes` | `required` | `yes` | `yes` | `portal.staff.view` | `no` |
| `wp_ajax` | `wp_ajax_cmn_match_set_active_weights` | `handle_match_set_active_weights` | `1095` | `26030` | `yes` | `required` | `yes` | `yes` | `portal.staff.view` | `no` |
| `wp_ajax` | `wp_ajax_cmn_match_rollback_weights` | `handle_match_rollback_weights` | `1096` | `26067` | `yes` | `required` | `yes` | `yes` | `portal.staff.view` | `no` |
| `wp_ajax_nopriv` | `wp_ajax_nopriv_cmn_thread_get` | `handle_thread_get` | `880` | `87591` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `wp_ajax_nopriv` | `wp_ajax_nopriv_cmn_thread_post_message` | `handle_thread_post_message` | `882` | `87620` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax_nopriv` | `wp_ajax_nopriv_cmn_thread_mark_read` | `handle_thread_mark_read` | `884` | `87713` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax_nopriv` | `wp_ajax_nopriv_cmn_thread_download_attachment` | `handle_thread_download_attachment` | `887` | `87778` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `wp_ajax_nopriv` | `wp_ajax_nopriv_cmn_livechat_start` | `handle_livechat_start` | `1030` | `16651` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `yes` |
| `wp_ajax_nopriv` | `wp_ajax_nopriv_cmn_livechat_send` | `handle_livechat_send` | `1032` | `16848` | `yes` | `required` | `no` | `no` | `none detected` | `yes` |
| `wp_ajax_nopriv` | `wp_ajax_nopriv_cmn_livechat_poll` | `handle_livechat_poll` | `1034` | `16942` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `wp_ajax_nopriv` | `wp_ajax_nopriv_cmn_livechat_feedback_submit` | `handle_livechat_feedback_submit` | `1036` | `16995` | `yes` | `required` | `no` | `no` | `none detected` | `yes` |
| `admin_post` | `admin_post_cmn_add_activity` | `handle_add_activity` | `800` | `86481` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_complete_activity` | `handle_complete_activity` | `801` | `86554` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_save_admin_recipient_email` | `handle_save_admin_recipient_email` | `802` | `43279` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_register_school` | `handle_register_school` | `804` | `88593` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_process_school_request` | `handle_process_school_request` | `805` | `88142` | `yes` | `not_applicable` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_approve_school_request` | `handle_approve_school_request` | `806` | `88328` | `yes` | `not_applicable` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_reject_school_request` | `handle_reject_school_request` | `807` | `88372` | `yes` | `not_applicable` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_register_candidate` | `handle_register_candidate` | `811` | `88917` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_import_schools` | `handle_import_schools_portal` | `812` | `101510` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_school_import_errors_csv` | `handle_school_import_errors_csv` | `813` | `101469` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_bulk_schools` | `handle_bulk_schools` | `814` | `103962` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_mark_school_import_resolved` | `handle_mark_school_import_resolved` | `815` | `103917` | `yes` | `not_applicable` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_add_school` | `handle_add_school_portal` | `816` | `101650` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_add_staff` | `handle_add_staff_portal` | `817` | `101768` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_assign_account_manager` | `handle_assign_account_manager` | `818` | `104448` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_school_rebook_candidate` | `handle_school_rebook_candidate` | `819` | `95729` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_priority_allocation_send` | `handle_priority_allocation_send` | `820` | `95084` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_priority_interest_register` | `handle_priority_interest_register` | `821` | `95254` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_send_emergency_broadcast` | `handle_send_emergency_broadcast` | `823` | `30348` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_add_contact` | `handle_add_contact_portal` | `824` | `103624` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_update_contact` | `handle_update_contact_portal` | `825` | `103698` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_delete_contact` | `handle_delete_contact_portal` | `826` | `103797` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_import_contacts` | `handle_import_contacts_portal` | `827` | `103839` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_assign_contact_to_school` | `handle_assign_contact_to_school` | `828` | `104385` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_unassign_contact` | `handle_unassign_contact` | `829` | `104418` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_convert_client` | `handle_convert_client` | `830` | `86665` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_verify_candidate_email` | `handle_verify_candidate_email` | `832` | `91909` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_resend_candidate_verification` | `handle_resend_candidate_verification` | `834` | `91921` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_toggle_availability` | `handle_toggle_availability` | `835` | `91942` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_save_calendar` | `handle_save_calendar` | `836` | `91997` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_save_candidate_bank_details` | `handle_save_candidate_bank_details` | `837` | `74267` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_candidate_accept_compliance_ack` | `handle_candidate_accept_compliance_ack` | `838` | `74484` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_candidate_generate_remittance_pdf` | `handle_candidate_generate_remittance_pdf` | `839` | `74603` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_candidate_download_remittance_pdf` | `handle_candidate_download_remittance_pdf` | `840` | `74673` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_generate_payout_run` | `handle_generate_payout_run` | `841` | `74765` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_training_mode_update` | `handle_training_mode_update` | `842` | `73711` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_training_console_action` | `handle_training_console_action` | `843` | `73815` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_payroll_adjustment_create` | `handle_payroll_adjustment_create` | `844` | `75028` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_payroll_adjustment_decision` | `handle_payroll_adjustment_decision` | `845` | `75171` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_mark_pay_approval_disputed` | `handle_mark_pay_approval_disputed` | `846` | `75300` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_staff_payroll_ticket_quick_action` | `handle_staff_payroll_ticket_quick_action` | `847` | `75425` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_create_booking` | `handle_create_booking` | `848` | `97664` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_confirm_booking_day` | `handle_confirm_booking_day` | `850` | `47226` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_update_candidate_request` | `handle_update_candidate_request` | `905` | `97279` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_send_candidate_invite` | `handle_send_candidate_invite` | `906` | `99139` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_candidate_response` | `handle_candidate_response` | `908` | `99199` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `yes` |
| `admin_post` | `admin_post_cmn_candidate_request_action` | `handle_candidate_request_action` | `909` | `96089` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_booking_chat_post` | `handle_booking_chat_post` | `910` | `96743` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_booking_chat_ack` | `handle_booking_chat_ack` | `911` | `97133` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_staff_update_candidate_pay` | `handle_staff_update_candidate_pay` | `919` | `97156` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_open_booking_thread` | `handle_open_booking_thread` | `920` | `96497` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_set_booking_thread_status` | `handle_set_booking_thread_status` | `921` | `96589` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_staff_save_candidate_role_rates` | `handle_staff_save_candidate_role_rates` | `922` | `97236` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_staff_review_candidate_doc` | `handle_staff_review_candidate_doc` | `923` | `94371` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_staff_delete_candidate_doc` | `handle_staff_delete_candidate_doc` | `924` | `94272` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_staff_set_candidate_doc_visibility` | `handle_staff_set_candidate_doc_visibility` | `925` | `94236` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_update_school_assignments` | `handle_update_school_assignments` | `926` | `99501` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_school_update_profile` | `handle_school_update_profile` | `927` | `99572` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_school_add_team_member` | `handle_school_add_team_member` | `928` | `99700` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_school_create_long_booking` | `handle_school_create_long_booking` | `929` | `95873` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_school_template_defaults_save` | `handle_school_template_defaults_save` | `930` | `99524` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_update_candidate_rate` | `handle_update_candidate_rate` | `931` | `99881` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_update_status` | `handle_update_status` | `932` | `97768` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_add_candidate_internal_note` | `handle_add_candidate_internal_note` | `933` | `29947` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_staff_candidate_compliance_decision` | `handle_staff_candidate_compliance_decision` | `934` | `94085` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_save_release_version` | `handle_save_release_version` | `935` | `43409` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_save_staff_availability_settings` | `handle_save_staff_availability_settings` | `936` | `43333` | `yes` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_save_school_team_user_limit` | `handle_save_school_team_user_limit` | `937` | `43305` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_save_converter_settings` | `handle_save_converter_settings` | `938` | `43447` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_email_sender_add` | `handle_email_sender_add` | `939` | `42204` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_email_sender_toggle` | `handle_email_sender_toggle` | `940` | `42267` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_email_template_assign_sender` | `handle_email_template_assign_sender` | `941` | `42355` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_email_template_save` | `handle_email_template_save` | `942` | `42498` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_email_centre_send_test` | `handle_email_centre_send_test` | `943` | `42852` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_email_log_resend` | `handle_email_log_resend` | `944` | `26101` | `yes` | `required` | `yes` | `yes` | `portal.staff.view` | `no` |
| `admin_post` | `admin_post_cmn_regenerate_marketing_runner_token` | `handle_regenerate_marketing_runner_token` | `945` | `43503` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_marketing_runner` | `handle_marketing_runner` | `947` | `43529` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `yes` |
| `admin_post` | `admin_post_cmn_ready_response_save` | `handle_ready_response_save` | `948` | `94472` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_ready_response_delete` | `handle_ready_response_delete` | `949` | `94544` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_save_automation_rule` | `handle_save_automation_rule` | `950` | `86222` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_toggle_automation_rule` | `handle_toggle_automation_rule` | `951` | `86364` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_export_automation_logs` | `handle_export_automation_logs` | `952` | `86390` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_run_automation_smoke_test` | `handle_run_automation_smoke_test` | `953` | `86443` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_export_data_integrity_csv` | `handle_export_data_integrity_csv` | `954` | `32519` | `no` | `not_applicable` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_export_finance_invoices_csv` | `handle_export_finance_invoices_csv` | `955` | `32552` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_export_partner_programme_summary_csv` | `handle_export_partner_programme_summary_csv` | `956` | `32681` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_generate_monthly_invoices` | `handle_generate_monthly_invoices` | `957` | `38162` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_run_monthly_invoice_job_now` | `handle_run_monthly_invoice_job_now` | `958` | `37827` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_run_invoice_overdue_reminder_job_now` | `handle_run_invoice_overdue_reminder_job_now` | `959` | `37890` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_update_invoice_school_reminder_opt_out` | `handle_update_invoice_school_reminder_opt_out` | `960` | `37938` | `yes` | `not_applicable` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_run_candidate_rewards_reset_now` | `handle_run_candidate_rewards_reset_now` | `961` | `37984` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_run_rewards_payroll_addon_processor` | `handle_run_rewards_payroll_addon_processor` | `962` | `38037` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_mark_rewards_addons_processed` | `handle_mark_rewards_addons_processed` | `963` | `38104` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_confirm_candidate_no_show` | `handle_confirm_candidate_no_show` | `964` | `96610` | `no` | `required` | `no` | `yes` | `implicit staff guard` | `no` |
| `admin_post` | `admin_post_cmn_invoice_change_status` | `handle_invoice_change_status` | `965` | `38265` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_invoice_add_adjustment` | `handle_invoice_add_adjustment` | `966` | `38303` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_invoice_record_payment` | `handle_invoice_record_payment` | `967` | `38386` | `no` | `not_applicable` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_invoice_send_email` | `handle_invoice_send_email` | `968` | `38582` | `no` | `not_applicable` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_download_invoice_pdf` | `handle_download_invoice_pdf` | `969` | `38957` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_partner_programme_recalculate` | `handle_partner_programme_recalculate` | `970` | `39042` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_partner_programme_adjust_credits` | `handle_partner_programme_adjust_credits` | `971` | `39078` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_school_partner_admin_recalculate_days` | `handle_school_partner_admin_recalculate_days` | `972` | `39126` | `yes` | `required` | `yes` | `yes` | `partner.admin.mutate` | `yes` |
| `admin_post` | `admin_post_cmn_school_partner_admin_set_tier` | `handle_school_partner_admin_set_tier` | `973` | `39176` | `yes` | `required` | `yes` | `yes` | `partner.admin.mutate` | `yes` |
| `admin_post` | `admin_post_cmn_school_partner_admin_adjust_days` | `handle_school_partner_admin_adjust_days` | `974` | `39227` | `yes` | `required` | `yes` | `yes` | `partner.admin.mutate` | `yes` |
| `admin_post` | `admin_post_cmn_school_partner_admin_create_credit` | `handle_school_partner_admin_create_credit` | `975` | `39278` | `yes` | `required` | `yes` | `yes` | `partner.admin.mutate` | `yes` |
| `admin_post` | `admin_post_cmn_school_partner_admin_void_credit` | `handle_school_partner_admin_void_credit` | `976` | `39340` | `yes` | `required` | `yes` | `yes` | `partner.admin.mutate` | `yes` |
| `admin_post` | `admin_post_cmn_reconcile_school_lists` | `handle_reconcile_school_lists` | `977` | `90640` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_staff_candidate_rewards_recalculate` | `handle_staff_candidate_rewards_recalculate` | `978` | `39391` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_staff_candidate_rewards_flag_review` | `handle_staff_candidate_rewards_flag_review` | `979` | `39448` | `no` | `required` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post` | `admin_post_cmn_staff_candidate_rewards_terminate` | `handle_staff_candidate_rewards_terminate` | `980` | `39505` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_staff_candidate_rewards_reverse_no_show` | `handle_staff_candidate_rewards_reverse_no_show` | `981` | `39567` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_staff_candidate_rewards_adjust_shifts` | `handle_staff_candidate_rewards_adjust_shifts` | `982` | `39635` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_staff_candidate_rewards_set_tier` | `handle_staff_candidate_rewards_set_tier` | `983` | `39748` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_staff_candidate_rewards_suspend` | `handle_staff_candidate_rewards_suspend` | `984` | `39851` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_staff_candidate_rewards_reinstate` | `handle_staff_candidate_rewards_reinstate` | `985` | `39929` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_staff_candidate_rewards_mark_no_show` | `handle_staff_candidate_rewards_mark_no_show` | `986` | `40021` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_staff_candidate_rewards_resolve_appeal` | `handle_staff_candidate_rewards_resolve_appeal` | `987` | `40115` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_portal_login` | `handle_portal_login` | `1001` | `101977` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_portal_logout` | `handle_portal_logout` | `1003` | `102002` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_` | `handle_portal_login_fallback_admin_post` | `1005` | `102016` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_portal_forgot_password` | `handle_portal_forgot_password` | `1007` | `102029` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_portal_reset_password` | `handle_portal_reset_password` | `1009` | `102048` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post` | `admin_post_cmn_run_upgrade_runner` | `handle_run_upgrade_runner` | `1010` | `9208` | `yes` | `required` | `yes` | `yes` | `system.upgrade.run` | `no` |
| `admin_post` | `admin_post_cmn_candidate_download_doc` | `handle_candidate_download_doc` | `1097` | `94140` | `no` | `not_applicable` | `no` | `yes` | `implicit partner guard` | `no` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_register_school` | `handle_register_school` | `803` | `88593` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_register_candidate` | `handle_register_candidate` | `810` | `88917` | `yes` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_priority_interest_register` | `handle_priority_interest_register` | `822` | `95254` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_verify_candidate_email` | `handle_verify_candidate_email` | `831` | `91909` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_resend_candidate_verification` | `handle_resend_candidate_verification` | `833` | `91921` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_confirm_booking_day` | `handle_confirm_booking_day` | `849` | `47226` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_candidate_response` | `handle_candidate_response` | `907` | `99199` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `yes` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_marketing_runner` | `handle_marketing_runner` | `946` | `43529` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `yes` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_portal_login` | `handle_portal_login` | `1000` | `101977` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_portal_logout` | `handle_portal_logout` | `1002` | `102002` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post_nopriv` | `admin_post_nopriv_` | `handle_portal_login_fallback_admin_post` | `1004` | `102016` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_portal_forgot_password` | `handle_portal_forgot_password` | `1006` | `102029` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `admin_post_nopriv` | `admin_post_nopriv_cmn_portal_reset_password` | `handle_portal_reset_password` | `1008` | `102048` | `no` | `required` | `no` | `no` | `none detected` | `no` |
| `cron` | `cmn_send_school_registration_emails` | `handle_deferred_school_registration_emails` | `808` | `88874` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `cron` | `cmn_process_school_registration_side_effects` | `handle_school_registration_side_effects` | `809` | `88832` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `cron` | `cmn_expire_bookings` | `expire_booking_requests` | `1117` | `99392` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `cron` | `cmn_compliance_reminders` | `run_compliance_reminders` | `1129` | `65597` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `cron` | `cmn_availability_nudges` | `run_availability_nudges` | `1132` | `65812` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `cron` | `cmn_automation_runner` | `run_automation_runner` | `1145` | `9046` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `shortcode` | `cmn_login` | `render_login_shortcode` | `26297` | `26312` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `shortcode` | `cmn_portal` | `render_portal_shortcode` | `26298` | `26613` | `no` | `not_applicable` | `no` | `yes` | `implicit staff guard` | `no` |
| `shortcode` | `cmn_staff_dashboard` | `render_staff_dashboard_shortcode` | `26299` | `26904` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `shortcode` | `cmn_staff_schools` | `render_staff_schools_shortcode` | `26300` | `27282` | `no` | `not_applicable` | `no` | `yes` | `implicit staff guard` | `no` |
| `shortcode` | `cmn_staff_candidates` | `render_staff_candidates_shortcode` | `26301` | `28340` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `shortcode` | `cmn_staff_bookings` | `render_staff_bookings_shortcode` | `26302` | `31674` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `shortcode` | `cmn_school_dashboard` | `render_school_dashboard_shortcode` | `26303` | `67439` | `no` | `not_applicable` | `no` | `yes` | `implicit staff guard` | `no` |
| `shortcode` | `cmn_candidate_dashboard` | `render_candidate_dashboard_shortcode` | `26304` | `69583` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `shortcode` | `cmn_register_school` | `render_register_school_shortcode` | `26305` | `66936` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `shortcode` | `cmn_register_candidate` | `render_register_candidate_shortcode` | `26306` | `67171` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `shortcode` | `cmn_request_received` | `render_request_received_shortcode` | `26307` | `67117` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `shortcode` | `cmn_school_landing` | `render_school_landing_shortcode` | `26308` | `67341` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `shortcode` | `cmn_candidate_landing` | `render_candidate_landing_shortcode` | `26309` | `67390` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `shortcode` | `cmn_available_wall` | `render_available_wall_shortcode` | `26310` | `82029` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `template_route` | `template_redirect` | `handle_portal_route_fallback` | `1119` | `15163` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `template_route` | `template_redirect` | `handle_cv_converter_api_endpoints` | `1120` | `10903` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `template_route` | `template_redirect` | `redirect_legacy_portal_paths` | `1121` | `15088` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `template_route` | `template_redirect` | `handle_marketing_unsubscribe` | `1122` | `15224` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `template_route` | `template_redirect` | `handle_automation_runner_endpoint` | `1123` | `9095` | `no` | `not_applicable` | `no` | `no` | `none detected` | `yes` |
| `template_route` | `template_redirect` | `handle_set_release_version_endpoint` | `1124` | `9161` | `yes` | `not_applicable` | `no` | `no` | `none detected` | `yes` |
| `template_route` | `template_redirect` | `handle_school_request_chat_route` | `1125` | `87901` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `template_route` | `template_redirect` | `protect_candidate_doc_attachment_access` | `1126` | `65104` | `no` | `not_applicable` | `no` | `no` | `none detected` | `no` |
| `rest` | `cmn/v1/cv/original` | `handle_rest_cv_original` | `92775` | `92846` | `no` | `rest_auth` | `no` | `no` | `none detected` | `no` |
| `rest` | `cmn/v1/cv/formatted` | `handle_rest_cv_formatted` | `92783` | `92893` | `yes` | `rest_auth` | `no` | `no` | `none detected` | `no` |
| `rest` | `cmn/v1/partner-programme/stats` | `handle_rest_partner_programme_stats` | `92791` | `92747` | `no` | `rest_auth` | `no` | `no` | `none detected` | `no` |
| `rest` | `cmn/v1/candidate/rewards/stats` | `handle_rest_candidate_rewards_stats` | `92799` | `92797` | `no` | `rest_auth` | `no` | `no` | `none detected` | `no` |

## P0 Security Gaps

- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_mark_available` -> `handle_mark_available` (`covermenowone-one.php:851`, `covermenowone-one.php:92047`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_mark_unavailable_morning` -> `handle_mark_unavailable_morning` (`covermenowone-one.php:852`, `covermenowone-one.php:92153`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_update_calendar_day` -> `handle_update_calendar_day` (`covermenowone-one.php:853`, `covermenowone-one.php:92196`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_bulk_update_calendar` -> `handle_bulk_update_calendar` (`covermenowone-one.php:856`, `covermenowone-one.php:92274`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_dismiss_candidate_tour` -> `handle_dismiss_candidate_tour` (`covermenowone-one.php:858`, `covermenowone-one.php:92391`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_save_candidate_settings` -> `handle_save_candidate_settings` (`covermenowone-one.php:860`, `covermenowone-one.php:92429`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_candidate_request_delete_account` -> `handle_candidate_request_delete_account` (`covermenowone-one.php:865`, `covermenowone-one.php:92567`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_admin_delete_candidate_account` -> `handle_admin_delete_candidate_account` (`covermenowone-one.php:866`, `covermenowone-one.php:92618`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_request_candidate` -> `handle_request_candidate` (`covermenowone-one.php:867`, `covermenowone-one.php:95535`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_school_live_match_action` -> `handle_school_live_match_action` (`covermenowone-one.php:868`, `covermenowone-one.php:95362`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_thread_post_message` -> `handle_thread_post_message` (`covermenowone-one.php:881`, `covermenowone-one.php:87620`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_thread_set_status` -> `handle_thread_set_status` (`covermenowone-one.php:885`, `covermenowone-one.php:87741`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_system_health_apply_all_safe_fixes` -> `handle_system_health_apply_all_safe_fixes` (`covermenowone-one.php:895`, `covermenowone-one.php:25727`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_system_health_apply_all_safe_fixes` -> `handle_system_health_apply_all_safe_fixes` (`covermenowone-one.php:895`, `covermenowone-one.php:25727`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_booking_feedback_submit` -> `handle_booking_feedback_submit` (`covermenowone-one.php:916`, `covermenowone-one.php:96918`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_add_staff_user` -> `handle_add_staff_user_ajax` (`covermenowone-one.php:1011`, `covermenowone-one.php:101814`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_update_staff_user` -> `handle_update_staff_user_ajax` (`covermenowone-one.php:1012`, `covermenowone-one.php:101878`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_toggle_staff_deactivated` -> `handle_toggle_staff_deactivated_ajax` (`covermenowone-one.php:1014`, `covermenowone-one.php:101944`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_support_create_ticket` -> `handle_support_create_ticket` (`covermenowone-one.php:1015`, `covermenowone-one.php:102452`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_support_save_transcript` -> `handle_support_save_transcript` (`covermenowone-one.php:1024`, `covermenowone-one.php:103519`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_support_email_transcript` -> `handle_support_email_transcript` (`covermenowone-one.php:1025`, `covermenowone-one.php:103566`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_candidate_submit_payroll_query` -> `handle_candidate_submit_payroll_query` (`covermenowone-one.php:1028`, `covermenowone-one.php:102092`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_livechat_send` -> `handle_livechat_send` (`covermenowone-one.php:1031`, `covermenowone-one.php:16848`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_livechat_feedback_submit` -> `handle_livechat_feedback_submit` (`covermenowone-one.php:1035`, `covermenowone-one.php:16995`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_candidate_upload_doc` -> `handle_candidate_upload_doc` (`covermenowone-one.php:1037`, `covermenowone-one.php:93705`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_candidate_delete_doc` -> `handle_candidate_delete_doc` (`covermenowone-one.php:1038`, `covermenowone-one.php:93865`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_candidate_remove_doc` -> `handle_candidate_delete_doc` (`covermenowone-one.php:1039`, `covermenowone-one.php:93865`)
- State mutation without required nonce: `wp_ajax:wp_ajax_cmn_save_candidate_formatted_cv` -> `handle_save_candidate_formatted_cv` (nonce=`not_applicable`) (`covermenowone-one.php:1044`, `covermenowone-one.php:93124`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_save_candidate_formatted_cv` -> `handle_save_candidate_formatted_cv` (`covermenowone-one.php:1044`, `covermenowone-one.php:93124`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_candidate_update_profile` -> `handle_candidate_update_profile` (`covermenowone-one.php:1047`, `covermenowone-one.php:93480`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_candidate_profile_photo_upload` -> `handle_candidate_profile_photo_upload` (`covermenowone-one.php:1048`, `covermenowone-one.php:93373`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_candidate_profile_photo_remove` -> `handle_candidate_profile_photo_remove` (`covermenowone-one.php:1049`, `covermenowone-one.php:93452`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_candidate_contact_card_save` -> `handle_candidate_contact_card_save` (`covermenowone-one.php:1050`, `covermenowone-one.php:93661`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_candidate_learning_opt_in` -> `handle_candidate_learning_opt_in` (`covermenowone-one.php:1051`, `covermenowone-one.php:93238`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_candidate_learning_complete_course` -> `handle_candidate_learning_complete_course` (`covermenowone-one.php:1052`, `covermenowone-one.php:93259`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_lead_finder` -> `handle_marketing_lead_finder` (`covermenowone-one.php:1056`, `covermenowone-one.php:97956`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_save_list` -> `handle_marketing_save_list` (`covermenowone-one.php:1057`, `covermenowone-one.php:98020`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_save_list` -> `handle_marketing_save_list` (`covermenowone-one.php:1057`, `covermenowone-one.php:98020`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_get_lists` -> `handle_marketing_get_lists` (`covermenowone-one.php:1058`, `covermenowone-one.php:98078`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_refresh_list` -> `handle_marketing_refresh_list` (`covermenowone-one.php:1059`, `covermenowone-one.php:98090`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_refresh_list` -> `handle_marketing_refresh_list` (`covermenowone-one.php:1059`, `covermenowone-one.php:98090`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_duplicate_list` -> `handle_marketing_duplicate_list` (`covermenowone-one.php:1060`, `covermenowone-one.php:98118`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_duplicate_list` -> `handle_marketing_duplicate_list` (`covermenowone-one.php:1060`, `covermenowone-one.php:98118`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_delete_list` -> `handle_marketing_delete_list` (`covermenowone-one.php:1061`, `covermenowone-one.php:98159`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_delete_list` -> `handle_marketing_delete_list` (`covermenowone-one.php:1061`, `covermenowone-one.php:98159`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_get_list_members` -> `handle_marketing_get_list_members` (`covermenowone-one.php:1062`, `covermenowone-one.php:98184`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_save_campaign` -> `handle_marketing_save_campaign` (`covermenowone-one.php:1063`, `covermenowone-one.php:98221`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_save_campaign` -> `handle_marketing_save_campaign` (`covermenowone-one.php:1063`, `covermenowone-one.php:98221`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_preview_campaign` -> `handle_marketing_preview_campaign` (`covermenowone-one.php:1064`, `covermenowone-one.php:97987`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_get_campaigns` -> `handle_marketing_get_campaigns` (`covermenowone-one.php:1065`, `covermenowone-one.php:98280`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_queue_campaign` -> `handle_marketing_queue_campaign` (`covermenowone-one.php:1066`, `covermenowone-one.php:98292`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_queue_campaign` -> `handle_marketing_queue_campaign` (`covermenowone-one.php:1066`, `covermenowone-one.php:98292`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_get_queue` -> `handle_marketing_get_queue` (`covermenowone-one.php:1067`, `covermenowone-one.php:98435`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_process_queue` -> `handle_marketing_process_queue` (`covermenowone-one.php:1068`, `covermenowone-one.php:98447`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_pause_campaign` -> `handle_marketing_pause_campaign` (`covermenowone-one.php:1069`, `covermenowone-one.php:98464`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_pause_campaign` -> `handle_marketing_pause_campaign` (`covermenowone-one.php:1069`, `covermenowone-one.php:98464`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_resume_campaign` -> `handle_marketing_resume_campaign` (`covermenowone-one.php:1070`, `covermenowone-one.php:98490`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_resume_campaign` -> `handle_marketing_resume_campaign` (`covermenowone-one.php:1070`, `covermenowone-one.php:98490`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_get_replies` -> `handle_marketing_get_replies` (`covermenowone-one.php:1071`, `covermenowone-one.php:98517`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_poll_replies` -> `handle_marketing_poll_replies` (`covermenowone-one.php:1072`, `covermenowone-one.php:98530`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_poll_replies` -> `handle_marketing_poll_replies` (`covermenowone-one.php:1072`, `covermenowone-one.php:98530`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_save_template` -> `handle_marketing_save_template` (`covermenowone-one.php:1073`, `covermenowone-one.php:98576`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_save_template` -> `handle_marketing_save_template` (`covermenowone-one.php:1073`, `covermenowone-one.php:98576`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_get_templates` -> `handle_marketing_get_templates` (`covermenowone-one.php:1074`, `covermenowone-one.php:98615`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_delete_template` -> `handle_marketing_delete_template` (`covermenowone-one.php:1075`, `covermenowone-one.php:98625`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_delete_template` -> `handle_marketing_delete_template` (`covermenowone-one.php:1075`, `covermenowone-one.php:98625`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_test_send` -> `handle_marketing_test_send` (`covermenowone-one.php:1076`, `covermenowone-one.php:98641`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_get_send_log` -> `handle_marketing_get_send_log` (`covermenowone-one.php:1077`, `covermenowone-one.php:98669`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_get_unsubscribes` -> `handle_marketing_get_unsubscribes` (`covermenowone-one.php:1078`, `covermenowone-one.php:98807`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_add_unsubscribe` -> `handle_marketing_add_unsubscribe` (`covermenowone-one.php:1079`, `covermenowone-one.php:98822`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_remove_unsubscribe` -> `handle_marketing_remove_unsubscribe` (`covermenowone-one.php:1080`, `covermenowone-one.php:98837`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_remove_unsubscribe` -> `handle_marketing_remove_unsubscribe` (`covermenowone-one.php:1080`, `covermenowone-one.php:98837`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_get_overview` -> `handle_marketing_get_overview` (`covermenowone-one.php:1081`, `covermenowone-one.php:98548`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_save_segment` -> `handle_marketing_save_segment` (`covermenowone-one.php:1082`, `covermenowone-one.php:98853`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_save_segment` -> `handle_marketing_save_segment` (`covermenowone-one.php:1082`, `covermenowone-one.php:98853`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_get_segments` -> `handle_marketing_get_segments` (`covermenowone-one.php:1083`, `covermenowone-one.php:98893`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_run_segment` -> `handle_marketing_run_segment` (`covermenowone-one.php:1084`, `covermenowone-one.php:98903`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_segment_to_list` -> `handle_marketing_segment_to_list` (`covermenowone-one.php:1085`, `covermenowone-one.php:98924`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_segment_to_list` -> `handle_marketing_segment_to_list` (`covermenowone-one.php:1085`, `covermenowone-one.php:98924`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_save_quick_campaign` -> `handle_marketing_save_quick_campaign` (`covermenowone-one.php:1086`, `covermenowone-one.php:98975`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_save_quick_campaign` -> `handle_marketing_save_quick_campaign` (`covermenowone-one.php:1086`, `covermenowone-one.php:98975`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_get_quick_campaigns` -> `handle_marketing_get_quick_campaigns` (`covermenowone-one.php:1087`, `covermenowone-one.php:99026`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_run_quick_campaign` -> `handle_marketing_run_quick_campaign` (`covermenowone-one.php:1088`, `covermenowone-one.php:99036`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_run_quick_campaign` -> `handle_marketing_run_quick_campaign` (`covermenowone-one.php:1088`, `covermenowone-one.php:99036`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_export_send_log` -> `handle_marketing_export_send_log` (`covermenowone-one.php:1089`, `covermenowone-one.php:98717`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_resend_failed` -> `handle_marketing_resend_failed` (`covermenowone-one.php:1090`, `covermenowone-one.php:98768`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_resend_failed` -> `handle_marketing_resend_failed` (`covermenowone-one.php:1090`, `covermenowone-one.php:98768`)
- State mutation without guard/capability signal: `wp_ajax:wp_ajax_cmn_marketing_save_settings` -> `handle_marketing_save_settings` (`covermenowone-one.php:1091`, `covermenowone-one.php:98561`)
- Staff/privileged surface without explicit staff/partner ability signal: `wp_ajax:wp_ajax_cmn_marketing_save_settings` -> `handle_marketing_save_settings` (`covermenowone-one.php:1091`, `covermenowone-one.php:98561`)
- Public nopriv state mutation: `wp_ajax_nopriv:wp_ajax_nopriv_cmn_thread_post_message` -> `handle_thread_post_message` (`covermenowone-one.php:882`, `covermenowone-one.php:87620`)
- State mutation without guard/capability signal: `wp_ajax_nopriv:wp_ajax_nopriv_cmn_thread_post_message` -> `handle_thread_post_message` (`covermenowone-one.php:882`, `covermenowone-one.php:87620`)
- Public nopriv state mutation: `wp_ajax_nopriv:wp_ajax_nopriv_cmn_livechat_start` -> `handle_livechat_start` (`covermenowone-one.php:1030`, `covermenowone-one.php:16651`)
- Public nopriv state mutation: `wp_ajax_nopriv:wp_ajax_nopriv_cmn_livechat_send` -> `handle_livechat_send` (`covermenowone-one.php:1032`, `covermenowone-one.php:16848`)
- State mutation without guard/capability signal: `wp_ajax_nopriv:wp_ajax_nopriv_cmn_livechat_send` -> `handle_livechat_send` (`covermenowone-one.php:1032`, `covermenowone-one.php:16848`)
- Public nopriv state mutation: `wp_ajax_nopriv:wp_ajax_nopriv_cmn_livechat_feedback_submit` -> `handle_livechat_feedback_submit` (`covermenowone-one.php:1036`, `covermenowone-one.php:16995`)
- State mutation without guard/capability signal: `wp_ajax_nopriv:wp_ajax_nopriv_cmn_livechat_feedback_submit` -> `handle_livechat_feedback_submit` (`covermenowone-one.php:1036`, `covermenowone-one.php:16995`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_save_admin_recipient_email` -> `handle_save_admin_recipient_email` (`covermenowone-one.php:802`, `covermenowone-one.php:43279`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_register_school` -> `handle_register_school` (`covermenowone-one.php:804`, `covermenowone-one.php:88593`)
- State mutation without required nonce: `admin_post:admin_post_cmn_process_school_request` -> `handle_process_school_request` (nonce=`not_applicable`) (`covermenowone-one.php:805`, `covermenowone-one.php:88142`)
- State mutation without required nonce: `admin_post:admin_post_cmn_approve_school_request` -> `handle_approve_school_request` (nonce=`not_applicable`) (`covermenowone-one.php:806`, `covermenowone-one.php:88328`)
- State mutation without required nonce: `admin_post:admin_post_cmn_reject_school_request` -> `handle_reject_school_request` (nonce=`not_applicable`) (`covermenowone-one.php:807`, `covermenowone-one.php:88372`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_register_candidate` -> `handle_register_candidate` (`covermenowone-one.php:811`, `covermenowone-one.php:88917`)
- State mutation without required nonce: `admin_post:admin_post_cmn_mark_school_import_resolved` -> `handle_mark_school_import_resolved` (nonce=`not_applicable`) (`covermenowone-one.php:815`, `covermenowone-one.php:103917`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_add_staff` -> `handle_add_staff_portal` (`covermenowone-one.php:817`, `covermenowone-one.php:101768`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_school_rebook_candidate` -> `handle_school_rebook_candidate` (`covermenowone-one.php:819`, `covermenowone-one.php:95729`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_priority_allocation_send` -> `handle_priority_allocation_send` (`covermenowone-one.php:820`, `covermenowone-one.php:95084`)
- State mutation without required nonce: `admin_post:admin_post_cmn_priority_interest_register` -> `handle_priority_interest_register` (nonce=`not_applicable`) (`covermenowone-one.php:821`, `covermenowone-one.php:95254`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_priority_interest_register` -> `handle_priority_interest_register` (`covermenowone-one.php:821`, `covermenowone-one.php:95254`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_send_emergency_broadcast` -> `handle_send_emergency_broadcast` (`covermenowone-one.php:823`, `covermenowone-one.php:30348`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_toggle_availability` -> `handle_toggle_availability` (`covermenowone-one.php:835`, `covermenowone-one.php:91942`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_save_calendar` -> `handle_save_calendar` (`covermenowone-one.php:836`, `covermenowone-one.php:91997`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_save_candidate_bank_details` -> `handle_save_candidate_bank_details` (`covermenowone-one.php:837`, `covermenowone-one.php:74267`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_candidate_accept_compliance_ack` -> `handle_candidate_accept_compliance_ack` (`covermenowone-one.php:838`, `covermenowone-one.php:74484`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_payroll_adjustment_create` -> `handle_payroll_adjustment_create` (`covermenowone-one.php:844`, `covermenowone-one.php:75028`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_payroll_adjustment_decision` -> `handle_payroll_adjustment_decision` (`covermenowone-one.php:845`, `covermenowone-one.php:75171`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_mark_pay_approval_disputed` -> `handle_mark_pay_approval_disputed` (`covermenowone-one.php:846`, `covermenowone-one.php:75300`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_staff_payroll_ticket_quick_action` -> `handle_staff_payroll_ticket_quick_action` (`covermenowone-one.php:847`, `covermenowone-one.php:75425`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_staff_payroll_ticket_quick_action` -> `handle_staff_payroll_ticket_quick_action` (`covermenowone-one.php:847`, `covermenowone-one.php:75425`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_create_booking` -> `handle_create_booking` (`covermenowone-one.php:848`, `covermenowone-one.php:97664`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_send_candidate_invite` -> `handle_send_candidate_invite` (`covermenowone-one.php:906`, `covermenowone-one.php:99139`)
- State mutation without required nonce: `admin_post:admin_post_cmn_candidate_response` -> `handle_candidate_response` (nonce=`not_applicable`) (`covermenowone-one.php:908`, `covermenowone-one.php:99199`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_candidate_response` -> `handle_candidate_response` (`covermenowone-one.php:908`, `covermenowone-one.php:99199`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_candidate_request_action` -> `handle_candidate_request_action` (`covermenowone-one.php:909`, `covermenowone-one.php:96089`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_booking_chat_ack` -> `handle_booking_chat_ack` (`covermenowone-one.php:911`, `covermenowone-one.php:97133`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_update_school_assignments` -> `handle_update_school_assignments` (`covermenowone-one.php:926`, `covermenowone-one.php:99501`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_school_add_team_member` -> `handle_school_add_team_member` (`covermenowone-one.php:928`, `covermenowone-one.php:99700`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_school_create_long_booking` -> `handle_school_create_long_booking` (`covermenowone-one.php:929`, `covermenowone-one.php:95873`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_update_candidate_rate` -> `handle_update_candidate_rate` (`covermenowone-one.php:931`, `covermenowone-one.php:99881`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_save_school_team_user_limit` -> `handle_save_school_team_user_limit` (`covermenowone-one.php:937`, `covermenowone-one.php:43305`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_save_converter_settings` -> `handle_save_converter_settings` (`covermenowone-one.php:938`, `covermenowone-one.php:43447`)
- State mutation without required nonce: `admin_post:admin_post_cmn_email_sender_toggle` -> `handle_email_sender_toggle` (nonce=`not_applicable`) (`covermenowone-one.php:940`, `covermenowone-one.php:42267`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_email_sender_toggle` -> `handle_email_sender_toggle` (`covermenowone-one.php:940`, `covermenowone-one.php:42267`)
- State mutation without required nonce: `admin_post:admin_post_cmn_email_template_assign_sender` -> `handle_email_template_assign_sender` (nonce=`not_applicable`) (`covermenowone-one.php:941`, `covermenowone-one.php:42355`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_email_template_assign_sender` -> `handle_email_template_assign_sender` (`covermenowone-one.php:941`, `covermenowone-one.php:42355`)
- State mutation without required nonce: `admin_post:admin_post_cmn_email_template_save` -> `handle_email_template_save` (nonce=`not_applicable`) (`covermenowone-one.php:942`, `covermenowone-one.php:42498`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_email_template_save` -> `handle_email_template_save` (`covermenowone-one.php:942`, `covermenowone-one.php:42498`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_regenerate_marketing_runner_token` -> `handle_regenerate_marketing_runner_token` (`covermenowone-one.php:945`, `covermenowone-one.php:43503`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_regenerate_marketing_runner_token` -> `handle_regenerate_marketing_runner_token` (`covermenowone-one.php:945`, `covermenowone-one.php:43503`)
- State mutation without required nonce: `admin_post:admin_post_cmn_marketing_runner` -> `handle_marketing_runner` (nonce=`not_applicable`) (`covermenowone-one.php:947`, `covermenowone-one.php:43529`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_marketing_runner` -> `handle_marketing_runner` (`covermenowone-one.php:947`, `covermenowone-one.php:43529`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_marketing_runner` -> `handle_marketing_runner` (`covermenowone-one.php:947`, `covermenowone-one.php:43529`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_ready_response_save` -> `handle_ready_response_save` (`covermenowone-one.php:948`, `covermenowone-one.php:94472`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_ready_response_delete` -> `handle_ready_response_delete` (`covermenowone-one.php:949`, `covermenowone-one.php:94544`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_save_automation_rule` -> `handle_save_automation_rule` (`covermenowone-one.php:950`, `covermenowone-one.php:86222`)
- State mutation without required nonce: `admin_post:admin_post_cmn_toggle_automation_rule` -> `handle_toggle_automation_rule` (nonce=`not_applicable`) (`covermenowone-one.php:951`, `covermenowone-one.php:86364`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_toggle_automation_rule` -> `handle_toggle_automation_rule` (`covermenowone-one.php:951`, `covermenowone-one.php:86364`)
- State mutation without guard/capability signal: `admin_post:admin_post_cmn_run_automation_smoke_test` -> `handle_run_automation_smoke_test` (`covermenowone-one.php:953`, `covermenowone-one.php:86443`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_export_partner_programme_summary_csv` -> `handle_export_partner_programme_summary_csv` (`covermenowone-one.php:956`, `covermenowone-one.php:32681`)
- State mutation without required nonce: `admin_post:admin_post_cmn_update_invoice_school_reminder_opt_out` -> `handle_update_invoice_school_reminder_opt_out` (nonce=`not_applicable`) (`covermenowone-one.php:960`, `covermenowone-one.php:37938`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_partner_programme_adjust_credits` -> `handle_partner_programme_adjust_credits` (`covermenowone-one.php:971`, `covermenowone-one.php:39078`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_staff_candidate_rewards_terminate` -> `handle_staff_candidate_rewards_terminate` (`covermenowone-one.php:980`, `covermenowone-one.php:39505`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_staff_candidate_rewards_reverse_no_show` -> `handle_staff_candidate_rewards_reverse_no_show` (`covermenowone-one.php:981`, `covermenowone-one.php:39567`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_staff_candidate_rewards_adjust_shifts` -> `handle_staff_candidate_rewards_adjust_shifts` (`covermenowone-one.php:982`, `covermenowone-one.php:39635`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_staff_candidate_rewards_set_tier` -> `handle_staff_candidate_rewards_set_tier` (`covermenowone-one.php:983`, `covermenowone-one.php:39748`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_staff_candidate_rewards_suspend` -> `handle_staff_candidate_rewards_suspend` (`covermenowone-one.php:984`, `covermenowone-one.php:39851`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_staff_candidate_rewards_reinstate` -> `handle_staff_candidate_rewards_reinstate` (`covermenowone-one.php:985`, `covermenowone-one.php:39929`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_staff_candidate_rewards_mark_no_show` -> `handle_staff_candidate_rewards_mark_no_show` (`covermenowone-one.php:986`, `covermenowone-one.php:40021`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post:admin_post_cmn_staff_candidate_rewards_resolve_appeal` -> `handle_staff_candidate_rewards_resolve_appeal` (`covermenowone-one.php:987`, `covermenowone-one.php:40115`)
- Public nopriv state mutation: `admin_post_nopriv:admin_post_nopriv_cmn_register_school` -> `handle_register_school` (`covermenowone-one.php:803`, `covermenowone-one.php:88593`)
- State mutation without guard/capability signal: `admin_post_nopriv:admin_post_nopriv_cmn_register_school` -> `handle_register_school` (`covermenowone-one.php:803`, `covermenowone-one.php:88593`)
- Public nopriv state mutation: `admin_post_nopriv:admin_post_nopriv_cmn_register_candidate` -> `handle_register_candidate` (`covermenowone-one.php:810`, `covermenowone-one.php:88917`)
- State mutation without guard/capability signal: `admin_post_nopriv:admin_post_nopriv_cmn_register_candidate` -> `handle_register_candidate` (`covermenowone-one.php:810`, `covermenowone-one.php:88917`)
- Public nopriv state mutation: `admin_post_nopriv:admin_post_nopriv_cmn_priority_interest_register` -> `handle_priority_interest_register` (`covermenowone-one.php:822`, `covermenowone-one.php:95254`)
- State mutation without required nonce: `admin_post_nopriv:admin_post_nopriv_cmn_priority_interest_register` -> `handle_priority_interest_register` (nonce=`not_applicable`) (`covermenowone-one.php:822`, `covermenowone-one.php:95254`)
- State mutation without guard/capability signal: `admin_post_nopriv:admin_post_nopriv_cmn_priority_interest_register` -> `handle_priority_interest_register` (`covermenowone-one.php:822`, `covermenowone-one.php:95254`)
- Public nopriv state mutation: `admin_post_nopriv:admin_post_nopriv_cmn_candidate_response` -> `handle_candidate_response` (`covermenowone-one.php:907`, `covermenowone-one.php:99199`)
- State mutation without required nonce: `admin_post_nopriv:admin_post_nopriv_cmn_candidate_response` -> `handle_candidate_response` (nonce=`not_applicable`) (`covermenowone-one.php:907`, `covermenowone-one.php:99199`)
- State mutation without guard/capability signal: `admin_post_nopriv:admin_post_nopriv_cmn_candidate_response` -> `handle_candidate_response` (`covermenowone-one.php:907`, `covermenowone-one.php:99199`)
- Public nopriv state mutation: `admin_post_nopriv:admin_post_nopriv_cmn_marketing_runner` -> `handle_marketing_runner` (`covermenowone-one.php:946`, `covermenowone-one.php:43529`)
- State mutation without required nonce: `admin_post_nopriv:admin_post_nopriv_cmn_marketing_runner` -> `handle_marketing_runner` (nonce=`not_applicable`) (`covermenowone-one.php:946`, `covermenowone-one.php:43529`)
- State mutation without guard/capability signal: `admin_post_nopriv:admin_post_nopriv_cmn_marketing_runner` -> `handle_marketing_runner` (`covermenowone-one.php:946`, `covermenowone-one.php:43529`)
- Staff/privileged surface without explicit staff/partner ability signal: `admin_post_nopriv:admin_post_nopriv_cmn_marketing_runner` -> `handle_marketing_runner` (`covermenowone-one.php:946`, `covermenowone-one.php:43529`)
- State mutation without required nonce: `template_route:template_redirect` -> `handle_marketing_unsubscribe` (nonce=`not_applicable`) (`covermenowone-one.php:1122`, `covermenowone-one.php:15224`)
- State mutation without guard/capability signal: `template_route:template_redirect` -> `handle_marketing_unsubscribe` (`covermenowone-one.php:1122`, `covermenowone-one.php:15224`)
- Staff/privileged surface without explicit staff/partner ability signal: `template_route:template_redirect` -> `handle_marketing_unsubscribe` (`covermenowone-one.php:1122`, `covermenowone-one.php:15224`)
- State mutation without required nonce: `template_route:template_redirect` -> `handle_set_release_version_endpoint` (nonce=`not_applicable`) (`covermenowone-one.php:1124`, `covermenowone-one.php:9161`)
- State mutation without guard/capability signal: `template_route:template_redirect` -> `handle_set_release_version_endpoint` (`covermenowone-one.php:1124`, `covermenowone-one.php:9161`)
- State mutation without required nonce: `rest:cmn/v1/cv/formatted` -> `handle_rest_cv_formatted` (nonce=`rest_auth`) (`covermenowone-one.php:92783`, `covermenowone-one.php:92893`)
- State mutation without guard/capability signal: `rest:cmn/v1/cv/formatted` -> `handle_rest_cv_formatted` (`covermenowone-one.php:92783`, `covermenowone-one.php:92893`)
- Staff/privileged surface without explicit staff/partner ability signal: `rest:cmn/v1/partner-programme/stats` -> `handle_rest_partner_programme_stats` (`covermenowone-one.php:92791`, `covermenowone-one.php:92747`)

## P0 Performance Gaps

- Legacy notifications polling fallback (`window.setInterval`) remains when poll manager absent (`frontend.js:4851`).
- Legacy support realtime loop uses recurring timeout every 3s when heartbeat unavailable (`frontend.js:6733` to `frontend.js:6743`).
- Legacy account-manager badge polling loop runs every 15s when heartbeat unavailable (`frontend.js:6868` to `frontend.js:6877`).
- Legacy staff lounge polling loop runs every 5s when heartbeat unavailable (`frontend.js:7102` to `frontend.js:7111`).
- Legacy booking chat polling loop runs every 5s when heartbeat unavailable (`frontend.js:8895` to `frontend.js:8907`).
- Heartbeat fallback has its own 4s `setInterval` path when poll manager absent (`frontend.js:947` to `frontend.js:952`).

## Deploy Gaps

- SSH/SFTP transport disables host-key verification (`deploy_portal.sh:154` to `deploy_portal.sh:155`).
- Deploy script can source interactive password file/variable via `sshpass` (`deploy_portal.sh:161` to `deploy_portal.sh:169`).
- Release endpoint token update is optional and skipped when token is absent (`deploy_portal.sh:255` to `deploy_portal.sh:260`).

---
_Static classification notes: endpoint mutability/nonce/guard/rate-limit are best-effort from handler body patterns in `covermenowone-one.php`._
