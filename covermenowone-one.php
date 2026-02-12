<?php
/**
 * Plugin Name: CoverMeNow ONE
 * Description: CRM + portal for schools and candidates.
 * Version: 0.1.1
 * Author: CoverMeNow
 */

if (!defined('ABSPATH')) {
    exit;
}

final class CMN_One_Plugin {
    const VERSION = '0.1.1';
    const SCHEMA_VERSION = 13;
    const EMAIL_CANDIDATE_DECLINED = false;

    public function __construct() {
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_roles']);
        add_action('init', [$this, 'register_shortcodes']);
        add_action('init', [$this, 'ensure_required_pages']);
        add_action('init', [$this, 'maybe_upgrade_schema']);
        add_action('init', [$this, 'maybe_auto_bump_portal_release_version'], 15);
        add_action('init', [$this, 'migrate_candidate_statuses_to_approved'], 20);
        add_action('init', [$this, 'configure_candidate_upload_runtime_limits'], 1);
        add_action('add_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('save_post_cmn_school', [$this, 'save_school_meta']);
        add_action('save_post_cmn_candidate', [$this, 'save_candidate_meta']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_filter('body_class', [$this, 'add_portal_body_class']);
        add_action('admin_post_cmn_add_activity', [$this, 'handle_add_activity']);
        add_action('admin_post_cmn_complete_activity', [$this, 'handle_complete_activity']);
        add_action('admin_post_nopriv_cmn_register_school', [$this, 'handle_register_school']);
        add_action('admin_post_cmn_register_school', [$this, 'handle_register_school']);
        add_action('admin_post_nopriv_cmn_register_candidate', [$this, 'handle_register_candidate']);
        add_action('admin_post_cmn_register_candidate', [$this, 'handle_register_candidate']);
        add_action('admin_post_cmn_import_schools', [$this, 'handle_import_schools_portal']);
        add_action('admin_post_cmn_bulk_schools', [$this, 'handle_bulk_schools']);
        add_action('admin_post_cmn_add_school', [$this, 'handle_add_school_portal']);
        add_action('admin_post_cmn_add_staff', [$this, 'handle_add_staff_portal']);
        add_action('admin_post_cmn_assign_account_manager', [$this, 'handle_assign_account_manager']);
        add_action('admin_post_cmn_add_contact', [$this, 'handle_add_contact_portal']);
        add_action('admin_post_cmn_update_contact', [$this, 'handle_update_contact_portal']);
        add_action('admin_post_cmn_delete_contact', [$this, 'handle_delete_contact_portal']);
        add_action('admin_post_cmn_import_contacts', [$this, 'handle_import_contacts_portal']);
        add_action('admin_post_cmn_assign_contact_to_school', [$this, 'handle_assign_contact_to_school']);
        add_action('admin_post_cmn_unassign_contact', [$this, 'handle_unassign_contact']);
        add_action('admin_post_cmn_convert_client', [$this, 'handle_convert_client']);
        add_action('admin_post_nopriv_cmn_verify_candidate_email', [$this, 'handle_verify_candidate_email']);
        add_action('admin_post_cmn_verify_candidate_email', [$this, 'handle_verify_candidate_email']);
        add_action('admin_post_nopriv_cmn_resend_candidate_verification', [$this, 'handle_resend_candidate_verification']);
        add_action('admin_post_cmn_resend_candidate_verification', [$this, 'handle_resend_candidate_verification']);
        add_action('admin_post_nopriv_cmn_toggle_availability', [$this, 'handle_toggle_availability']);
        add_action('admin_post_cmn_toggle_availability', [$this, 'handle_toggle_availability']);
        add_action('admin_post_nopriv_cmn_save_calendar', [$this, 'handle_save_calendar']);
        add_action('admin_post_cmn_save_calendar', [$this, 'handle_save_calendar']);
        add_action('admin_post_nopriv_cmn_create_booking', [$this, 'handle_create_booking']);
        add_action('admin_post_cmn_create_booking', [$this, 'handle_create_booking']);
        add_action('wp_ajax_cmn_mark_available', [$this, 'handle_mark_available']);
        add_action('wp_ajax_cmn_update_calendar_day', [$this, 'handle_update_calendar_day']);
        add_action('wp_ajax_cmn_get_calendar_availability', [$this, 'handle_get_calendar_availability']);
        add_action('wp_ajax_cmn_bulk_update_calendar', [$this, 'handle_bulk_update_calendar']);
        add_action('wp_ajax_cmn_clear_calendar', [$this, 'handle_clear_calendar']);
        add_action('wp_ajax_cmn_dismiss_candidate_tour', [$this, 'handle_dismiss_candidate_tour']);
        add_action('wp_ajax_cmn_get_candidate_settings', [$this, 'handle_get_candidate_settings']);
        add_action('wp_ajax_cmn_save_candidate_settings', [$this, 'handle_save_candidate_settings']);
        add_action('wp_ajax_cmn_get_theme_settings', [$this, 'handle_get_theme_settings']);
        add_action('wp_ajax_cmn_save_theme_settings', [$this, 'handle_save_theme_settings']);
        add_action('wp_ajax_cmn_candidate_request_delete_account', [$this, 'handle_candidate_request_delete_account']);
        add_action('wp_ajax_cmn_admin_delete_candidate_account', [$this, 'handle_admin_delete_candidate_account']);
        add_action('wp_ajax_cmn_request_candidate', [$this, 'handle_request_candidate']);
        add_action('wp_ajax_cmn_mark_notifications_read', [$this, 'handle_mark_notifications_read']);
        add_action('wp_ajax_cmn_notifications_mark_all_read', [$this, 'handle_notifications_mark_all_read']);
        add_action('wp_ajax_cmn_notifications_clear_all', [$this, 'handle_notifications_clear_all']);
        add_action('wp_ajax_cmn_notifications_mark_read', [$this, 'handle_notifications_mark_read']);
        add_action('wp_ajax_cmn_send_test_emails', [$this, 'handle_send_test_emails']);
        add_action('admin_post_cmn_update_candidate_request', [$this, 'handle_update_candidate_request']);
        add_action('admin_post_cmn_send_candidate_invite', [$this, 'handle_send_candidate_invite']);
        add_action('admin_post_nopriv_cmn_candidate_response', [$this, 'handle_candidate_response']);
        add_action('admin_post_cmn_candidate_response', [$this, 'handle_candidate_response']);
        add_action('admin_post_cmn_candidate_request_action', [$this, 'handle_candidate_request_action']);
        add_action('admin_post_cmn_booking_chat_post', [$this, 'handle_booking_chat_post']);
        add_action('admin_post_cmn_booking_chat_ack', [$this, 'handle_booking_chat_ack']);
        add_action('wp_ajax_cmn_booking_chat_fetch', [$this, 'handle_booking_chat_fetch']);
        add_action('wp_ajax_cmn_booking_feedback_fetch', [$this, 'handle_booking_feedback_fetch']);
        add_action('wp_ajax_cmn_booking_feedback_submit', [$this, 'handle_booking_feedback_submit']);
        add_action('admin_post_cmn_staff_update_candidate_pay', [$this, 'handle_staff_update_candidate_pay']);
        add_action('admin_post_cmn_staff_review_candidate_doc', [$this, 'handle_staff_review_candidate_doc']);
        add_action('admin_post_cmn_update_school_assignments', [$this, 'handle_update_school_assignments']);
        add_action('admin_post_cmn_update_candidate_rate', [$this, 'handle_update_candidate_rate']);
        add_action('admin_post_cmn_update_status', [$this, 'handle_update_status']);
        add_action('admin_post_cmn_ready_response_save', [$this, 'handle_ready_response_save']);
        add_action('admin_post_cmn_ready_response_delete', [$this, 'handle_ready_response_delete']);
        add_action('wp_login', [$this, 'record_user_login'], 10, 2);
        add_filter('login_redirect', [$this, 'handle_login_redirect'], 10, 3);
        add_filter('logout_redirect', [$this, 'handle_logout_redirect'], 10, 3);
        add_action('admin_init', [$this, 'block_wp_admin_for_non_admins']);
        add_action('login_init', [$this, 'redirect_wp_login_for_portal_users']);
        add_filter('show_admin_bar', [$this, 'maybe_hide_admin_bar']);
        add_action('login_enqueue_scripts', [$this, 'enqueue_login_branding']);
        add_filter('login_headerurl', [$this, 'login_header_url']);
        add_filter('login_headertext', [$this, 'login_header_text']);
        add_filter('login_message', [$this, 'login_brand_message']);
        add_action('login_footer', [$this, 'login_footer_link']);
        add_action('admin_post_nopriv_cmn_portal_login', [$this, 'handle_portal_login']);
        add_action('admin_post_cmn_portal_login', [$this, 'handle_portal_login']);
        add_action('admin_post_nopriv_cmn_portal_forgot_password', [$this, 'handle_portal_forgot_password']);
        add_action('admin_post_cmn_portal_forgot_password', [$this, 'handle_portal_forgot_password']);
        add_action('admin_post_nopriv_cmn_portal_reset_password', [$this, 'handle_portal_reset_password']);
        add_action('admin_post_cmn_portal_reset_password', [$this, 'handle_portal_reset_password']);
        add_action('wp_ajax_cmn_add_staff_user', [$this, 'handle_add_staff_user_ajax']);
        add_action('wp_ajax_cmn_update_staff_user', [$this, 'handle_update_staff_user_ajax']);
        add_action('wp_ajax_cmn_send_staff_reset_password', [$this, 'handle_send_staff_reset_password_ajax']);
        add_action('wp_ajax_cmn_toggle_staff_deactivated', [$this, 'handle_toggle_staff_deactivated_ajax']);
        add_action('wp_ajax_cmn_support_create_ticket', [$this, 'handle_support_create_ticket']);
        add_action('wp_ajax_cmn_support_list_tickets', [$this, 'handle_support_list_tickets']);
        add_action('wp_ajax_support_list_tickets_unfiltered_admin', [$this, 'handle_support_list_tickets_unfiltered_admin']);
        add_action('wp_ajax_cmn_support_list_tickets_unfiltered_admin', [$this, 'handle_support_list_tickets_unfiltered_admin']);
        add_action('wp_ajax_cmn_support_get_ticket', [$this, 'handle_support_get_ticket']);
        add_action('wp_ajax_cmn_support_post_message', [$this, 'handle_support_post_message']);
        add_action('wp_ajax_cmn_support_close_ticket', [$this, 'handle_support_close_ticket']);
        add_action('wp_ajax_cmn_support_submit_feedback', [$this, 'handle_support_submit_feedback']);
        add_action('wp_ajax_cmn_support_save_transcript', [$this, 'handle_support_save_transcript']);
        add_action('wp_ajax_cmn_support_email_transcript', [$this, 'handle_support_email_transcript']);
        add_action('wp_ajax_cmn_candidate_upload_doc', [$this, 'handle_candidate_upload_doc']);
        add_action('wp_ajax_cmn_candidate_delete_doc', [$this, 'handle_candidate_delete_doc']);
        add_action('wp_ajax_cmn_candidate_remove_doc', [$this, 'handle_candidate_delete_doc']);
        add_action('wp_ajax_cmn_candidate_get_doc', [$this, 'handle_candidate_get_doc']);
        add_action('wp_ajax_cmn_get_compliance_status', [$this, 'handle_get_compliance_status']);
        add_action('wp_ajax_cmn_generate_cv_converter_token', [$this, 'handle_generate_cv_converter_token']);
        add_action('wp_ajax_cmn_get_candidate_original_cv', [$this, 'handle_get_candidate_original_cv']);
        add_action('wp_ajax_cmn_save_candidate_formatted_cv', [$this, 'handle_save_candidate_formatted_cv']);
        add_action('wp_ajax_cmn_candidate_update_profile', [$this, 'handle_candidate_update_profile']);
        add_action('wp_ajax_cmn_candidate_learning_opt_in', [$this, 'handle_candidate_learning_opt_in']);
        add_action('admin_post_cmn_candidate_download_doc', [$this, 'handle_candidate_download_doc']);
        add_filter('upload_size_limit', [$this, 'filter_candidate_upload_size_limit'], 20);
        add_filter('wp_handle_upload_prefilter', [$this, 'prefilter_candidate_doc_upload']);
        add_filter('authenticate', [$this, 'block_deactivated_staff_login'], 30, 3);
        add_action('phpmailer_init', [$this, 'configure_candidate_smtp']);
        add_action('wp_mail_failed', [$this, 'handle_candidate_mail_failed']);
        add_action('wp_mail_succeeded', [$this, 'handle_candidate_mail_succeeded']);
        add_filter('wp_mail_from', [$this, 'filter_mail_from']);
        add_filter('wp_mail_from_name', [$this, 'filter_mail_from_name']);
        add_filter('retrieve_password_message', [$this, 'flag_candidate_password_reset'], 10, 4);
        add_filter('retrieve_password_title', [$this, 'flag_candidate_password_reset_title'], 10, 3);
        add_filter('password_change_email', [$this, 'flag_candidate_password_change_email'], 10, 3);
        add_filter('manage_cmn_school_posts_columns', [$this, 'school_columns']);
        add_action('manage_cmn_school_posts_custom_column', [$this, 'school_column_values'], 10, 2);
        add_filter('manage_cmn_candidate_posts_columns', [$this, 'candidate_columns']);
        add_action('manage_cmn_candidate_posts_custom_column', [$this, 'candidate_column_values'], 10, 2);
        add_action('init', [$this, 'schedule_booking_expiry']);
        add_action('cmn_expire_bookings', [$this, 'expire_booking_requests']);
        add_filter('cron_schedules', [$this, 'register_cron_schedules']);
        add_action('template_redirect', [$this, 'redirect_legacy_portal_paths']);
        add_action('template_redirect', [$this, 'protect_candidate_doc_attachment_access'], 1);
        add_action('init', [$this, 'schedule_compliance_reminders']);
        add_action('init', [$this, 'maybe_run_compliance_reminders_fallback'], 20);
        add_action('cmn_compliance_reminders', [$this, 'run_compliance_reminders']);
    }

    public static function activate() {
        self::create_page_if_missing('Login', '[cmn_login]');
        self::create_page_if_missing('Portal', '[cmn_portal]');
        self::create_page_if_missing('School Registration', '[cmn_register_school]');
        self::create_page_if_missing('Candidate Registration', '[cmn_register_candidate]');
        self::create_page_if_missing('CoverMeNow ONE for Schools', '[cmn_school_landing]');
        self::create_page_if_missing('CoverMeNow ONE for Candidates', '[cmn_candidate_landing]');
        self::create_page_if_missing('School Dashboard', '[cmn_school_dashboard]');
        self::create_page_if_missing('Candidate Dashboard', '[cmn_candidate_dashboard]');
        self::create_page_if_missing('Available Tomorrow', '[cmn_available_wall]');
        self::install_schema();
    }

    private static function create_page_if_missing($title, $shortcode) {
        $existing = get_page_by_title($title);
        if ($existing) {
            return;
        }
        wp_insert_post([
            'post_type' => 'page',
            'post_title' => $title,
            'post_status' => 'publish',
            'post_content' => $shortcode,
        ]);
    }

    public function ensure_required_pages() {
        self::create_page_if_missing('Portal', '[cmn_portal]');
        self::create_page_if_missing('Login', '[cmn_login]');
        self::create_page_if_missing('School Registration', '[cmn_register_school]');
        self::create_page_if_missing('Candidate Registration', '[cmn_register_candidate]');
        self::create_page_if_missing('CoverMeNow ONE for Schools', '[cmn_school_landing]');
        self::create_page_if_missing('CoverMeNow ONE for Candidates', '[cmn_candidate_landing]');
    }

    public function maybe_upgrade_schema() {
        $installed = (int) get_option('cmn_schema_version', 0);
        if ($installed < self::SCHEMA_VERSION) {
            self::install_schema();
        }
    }

    public function maybe_auto_bump_portal_release_version() {
        if (!$this->should_auto_bump_release_version()) {
            return;
        }
        $current_fingerprint = $this->get_release_fingerprint();
        if ($current_fingerprint === '') {
            return;
        }
        $stored_fingerprint = (string) get_option('cmn_portal_release_fingerprint', '');
        if ($stored_fingerprint === '') {
            update_option('cmn_portal_release_fingerprint', $current_fingerprint, false);
            if ((string) get_option('cmn_portal_release_version', '') === '') {
                update_option('cmn_portal_release_version', '0.0.1', false);
            }
            return;
        }
        if ($stored_fingerprint === $current_fingerprint) {
            return;
        }
        $current_version = $this->get_portal_release_version();
        $next_version = $this->increment_patch_version($current_version);
        update_option('cmn_portal_release_version', $next_version, false);
        update_option('cmn_portal_release_fingerprint', $current_fingerprint, false);
    }

    private function should_auto_bump_release_version() {
        if (defined('CMN_RELEASE_AUTO_BUMP')) {
            return (bool) CMN_RELEASE_AUTO_BUMP;
        }
        if (defined('WP_ENVIRONMENT_TYPE')) {
            $env = strtolower((string) WP_ENVIRONMENT_TYPE);
            if (in_array($env, ['local', 'development'], true)) {
                return false;
            }
        }
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return false;
        }
        $home = home_url('/');
        $host = strtolower((string) wp_parse_url($home, PHP_URL_HOST));
        if ($host === '') {
            return false;
        }
        $is_local_tld = (strlen($host) >= 6 && substr($host, -6) === '.local');
        $is_staging = strpos($host, 'staging') !== false;
        if ($host === 'localhost' || $host === '127.0.0.1' || $is_local_tld || $is_staging) {
            return false;
        }
        return true;
    }

    private function get_release_fingerprint() {
        $paths = [
            plugin_dir_path(__FILE__) . 'covermenowone-one.php',
            plugin_dir_path(__FILE__) . 'frontend.js',
            plugin_dir_path(__FILE__) . 'frontend.css',
            plugin_dir_path(__FILE__) . 'admin.css',
        ];
        $parts = [];
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }
            $hash = md5_file($path);
            if ($hash === false) {
                continue;
            }
            $parts[] = basename($path) . ':' . $hash;
        }
        if (!$parts) {
            return '';
        }
        return md5(implode('|', $parts));
    }

    private function increment_patch_version($version) {
        $version = trim((string) $version);
        if (!preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $version, $matches)) {
            return '0.0.1';
        }
        $major = (int) $matches[1];
        $minor = (int) $matches[2];
        $patch = (int) $matches[3] + 1;
        return $major . '.' . $minor . '.' . $patch;
    }

    private function get_portal_release_version() {
        $version = (string) get_option('cmn_portal_release_version', '');
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            $version = '0.0.1';
            update_option('cmn_portal_release_version', $version, false);
        }
        return $version;
    }

    private function render_portal_branding() {
        $version = $this->get_portal_release_version();
        ob_start();
        ?>
        <div class="cmn-brand-block">
            <div class="cmn-brand-title">CoverMeNow <span class="cmn-topbar-accent">ONE</span></div>
            <div class="cmn-brand-version">Version V<?php echo esc_html($version); ?></div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function migrate_candidate_statuses_to_approved() {
        if (get_option('cmn_candidate_status_migrated_v1') === '1') {
            return;
        }
        $candidate_ids = get_posts([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);
        foreach ((array) $candidate_ids as $candidate_id) {
            $current = strtolower((string) get_post_meta((int) $candidate_id, 'cmn_status', true));
            if ($current === '' || $current === 'pending') {
                update_post_meta((int) $candidate_id, 'cmn_status', 'approved');
            }
            $user_id = (int) get_post_meta((int) $candidate_id, 'cmn_user_id', true);
            if ($user_id) {
                $user = get_user_by('id', $user_id);
                if ($user) {
                    $roles = (array) $user->roles;
                    if (in_array('cmn_candidate_pending', $roles, true) || !in_array('cmn_candidate', $roles, true)) {
                        $user_obj = new WP_User($user_id);
                        $user_obj->set_role('cmn_candidate');
                    }
                }
            }
        }
        update_option('cmn_candidate_status_migrated_v1', '1', false);
    }

    private static function install_schema() {
        global $wpdb;
        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
        $charset = $wpdb->get_charset_collate();
        $school_index = $wpdb->prefix . 'cmn_school_index';
        $contact_school = $wpdb->prefix . 'cmn_contact_school';
        $activities = $wpdb->prefix . 'cmn_activities';
        $client_profiles = $wpdb->prefix . 'cmn_client_profiles';
        $client_tokens = $wpdb->prefix . 'cmn_client_tokens';
        $candidate_availability = $wpdb->prefix . 'cmn_candidate_availability';
        $candidate_requests = $wpdb->prefix . 'cmn_candidate_requests';
        $candidate_calendar = $wpdb->prefix . 'cmn_candidate_calendar_availability';
        $notifications = $wpdb->prefix . 'cmn_notifications';
        $email_log = $wpdb->prefix . 'cmn_email_log';
        $support_tickets = $wpdb->prefix . 'cmn_support_tickets';
        $support_messages = $wpdb->prefix . 'cmn_support_messages';
        $support_feedback = $wpdb->prefix . 'cmn_support_feedback';
        $support_transcripts = $wpdb->prefix . 'cmn_support_transcripts';
        $support_transcript_emails = $wpdb->prefix . 'cmn_support_transcript_emails';
        $booking_threads = $wpdb->prefix . 'cmn_booking_threads';
        $booking_messages = $wpdb->prefix . 'cmn_booking_messages';
        $booking_participants = $wpdb->prefix . 'cmn_booking_thread_participants';
        $booking_feedback = $wpdb->prefix . 'cmn_booking_feedback';
        $ready_responses = $wpdb->prefix . 'cmn_school_ready_responses';

        $sql = "CREATE TABLE {$school_index} (
            school_id varchar(20) NOT NULL,
            post_id bigint(20) unsigned NOT NULL,
            name varchar(255) NULL,
            school_email varchar(190) NULL,
            school_email_domain varchar(190) NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (school_id),
            UNIQUE KEY post_id (post_id),
            UNIQUE KEY school_email_domain (school_email_domain),
            KEY school_email (school_email)
        ) {$charset};

        CREATE TABLE {$contact_school} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            school_id varchar(20) NOT NULL,
            school_email_domain varchar(190) NULL,
            contact_id bigint(20) unsigned NOT NULL,
            is_primary tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY school_contact (school_id, contact_id),
            UNIQUE KEY school_domain_contact (school_email_domain, contact_id),
            KEY contact_id (contact_id),
            KEY school_id (school_id),
            KEY school_email_domain (school_email_domain)
        ) {$charset};

        CREATE TABLE {$activities} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            entity_type varchar(20) NOT NULL,
            entity_ref varchar(64) NOT NULL,
            activity_type varchar(20) NOT NULL,
            subject varchar(255) NOT NULL,
            notes longtext NULL,
            due_date date NULL,
            duration_minutes int NULL,
            assigned_to_user_id bigint(20) unsigned NULL,
            assigned_to_school_domain varchar(190) NULL,
            completed_at datetime NULL,
            created_by bigint(20) unsigned NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY entity_lookup (entity_type, entity_ref),
            KEY due_date (due_date)
        ) {$charset};

        CREATE TABLE {$client_profiles} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            school_email_domain varchar(190) NOT NULL,
            completion_status varchar(20) NOT NULL DEFAULT 'incomplete',
            school_type varchar(120) NULL,
            pupil_count varchar(120) NULL,
            supply_frequency varchar(120) NULL,
            uses_agencies varchar(120) NULL,
            agency_count varchar(120) NULL,
            notes longtext NULL,
            completed_at datetime NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY school_email_domain (school_email_domain)
        ) {$charset};

        CREATE TABLE {$client_tokens} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            school_email_domain varchar(190) NOT NULL,
            token_hash varchar(128) NOT NULL,
            expires_at datetime NOT NULL,
            used_at datetime NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY school_email_domain (school_email_domain),
            KEY token_hash (token_hash)
        ) {$charset};

        CREATE TABLE {$candidate_availability} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            candidate_id bigint(20) unsigned NOT NULL,
            available_date date NOT NULL,
            available_type varchar(20) NOT NULL DEFAULT 'morning',
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY candidate_day (candidate_id, available_date),
            KEY available_date (available_date),
            KEY candidate_id (candidate_id)
        ) {$charset};

        CREATE TABLE {$candidate_calendar} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            candidate_id bigint(20) unsigned NOT NULL,
            date date NOT NULL,
            status varchar(20) NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY candidate_date (candidate_id, date),
            KEY date (date),
            KEY candidate_id (candidate_id)
        ) {$charset};

        CREATE TABLE {$candidate_requests} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            school_id bigint(20) unsigned NULL,
            school_email_domain varchar(190) NOT NULL,
            school_user_id bigint(20) unsigned NULL,
            candidate_id bigint(20) unsigned NOT NULL,
            account_manager_user_id bigint(20) unsigned NULL,
            ready_response_id bigint(20) unsigned NULL,
            requested_date date NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'requested',
            request_sent_at datetime NULL,
            expires_at datetime NULL,
            candidate_pay_rate decimal(10,2) NULL,
            school_charge_rate decimal(10,2) NULL,
            internal_note longtext NULL,
            requested_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY school_candidate_day (school_email_domain, candidate_id, requested_date),
            KEY school_id (school_id),
            KEY school_email_domain (school_email_domain),
            KEY school_user_id (school_user_id),
            KEY candidate_id (candidate_id),
            KEY account_manager_user_id (account_manager_user_id),
            KEY ready_response_id (ready_response_id),
            KEY requested_date (requested_date),
            KEY status (status),
            KEY expires_at (expires_at)
        ) {$charset};

        CREATE TABLE {$notifications} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            type varchar(40) NOT NULL,
            title varchar(190) NOT NULL,
            message longtext NULL,
            link_url varchar(255) NULL,
            is_read tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY is_read (is_read),
            KEY type (type)
        ) {$charset};

        CREATE TABLE {$email_log} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            to_email varchar(190) NOT NULL,
            subject varchar(190) NOT NULL,
            type varchar(60) NOT NULL,
            related_school_domain varchar(190) NULL,
            related_candidate_id bigint(20) unsigned NULL,
            related_request_id bigint(20) unsigned NULL,
            sent_at datetime NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'sent',
            error_message longtext NULL,
            PRIMARY KEY (id),
            KEY type (type),
            KEY related_school_domain (related_school_domain),
            KEY related_candidate_id (related_candidate_id),
            KEY related_request_id (related_request_id)
        ) {$charset};

        CREATE TABLE {$support_tickets} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            ticket_ref varchar(30) NOT NULL,
            created_by_user_id bigint(20) unsigned NOT NULL,
            user_role_type varchar(20) NOT NULL,
            subject varchar(255) NOT NULL,
            category varchar(50) NULL,
            status varchar(20) NOT NULL DEFAULT 'new',
            is_new_for_admin tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            closed_at datetime NULL,
            PRIMARY KEY (id),
            UNIQUE KEY ticket_ref (ticket_ref),
            KEY created_by_user_id (created_by_user_id),
            KEY status (status),
            KEY is_new_for_admin (is_new_for_admin)
        ) {$charset};

        CREATE TABLE {$support_messages} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) unsigned NOT NULL,
            sender_user_id bigint(20) unsigned NULL,
            sender_type varchar(20) NOT NULL,
            message longtext NOT NULL,
            attachment_ids longtext NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY ticket_id (ticket_id),
            KEY sender_type (sender_type)
        ) {$charset};

        CREATE TABLE {$support_feedback} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            support_rating tinyint(1) NOT NULL,
            response_time_rating tinyint(1) NOT NULL,
            issue_resolved tinyint(1) NOT NULL DEFAULT 0,
            overall_satisfaction tinyint(1) NOT NULL,
            comments longtext NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY ticket_user (ticket_id, user_id),
            KEY ticket_id (ticket_id),
            KEY user_id (user_id)
        ) {$charset};

        CREATE TABLE {$support_transcripts} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) unsigned NOT NULL,
            created_by bigint(20) unsigned NOT NULL,
            transcript_text longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY ticket_id (ticket_id),
            KEY created_by (created_by)
        ) {$charset};

        CREATE TABLE {$support_transcript_emails} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) unsigned NOT NULL,
            created_by bigint(20) unsigned NOT NULL,
            to_email varchar(190) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'sent',
            error_message longtext NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY ticket_id (ticket_id),
            KEY created_by (created_by)
        ) {$charset};

        CREATE TABLE {$booking_threads} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) unsigned NOT NULL,
            thread_type varchar(30) NOT NULL DEFAULT 'booking_details',
            candidate_user_id bigint(20) unsigned NULL,
            school_user_id bigint(20) unsigned NULL,
            account_manager_user_id bigint(20) unsigned NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY booking_thread_type (booking_id, thread_type),
            KEY booking_id (booking_id),
            KEY candidate_user_id (candidate_user_id),
            KEY school_user_id (school_user_id),
            KEY account_manager_user_id (account_manager_user_id),
            KEY status (status)
        ) {$charset};

        CREATE TABLE {$booking_messages} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            thread_id bigint(20) unsigned NOT NULL,
            sender_user_id bigint(20) unsigned NULL,
            sender_role_type varchar(30) NOT NULL,
            message longtext NOT NULL,
            attachment_ids longtext NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY thread_id (thread_id),
            KEY sender_user_id (sender_user_id)
        ) {$charset};

        CREATE TABLE {$booking_participants} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            thread_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            role_type varchar(30) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY thread_user (thread_id, user_id),
            KEY thread_id (thread_id),
            KEY user_id (user_id)
        ) {$charset};

        CREATE TABLE {$booking_feedback} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) unsigned NOT NULL,
            rater_user_id bigint(20) unsigned NOT NULL,
            rated_entity_type varchar(20) NOT NULL,
            rated_entity_id bigint(20) unsigned NOT NULL,
            stars_overall tinyint(1) NOT NULL,
            stars_1 tinyint(1) NOT NULL,
            stars_2 tinyint(1) NOT NULL,
            stars_3 tinyint(1) NOT NULL,
            tags longtext NULL,
            would_rebook tinyint(1) NULL,
            comment longtext NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY booking_rater (booking_id, rater_user_id),
            KEY booking_id (booking_id),
            KEY rated_entity_type (rated_entity_type),
            KEY rated_entity_id (rated_entity_id),
            KEY stars_overall (stars_overall),
            KEY created_at (created_at)
        ) {$charset};

        CREATE TABLE {$ready_responses} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            school_user_id bigint(20) unsigned NOT NULL,
            title varchar(190) NOT NULL,
            is_default tinyint(1) NOT NULL DEFAULT 0,
            message_template longtext NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY school_user_id (school_user_id),
            KEY is_default (is_default)
        ) {$charset};";

        dbDelta($sql);
        update_option('cmn_schema_version', self::SCHEMA_VERSION);
        self::migrate_school_index();
        self::migrate_contact_links();
    }

    private static function migrate_school_index() {
        global $wpdb;
        $school_index = $wpdb->prefix . 'cmn_school_index';
        $schools = get_posts([
            'post_type' => 'cmn_school',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);
        if (!$schools) {
            return;
        }
        foreach ($schools as $post_id) {
            $school_id = get_post_meta($post_id, 'cmn_school_id', true);
            if (!$school_id) {
                $school_id = self::generate_school_id_static();
                update_post_meta($post_id, 'cmn_school_id', $school_id);
            }
            $name = get_the_title($post_id);
            $email = get_post_meta($post_id, 'cmn_email', true);
            $domain = '';
            if ($email) {
                $parts = explode('@', strtolower(trim($email)));
                $domain = strtolower(trim(end($parts)));
                if ($domain) {
                    update_post_meta($post_id, 'cmn_school_email_domain', $domain);
                }
            }
            $wpdb->replace($school_index, [
                'school_id' => strtoupper(trim($school_id)),
                'post_id' => (int) $post_id,
                'name' => $name,
                'school_email' => $email,
                'school_email_domain' => $domain ?: null,
                'updated_at' => current_time('mysql'),
            ], ['%s', '%d', '%s', '%s', '%s', '%s']);
        }
    }

    private static function migrate_contact_links() {
        global $wpdb;
        $contact_school = $wpdb->prefix . 'cmn_contact_school';
        $contacts = get_posts([
            'post_type' => 'cmn_contact',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);
        if (!$contacts) {
            return;
        }
        foreach ($contacts as $contact_id) {
            $legacy_school_post = (int) get_post_meta($contact_id, 'cmn_contact_school_id', true);
            if (!$legacy_school_post) {
                continue;
            }
            $school_code = get_post_meta($legacy_school_post, 'cmn_school_id', true);
            $school_domain = get_post_meta($legacy_school_post, 'cmn_school_email_domain', true);
            if (!$school_code) {
                continue;
            }
            $wpdb->replace($contact_school, [
                'school_id' => strtoupper(trim($school_code)),
                'school_email_domain' => $school_domain ?: null,
                'contact_id' => (int) $contact_id,
                'is_primary' => 1,
                'created_at' => current_time('mysql'),
            ], ['%s', '%s', '%d', '%d', '%s']);
        }
    }

    public function record_user_login($user_login, $user) {
        if (!$user instanceof WP_User) {
            return;
        }
        update_user_meta($user->ID, 'cmn_last_login', time());
    }

    public function register_post_types() {
        $common = [
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => ['title'],
            'capability_type' => 'post',
        ];

        register_post_type('cmn_school', array_merge($common, [
            'label' => 'Schools',
            'menu_icon' => 'dashicons-admin-multisite',
        ]));

        register_post_type('cmn_candidate', array_merge($common, [
            'label' => 'Candidates',
            'menu_icon' => 'dashicons-id',
        ]));

        register_post_type('cmn_contact', array_merge($common, [
            'label' => 'Contacts',
            'menu_icon' => 'dashicons-businessperson',
        ]));

        register_post_type('cmn_job', array_merge($common, [
            'label' => 'Jobs',
            'menu_icon' => 'dashicons-clipboard',
        ]));

        register_post_type('cmn_booking', array_merge($common, [
            'label' => 'Bookings',
            'menu_icon' => 'dashicons-calendar-alt',
        ]));

        register_post_type('cmn_activity', array_merge($common, [
            'label' => 'Activities',
            'menu_icon' => 'dashicons-list-view',
        ]));
    }

    public function register_roles() {
        // Roles will be refined once capability matrix is confirmed.
        if (!get_role('cmn_admin')) {
            add_role('cmn_admin', 'CMN Admin', ['read' => true]);
        }
        if (!get_role('cmn_staff')) {
            add_role('cmn_staff', 'CMN Staff', ['read' => true]);
        }
        if (!get_role('cmn_account_manager')) {
            add_role('cmn_account_manager', 'Account Manager', ['read' => true]);
        }
        if (!get_role('cmn_school_manager')) {
            add_role('cmn_school_manager', 'School Manager', ['read' => true]);
        }
        if (!get_role('cmn_school_staff')) {
            add_role('cmn_school_staff', 'School Staff', ['read' => true]);
        }
        if (!get_role('cmn_candidate')) {
            add_role('cmn_candidate', 'Candidate', ['read' => true]);
        }
        if (!get_role('cmn_candidate_pending')) {
            add_role('cmn_candidate_pending', 'Candidate (Pending)', ['read' => true]);
        }
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'cmn-') === false && $hook !== 'toplevel_page_cmn-dashboard') {
            return;
        }
        wp_enqueue_style('cmn-admin', plugin_dir_url(__FILE__) . 'admin.css', [], self::VERSION);
    }

    public function enqueue_frontend_assets() {
        if (!is_singular()) {
            return;
        }
        $css_path = plugin_dir_path(__FILE__) . 'frontend.css';
        $js_path = plugin_dir_path(__FILE__) . 'frontend.js';
        $css_ver = file_exists($css_path) ? filemtime($css_path) : self::VERSION;
        $js_ver = file_exists($js_path) ? filemtime($js_path) : self::VERSION;
        wp_enqueue_style('cmn-frontend', plugin_dir_url(__FILE__) . 'frontend.css', [], $css_ver);
        wp_enqueue_script('cmn-frontend', plugin_dir_url(__FILE__) . 'frontend.js', [], $js_ver, true);
        wp_localize_script('cmn-frontend', 'cmnPortal', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'availabilityNonce' => wp_create_nonce('cmn_mark_available'),
            'calendarNonce' => wp_create_nonce('cmn_update_calendar_day'),
            'calendarBulkNonce' => wp_create_nonce('cmn_bulk_update_calendar'),
            'calendarClearNonce' => wp_create_nonce('cmn_clear_calendar'),
            'requestCandidateNonce' => wp_create_nonce('cmn_request_candidate'),
            'notificationNonce' => wp_create_nonce('cmn_mark_notifications_read'),
            'testEmailNonce' => wp_create_nonce('cmn_send_test_emails'),
            'staffNonce' => wp_create_nonce('cmn_staff_manage'),
            'supportNonce' => wp_create_nonce('cmn_support'),
            'candidateTourNonce' => wp_create_nonce('cmn_dismiss_candidate_tour'),
            'candidateSettingsNonce' => wp_create_nonce('cmn_candidate_settings'),
            'candidateDocNonce' => wp_create_nonce('cmn_candidate_doc'),
            'candidateProfileNonce' => wp_create_nonce('cmn_candidate_profile'),
            'candidateLearningNonce' => wp_create_nonce('cmn_candidate_learning'),
            'themeSettingsNonce' => wp_create_nonce('cmn_theme_settings'),
            'candidateTourAvatar' => site_url('/covermenowone/avatar.png'),
            'bookingChatNonce' => wp_create_nonce('cmn_booking_chat_fetch'),
            'bookingFeedbackNonce' => wp_create_nonce('cmn_booking_feedback'),
        ]);
    }

    public function add_portal_body_class($classes) {
        if ($this->is_portal_page()) {
            $classes[] = 'cmn-portal-page';
        }
        if ($this->is_portal_page() && !is_user_logged_in()) {
            $view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : '';
            if (in_array($view, ['forgot-password', 'reset-password', 'set-password', 'candidate-verify'], true)) {
                $classes[] = 'cmn-portal-auth';
                $classes[] = 'cmn-portal-bg';
            }
        }
        if ($this->is_registration_page()) {
            $classes[] = 'cmn-portal-bg';
        }
        if ($this->is_login_page() || ($this->is_portal_page() && !is_user_logged_in())) {
            $classes[] = 'cmn-portal-scroll';
        }
        if ($this->is_portal_page() && is_user_logged_in()) {
            $classes[] = 'cmn-theme-' . $this->get_user_theme_scheme(get_current_user_id());
        }
        return $classes;
    }

    private function is_portal_page() {
        if (!is_singular()) {
            return false;
        }
        $post = get_post();
        if (!$post) {
            return false;
        }
        $content = $post->post_content;
        return has_shortcode($content, 'cmn_login')
            || has_shortcode($content, 'cmn_portal')
            || has_shortcode($content, 'cmn_register_school')
            || has_shortcode($content, 'cmn_register_candidate')
            || has_shortcode($content, 'cmn_school_landing')
            || has_shortcode($content, 'cmn_candidate_landing')
            || has_shortcode($content, 'cmn_school_dashboard')
            || has_shortcode($content, 'cmn_candidate_dashboard');
    }

    private function is_registration_page() {
        if (!is_singular()) {
            return false;
        }
        $post = get_post();
        if (!$post) {
            return false;
        }
        $content = $post->post_content;
        return has_shortcode($content, 'cmn_register_school')
            || has_shortcode($content, 'cmn_register_candidate');
    }

    private function is_login_page() {
        if (!is_singular()) {
            return false;
        }
        $post = get_post();
        if (!$post) {
            return false;
        }
        $content = $post->post_content;
        return has_shortcode($content, 'cmn_login');
    }

    private function can_preview_dashboards() {
        $user = wp_get_current_user();
        if (!$user || !$user->exists()) {
            return false;
        }
        $email = strtolower($user->user_email);
        return $email === 'jay.norton@covermenow.co.uk' || current_user_can('manage_options');
    }

    public function register_cron_schedules($schedules) {
        if (!isset($schedules['cmn_five_minutes'])) {
            $schedules['cmn_five_minutes'] = [
                'interval' => 300,
                'display' => 'Every 5 Minutes',
            ];
        }
        if (!isset($schedules['cmn_hourly'])) {
            $schedules['cmn_hourly'] = [
                'interval' => 3600,
                'display' => 'Every Hour',
            ];
        }
        return $schedules;
    }

    public function schedule_booking_expiry() {
        if (!wp_next_scheduled('cmn_expire_bookings')) {
            wp_schedule_event(time() + 300, 'cmn_five_minutes', 'cmn_expire_bookings');
        }
        $last_feedback_scan = (int) get_transient('cmn_feedback_scan_ts');
        $now_ts = (int) current_time('timestamp');
        if ($last_feedback_scan < 1 || ($now_ts - $last_feedback_scan) >= 300) {
            $this->maybe_request_feedback_for_past_bookings();
            set_transient('cmn_feedback_scan_ts', $now_ts, 300);
        }
    }

    public function schedule_compliance_reminders() {
        if (!wp_next_scheduled('cmn_compliance_reminders')) {
            wp_schedule_event(time() + 900, 'cmn_hourly', 'cmn_compliance_reminders');
        }
    }

    public function maybe_run_compliance_reminders_fallback() {
        if (wp_doing_ajax()) {
            return;
        }
        if (!$this->is_portal_page()) {
            return;
        }
        $last_run = (int) get_option('cmn_compliance_reminder_last_run_ts', 0);
        $now = time();
        if (($now - $last_run) < 3600) {
            return;
        }
        if (!defined('DISABLE_WP_CRON') || !DISABLE_WP_CRON) {
            return;
        }
        $this->run_compliance_reminders();
    }

    private function resolve_school_id_for_user($user_id = 0) {
        $user_id = $user_id ?: get_current_user_id();
        if (!$user_id) {
            return 0;
        }
        $mapped = get_user_meta($user_id, 'cmn_school_id', true);
        if ($mapped) {
            return (int) $mapped;
        }
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return 0;
        }
        $school = get_posts([
            'post_type' => 'cmn_school',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'cmn_email',
                    'value' => $user->user_email,
                ],
            ],
        ]);
        if ($school) {
            return (int) $school[0]->ID;
        }
        return 0;
    }

    private function get_candidate_id_for_user($user_id = 0) {
        $user_id = $user_id ?: get_current_user_id();
        if (!$user_id) {
            return 0;
        }
        $user = get_user_by('id', $user_id);
        if (!$user || !$user->user_email) {
            return 0;
        }
        $candidate = get_posts([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'cmn_email',
                    'value' => $user->user_email,
                ],
            ],
        ]);
        if ($candidate) {
            return (int) $candidate[0];
        }
        return 0;
    }

    private function get_candidate_user_id($candidate_id) {
        if (!$candidate_id) {
            return 0;
        }
        $user_id = (int) get_post_meta($candidate_id, 'cmn_user_id', true);
        if ($user_id) {
            return $user_id;
        }
        $email = get_post_meta($candidate_id, 'cmn_email', true);
        if ($email) {
            $user = get_user_by('email', $email);
            if ($user) {
                return (int) $user->ID;
            }
        }
        return 0;
    }

    private function get_account_manager($school_id) {
        $name = get_post_meta($school_id, 'cmn_account_manager_name', true);
        $email = get_post_meta($school_id, 'cmn_account_manager_email', true);
        if (!$email) {
            $email = get_option('admin_email');
        }
        if (!$name) {
            $name = 'CoverMeNow ONE';
        }
        return [
            'name' => $name,
            'email' => $email,
        ];
    }

    private function render_staff_shell($active, $inner_html) {
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        $user = wp_get_current_user();
        $nav_items = [
            'dashboard' => ['label' => 'Dashboard', 'url' => $portal_url],
            'schools' => ['label' => 'Schools', 'url' => add_query_arg(['view' => 'schools'], $portal_url)],
            'contacts' => ['label' => 'Contacts', 'url' => add_query_arg(['view' => 'contacts'], $portal_url)],
            'candidates' => ['label' => 'Candidates', 'url' => add_query_arg(['view' => 'candidates'], $portal_url)],
            'cv_converter' => ['label' => 'CV Converter', 'url' => add_query_arg(['view' => 'cv-converter'], $portal_url)],
            'requests' => ['label' => 'Requests', 'url' => add_query_arg(['view' => 'requests'], $portal_url)],
            'bookings' => ['label' => 'Bookings', 'url' => add_query_arg(['view' => 'bookings'], $portal_url)],
            'analytics' => ['label' => 'Analytics', 'url' => add_query_arg(['view' => 'analytics'], $portal_url)],
            'settings' => ['label' => 'Settings', 'url' => add_query_arg(['view' => 'settings'], $portal_url)],
            'invoicing' => ['label' => 'Invoicing', 'url' => add_query_arg(['view' => 'invoicing'], $portal_url)],
            'support' => ['label' => 'Support', 'url' => add_query_arg(['view' => 'support'], $portal_url)],
        ];
        if ($this->is_admin_user($user ? $user->ID : 0)) {
            $nav_items['war_room'] = ['label' => 'War Room', 'url' => add_query_arg(['view' => 'war-room'], $portal_url)];
        }
        if ($this->is_super_admin_user()) {
            $nav_items['staff'] = ['label' => 'Staff', 'url' => add_query_arg(['view' => 'staff'], $portal_url)];
        }

        ob_start();
        ?>
        <section class="cmn-portal cmn-portal-light">
            <div class="cmn-portal-topbar">
                <div class="cmn-topbar-left"><?php echo $this->render_portal_branding(); ?></div>
                <div class="cmn-topbar-right">
                    <?php echo $this->render_notifications_bell($user ? $user->ID : 0); ?>
                    <span>Welcome, <?php echo esc_html($user ? $user->display_name : 'Admin'); ?></span>
                    <a class="cmn-topbar-logout" href="<?php echo esc_url(wp_logout_url($portal_url)); ?>">Logout</a>
                </div>
            </div>
            <div class="cmn-school-shell cmn-staff-shell">
                <aside class="cmn-school-nav cmn-staff-nav">
                    <nav class="cmn-school-nav-links">
                        <?php foreach ($nav_items as $key => $item) : ?>
                            <a class="cmn-school-nav-link<?php echo $active === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
                        <?php endforeach; ?>
                    </nav>
                </aside>
                <main class="cmn-school-main cmn-staff-main">
                    <?php echo $inner_html; ?>
                </main>
            </div>
        </section>
        <?php
        $output = ob_get_clean();
        if (strpos($output, 'id="cmn-tomorrow-availability-btn"') === false) {
            error_log('CMN availability button missing in candidate dashboard render. user_id=' . (is_user_logged_in() ? get_current_user_id() : 0));
        }
        return $output;
    }

    private function get_assigned_candidates($school_id) {
        $assigned = get_post_meta($school_id, 'cmn_assigned_candidates', true);
        if (is_array($assigned)) {
            return array_map('intval', $assigned);
        }
        if (is_string($assigned) && $assigned !== '') {
            $decoded = json_decode($assigned, true);
            if (is_array($decoded)) {
                return array_map('intval', $decoded);
            }
        }
        return [];
    }

    private function get_candidate_rate($candidate_id, $school_id = 0) {
        $default_rate = get_post_meta($candidate_id, 'cmn_default_rate', true);
        if ($default_rate === '') {
            $default_rate = 100;
        }
        $rate = (float) $default_rate;
        if ($school_id) {
            $rates = get_post_meta($school_id, 'cmn_candidate_rates', true);
            if (is_array($rates) && isset($rates[$candidate_id]) && $rates[$candidate_id] !== '') {
                $rate = (float) $rates[$candidate_id];
            }
        }
        return $rate;
    }

    private function get_portal_base_url() {
        return home_url('/portal/');
    }

    private function get_cv_converter_base_url() {
        return home_url('/cv-converter/');
    }

    private function user_can_manage_cv_converter($user_id = 0) {
        $user_id = $user_id ? (int) $user_id : get_current_user_id();
        if (!$user_id) {
            return false;
        }
        return $this->is_admin_user($user_id)
            || $this->is_staff_role($user_id)
            || $this->is_account_manager_user($user_id);
    }

    private function get_staff_admin_users_for_cv_notifications() {
        $users = get_users([
            'role__in' => ['administrator', 'cmn_admin', 'cmn_staff', 'cmn_account_manager'],
            'fields' => ['ID'],
        ]);
        $ids = [];
        foreach ((array) $users as $user) {
            if (!is_object($user) || !isset($user->ID)) {
                continue;
            }
            $ids[] = (int) $user->ID;
        }
        return array_values(array_unique(array_filter($ids)));
    }

    private function add_candidate_cv_reconversion_notification($candidate_id) {
        $candidate_id = (int) $candidate_id;
        if (!$candidate_id) {
            return;
        }
        $candidate_name = (string) get_the_title($candidate_id);
        $portal_link = add_query_arg([
            'view' => 'cv-converter',
            'candidate_id' => $candidate_id,
        ], $this->get_portal_base_url());
        foreach ($this->get_staff_admin_users_for_cv_notifications() as $user_id) {
            $this->add_notification(
                (int) $user_id,
                'candidate_cv_reconversion_required',
                'Candidate updated CV — reconversion required.',
                $candidate_name !== '' ? ($candidate_name . ' requires CV reconversion.') : 'Candidate requires CV reconversion.',
                $portal_link
            );
        }
    }

    private function add_candidate_cv_audit_entry($candidate_id, $subject, $notes = '', $created_by = 0) {
        $candidate_id = (int) $candidate_id;
        if (!$candidate_id) {
            return 0;
        }
        return $this->insert_activity_row([
            'entity_type' => 'contact',
            'entity_ref' => (string) $candidate_id,
            'activity_type' => 'note',
            'subject' => sanitize_text_field((string) $subject),
            'notes' => sanitize_textarea_field((string) $notes),
            'created_by' => $created_by ? (int) $created_by : get_current_user_id(),
        ]);
    }

    private function get_candidate_cv_original_attachment_id($candidate_id, $candidate_user_id = 0) {
        $candidate_id = (int) $candidate_id;
        if (!$candidate_id) {
            return 0;
        }
        $attachment_id = (int) get_post_meta($candidate_id, 'cmn_cv_original_attachment_id', true);
        if ($attachment_id > 0) {
            return $attachment_id;
        }
        if (!$candidate_user_id) {
            $candidate_user_id = (int) $this->get_candidate_user_id($candidate_id);
        }
        if ($candidate_user_id > 0) {
            $cv_status = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, 'cv');
            $attachment_id = (int) ($cv_status['attachment_id'] ?? 0);
            if ($attachment_id > 0) {
                update_post_meta($candidate_id, 'cmn_cv_original_attachment_id', $attachment_id);
                $uploaded_at = (string) ($cv_status['uploaded_at'] ?? '');
                if ($uploaded_at !== '') {
                    update_post_meta($candidate_id, 'cmn_cv_original_uploaded_at', $uploaded_at);
                }
            }
        }
        return $attachment_id;
    }

    private function get_candidate_cv_formatted_attachment_id($candidate_id) {
        $candidate_id = (int) $candidate_id;
        if (!$candidate_id) {
            return 0;
        }
        if ((string) get_post_meta($candidate_id, 'cmn_cv_formatted_outdated', true) === '1') {
            return 0;
        }
        $attachment_id = (int) get_post_meta($candidate_id, 'cmn_cv_formatted_attachment_id', true);
        if ($attachment_id < 1) {
            return 0;
        }
        if (get_post_type($attachment_id) !== 'attachment') {
            return 0;
        }
        return $attachment_id;
    }

    private function get_candidate_cv_formatted_status($candidate_id) {
        $candidate_id = (int) $candidate_id;
        $attachment_id = $this->get_candidate_cv_formatted_attachment_id($candidate_id);
        $generated_at = (string) get_post_meta($candidate_id, 'cmn_cv_formatted_generated_at', true);
        $generated_by = (int) get_post_meta($candidate_id, 'cmn_cv_formatted_generated_by', true);
        $version = (string) get_post_meta($candidate_id, 'cmn_cv_formatted_version', true);
        $generated_by_name = '';
        if ($generated_by > 0) {
            $user = get_user_by('id', $generated_by);
            if ($user instanceof WP_User) {
                $generated_by_name = (string) $user->display_name;
            }
        }
        $filename = '';
        $filesize_label = '';
        if ($attachment_id > 0) {
            $file = get_attached_file($attachment_id);
            if ($file) {
                $filename = basename($file);
                $size = @filesize($file);
                if ($size !== false && (int) $size > 0) {
                    $filesize_label = size_format((int) $size);
                }
            }
        }
        return [
            'available' => $attachment_id > 0,
            'attachment_id' => $attachment_id,
            'filename' => $filename,
            'filesize_label' => $filesize_label,
            'generated_at' => $generated_at,
            'generated_at_label' => $generated_at !== '' ? date_i18n('M j, Y g:ia', strtotime($generated_at)) : '',
            'generated_by' => $generated_by,
            'generated_by_name' => $generated_by_name,
            'version' => $version,
            'outdated' => (string) get_post_meta($candidate_id, 'cmn_cv_formatted_outdated', true) === '1',
        ];
    }

    private function mark_candidate_formatted_cv_outdated($candidate_id, $notify = true) {
        $candidate_id = (int) $candidate_id;
        if (!$candidate_id) {
            return false;
        }
        $has_formatted = (int) get_post_meta($candidate_id, 'cmn_cv_formatted_attachment_id', true) > 0;
        if (!$has_formatted) {
            return false;
        }
        update_post_meta($candidate_id, 'cmn_cv_formatted_outdated', '1');
        if ($notify) {
            $this->add_candidate_cv_reconversion_notification($candidate_id);
        }
        return true;
    }

    private function get_cv_converter_token_transient_key($token) {
        $token = trim((string) $token);
        if ($token === '') {
            return '';
        }
        return 'cmn_cv_converter_' . substr(hash('sha256', $token), 0, 40);
    }

    private function create_cv_converter_token($candidate_id, $staff_user_id, $ttl = 600) {
        $candidate_id = (int) $candidate_id;
        $staff_user_id = (int) $staff_user_id;
        if ($candidate_id < 1 || $staff_user_id < 1) {
            return '';
        }
        $token = wp_generate_password(48, false, false);
        $key = $this->get_cv_converter_token_transient_key($token);
        if ($key === '') {
            return '';
        }
        set_transient($key, [
            'candidate_id' => $candidate_id,
            'staff_user_id' => $staff_user_id,
            'created_at' => time(),
        ], max(60, (int) $ttl));
        return $token;
    }

    private function validate_cv_converter_token($token, $candidate_id, $staff_user_id) {
        $candidate_id = (int) $candidate_id;
        $staff_user_id = (int) $staff_user_id;
        $key = $this->get_cv_converter_token_transient_key($token);
        if ($candidate_id < 1 || $staff_user_id < 1 || $key === '') {
            return new WP_Error('cmn_invalid_cv_token', 'Invalid CV converter token.');
        }
        $payload = get_transient($key);
        if (!is_array($payload)) {
            return new WP_Error('cmn_expired_cv_token', 'CV converter token expired. Please reopen converter.');
        }
        if ((int) ($payload['candidate_id'] ?? 0) !== $candidate_id || (int) ($payload['staff_user_id'] ?? 0) !== $staff_user_id) {
            return new WP_Error('cmn_invalid_cv_token', 'Invalid CV converter token.');
        }
        return $payload;
    }

    private function get_current_url() {
        $scheme = is_ssl() ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return $scheme . $host . $uri;
    }

    private function get_portal_login_url() {
        return add_query_arg(['view' => 'login'], $this->get_portal_base_url());
    }

    private function get_portal_forgot_url() {
        return add_query_arg(['view' => 'forgot-password'], $this->get_portal_base_url());
    }

    private function get_portal_reset_url($login, $key) {
        return add_query_arg([
            'view' => 'set-password',
            'login' => rawurlencode($login),
            'key' => rawurlencode($key),
        ], $this->get_portal_base_url());
    }

    private function get_candidate_verify_url($user_id, $token, $login = '', $key = '') {
        $args = [
            'view' => 'candidate-verify',
            'uid' => (int) $user_id,
            'token' => rawurlencode($token),
        ];
        if ($login !== '' && $key !== '') {
            $args['login'] = rawurlencode($login);
            $args['key'] = rawurlencode($key);
        }
        return add_query_arg($args, $this->get_portal_base_url());
    }

    public function handle_login_redirect($redirect_to, $requested_redirect_to, $user) {
        if (!$user || is_wp_error($user)) {
            return $redirect_to;
        }
        $user_id = isset($user->ID) ? (int) $user->ID : 0;
        if (!$user_id) {
            return $redirect_to;
        }
        $portal_base = $this->get_portal_base_url();
        if ($this->is_admin_user($user_id) || $this->is_staff_role($user_id) || $this->is_account_manager_user($user_id)) {
            return add_query_arg(['view' => 'dashboard'], $portal_base);
        }
        if ($this->is_candidate_user($user_id)) {
            return add_query_arg(['view' => 'candidate-dashboard'], $portal_base);
        }
        if ($this->is_school_user($user_id)) {
            return add_query_arg(['view' => 'school-dashboard'], $portal_base);
        }
        return $portal_base;
    }

    public function handle_logout_redirect($redirect_to, $requested_redirect_to, $user) {
        return $this->get_portal_base_url();
    }

    public function block_wp_admin_for_non_admins() {
        if (!is_user_logged_in()) {
            return;
        }
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        if (defined('DOING_CRON') && DOING_CRON) {
            return;
        }
        if ($this->is_wordpress_admin_user()) {
            return;
        }
        if (is_admin()) {
            wp_redirect($this->get_portal_base_url());
            exit;
        }
    }

    public function redirect_legacy_portal_paths() {
        if (is_admin()) {
            return;
        }
        $path = wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        if (!$path) {
            return;
        }
        $legacy_paths = [
            '/covermenow-one',
            '/covermenow-one/',
            '/covermenowone-one',
            '/covermenowone-one/',
        ];
        if (!in_array($path, $legacy_paths, true)) {
            return;
        }
        $query = wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
        $target = $this->get_portal_base_url();
        if ($query) {
            $target = $target . (strpos($target, '?') === false ? '?' : '&') . $query;
        }
        wp_safe_redirect($target, 301);
        exit;
    }

    public function redirect_wp_login_for_portal_users() {
        if (is_user_logged_in() && $this->is_wordpress_admin_user()) {
            return;
        }
        if (isset($_GET['cmn_admin']) && $_GET['cmn_admin'] === '1') {
            return;
        }
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'login';
        if ($action === 'logout') {
            return;
        }
        if (in_array($action, ['rp', 'resetpass'], true)) {
            $key = sanitize_text_field($_REQUEST['key'] ?? '');
            $login = sanitize_text_field($_REQUEST['login'] ?? '');
            if ($key && $login) {
                wp_redirect($this->get_portal_reset_url($login, $key));
                exit;
            }
        }
        if ($action === 'lostpassword') {
            wp_redirect($this->get_portal_forgot_url());
            exit;
        }
        wp_redirect($this->get_portal_login_url());
        exit;
    }

    public function configure_candidate_upload_runtime_limits() {
        if (defined('WP_CLI') && WP_CLI) {
            return;
        }
        @ini_set('upload_max_filesize', '32M');
        @ini_set('post_max_size', '64M');
        @ini_set('memory_limit', '256M');
        @ini_set('max_execution_time', '120');
        @ini_set('max_input_time', '120');
    }

    private function get_candidate_doc_target_limit_bytes() {
        return 20 * 1024 * 1024;
    }

    public function filter_candidate_upload_size_limit($size) {
        $target = $this->get_candidate_doc_target_limit_bytes();
        if ($size < $target) {
            return $target;
        }
        return $size;
    }

    private function get_candidate_doc_allowed_mimes($doc_type = '') {
        $common = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];
        if ($doc_type === 'cv' || $doc_type === '') {
            $common['doc'] = 'application/msword';
            $common['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        }
        return $common;
    }

    public function prefilter_candidate_doc_upload($file) {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            return $file;
        }
        $action = isset($_REQUEST['action']) ? sanitize_key((string) $_REQUEST['action']) : '';
        if ($action !== 'cmn_candidate_upload_doc') {
            return $file;
        }

        $doc_type = sanitize_key((string) ($_REQUEST['doc_type'] ?? ''));
        $allowed = $this->get_candidate_doc_allowed_mimes($doc_type);
        $extension = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!$extension || !isset($allowed[$extension])) {
            $file['error'] = 'File type not allowed. Upload PDF, JPG, JPEG, PNG' . ($doc_type === 'cv' ? ', DOC or DOCX.' : '.');
            return $file;
        }

        $effective_limit = (int) wp_max_upload_size();
        $target_limit = (int) $this->get_candidate_doc_target_limit_bytes();
        $requested_max = max(1, min($target_limit, $effective_limit > 0 ? $effective_limit : $target_limit));
        $size = isset($file['size']) ? (int) $file['size'] : 0;
        if ($size > $requested_max) {
            $msg = 'File is too large. Maximum supported upload is ' . size_format($requested_max) . '.';
            if ($effective_limit > 0 && $effective_limit < $target_limit) {
                $msg .= ' Hosting limits are currently too low. Please ask support to raise upload_max_filesize/post_max_size.';
            }
            $file['error'] = $msg;
            return $file;
        }

        return $file;
    }

    private function get_candidate_doc_host_limit_warning() {
        $effective_limit = (int) wp_max_upload_size();
        $target_limit = (int) $this->get_candidate_doc_target_limit_bytes();
        if ($effective_limit >= $target_limit) {
            return '';
        }
        return 'Hosting upload limits are too low for candidate documents. Current limit: '
            . size_format(max(1, $effective_limit))
            . '. Recommended minimum: '
            . size_format($target_limit)
            . '. Increase PHP upload_max_filesize and post_max_size in hosting settings.';
    }

    public function maybe_hide_admin_bar($show) {
        if (!is_user_logged_in()) {
            return $show;
        }
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return $show;
        }
        if (in_array('administrator', (array) $user->roles, true)) {
            return $show;
        }
        return false;
    }

    public function enqueue_login_branding() {
        wp_enqueue_style('cmn-login', plugin_dir_url(__FILE__) . 'login.css', [], self::VERSION);
    }

    public function login_header_url() {
        return $this->get_portal_base_url();
    }

    public function login_header_text() {
        return 'CoverMeNow ONE';
    }

    public function login_brand_message($message) {
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : 'login';
        $title = 'CoverMeNow ONE';
        $subtitle = 'Sign in to continue';
        if ($action === 'rp' || $action === 'resetpass') {
            $subtitle = 'Set your password';
        } elseif ($action === 'lostpassword') {
            $subtitle = 'Reset your password';
        } elseif ($action === 'checkemail') {
            $subtitle = 'Check your email';
        }
        $banner = '<div class="cmn-login-banner"><h2>' . esc_html($title) . '</h2><p>' . esc_html($subtitle) . '</p></div>';
        return $banner . $message;
    }

    public function login_footer_link() {
        echo '<p class="cmn-login-back"><a href="' . esc_url($this->get_portal_base_url()) . '">Back to portal</a></p>';
    }

    private function get_support_ticket_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_support_tickets';
    }

    private function get_support_message_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_support_messages';
    }

    private function get_support_feedback_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_support_feedback';
    }

    private function get_support_transcript_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_support_transcripts';
    }

    private function get_support_transcript_email_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_support_transcript_emails';
    }

    private function get_support_ticket_columns() {
        static $columns = null;
        if (is_array($columns)) {
            return $columns;
        }
        global $wpdb;
        $table = $this->get_support_ticket_table();
        $rows = $wpdb->get_results("SHOW COLUMNS FROM {$table}", ARRAY_A);
        $columns = [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!empty($row['Field'])) {
                    $columns[] = (string) $row['Field'];
                }
            }
        }
        return $columns;
    }

    private function support_ticket_has_column($column) {
        $column = (string) $column;
        if ($column === '') {
            return false;
        }
        return in_array($column, $this->get_support_ticket_columns(), true);
    }

    private function get_support_owner_user_id($ticket) {
        if (!is_array($ticket)) {
            return 0;
        }
        if (isset($ticket['created_by_user_id']) && (int) $ticket['created_by_user_id'] > 0) {
            return (int) $ticket['created_by_user_id'];
        }
        if (isset($ticket['user_id']) && (int) $ticket['user_id'] > 0) {
            return (int) $ticket['user_id'];
        }
        return 0;
    }

    private function normalize_support_status($status) {
        $status = sanitize_key((string) $status);
        if (in_array($status, ['closed', 'resolved', 'done'], true)) {
            return 'closed';
        }
        if (in_array($status, ['new'], true)) {
            return 'new';
        }
        if (in_array($status, ['awaiting_admin', 'awaiting_user', 'reopened', 'reply', 'open'], true)) {
            return 'open';
        }
        if ($status === '') {
            return 'open';
        }
        return 'open';
    }

    private function normalize_support_ticket_for_view($ticket, $is_staff_view = false) {
        $normalized = $this->normalize_support_status($ticket['status'] ?? '');
        if (!$is_staff_view && $normalized === 'new') {
            $normalized = 'open';
        }
        if ($normalized !== 'closed' && $is_staff_view && !empty($ticket['is_new_for_admin'])) {
            $normalized = 'new';
        }
        $ticket['status'] = $normalized;
        return $ticket;
    }

    private function get_support_message_attachments($message_row) {
        $ids = [];
        if (!empty($message_row['attachment_ids'])) {
            $raw = json_decode((string) $message_row['attachment_ids'], true);
            if (is_array($raw)) {
                foreach ($raw as $id) {
                    $id = (int) $id;
                    if ($id > 0) {
                        $ids[] = $id;
                    }
                }
            }
        }
        $attachments = [];
        foreach ($ids as $id) {
            $url = wp_get_attachment_url($id);
            if (!$url) {
                continue;
            }
            $path = get_attached_file($id);
            $attachments[] = [
                'id' => $id,
                'filename' => $path ? basename($path) : ('Attachment #' . $id),
                'url' => esc_url_raw($url),
            ];
        }
        return $attachments;
    }

    private function handle_support_attachments_upload($field_name = 'attachments') {
        $results = [
            'ids' => [],
            'errors' => [],
        ];
        if (empty($_FILES[$field_name]) || !is_array($_FILES[$field_name])) {
            return $results;
        }
        $files = $_FILES[$field_name];
        $names = isset($files['name']) && is_array($files['name']) ? $files['name'] : [$files['name']];
        $types = isset($files['type']) && is_array($files['type']) ? $files['type'] : [$files['type']];
        $tmp_names = isset($files['tmp_name']) && is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
        $errors = isset($files['error']) && is_array($files['error']) ? $files['error'] : [$files['error']];
        $sizes = isset($files['size']) && is_array($files['size']) ? $files['size'] : [$files['size']];

        $allowed = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'txt' => 'text/plain',
        ];

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        for ($i = 0; $i < count($names); $i++) {
            $name = (string) ($names[$i] ?? '');
            $tmp = (string) ($tmp_names[$i] ?? '');
            $error = (int) ($errors[$i] ?? UPLOAD_ERR_NO_FILE);
            $size = (int) ($sizes[$i] ?? 0);
            if ($error === UPLOAD_ERR_NO_FILE || $name === '' || $tmp === '') {
                continue;
            }
            if ($error !== UPLOAD_ERR_OK) {
                $results['errors'][] = $name . ': upload error.';
                continue;
            }
            if ($size > 10 * 1024 * 1024) {
                $results['errors'][] = $name . ': file is over 10MB.';
                continue;
            }
            $ext = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
            if (!isset($allowed[$ext])) {
                $results['errors'][] = $name . ': invalid file type.';
                continue;
            }
            $single = [
                'name' => $name,
                'type' => (string) ($types[$i] ?? ''),
                'tmp_name' => $tmp,
                'error' => $error,
                'size' => $size,
            ];
            $uploaded = wp_handle_upload($single, [
                'test_form' => false,
                'mimes' => $allowed,
            ]);
            if (!empty($uploaded['error'])) {
                $results['errors'][] = $name . ': ' . sanitize_text_field((string) $uploaded['error']);
                continue;
            }
            $attachment_id = wp_insert_attachment([
                'post_mime_type' => $uploaded['type'] ?? '',
                'post_title' => sanitize_file_name(pathinfo($name, PATHINFO_FILENAME)),
                'post_status' => 'inherit',
                'guid' => $uploaded['url'] ?? '',
            ], $uploaded['file'] ?? '');
            if (is_wp_error($attachment_id) || !$attachment_id) {
                $results['errors'][] = $name . ': failed to save.';
                continue;
            }
            $meta = wp_generate_attachment_metadata($attachment_id, $uploaded['file']);
            if (!is_wp_error($meta)) {
                wp_update_attachment_metadata($attachment_id, $meta);
            }
            $results['ids'][] = (int) $attachment_id;
        }
        return $results;
    }

    private function get_support_feedback($ticket_id, $user_id = 0) {
        global $wpdb;
        $table = $this->get_support_feedback_table();
        $ticket_id = (int) $ticket_id;
        if (!$ticket_id) {
            return null;
        }
        if ($user_id) {
            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table} WHERE ticket_id = %d AND user_id = %d",
                $ticket_id,
                (int) $user_id
            ), ARRAY_A);
        }
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE ticket_id = %d ORDER BY created_at DESC LIMIT 1",
            $ticket_id
        ), ARRAY_A);
    }

    private function build_support_transcript_text($ticket, $messages) {
        $lines = [];
        $lines[] = 'CoverMeNow ONE Support Transcript';
        $lines[] = 'Ticket Ref: ' . ($ticket['ticket_ref'] ?? '');
        $lines[] = 'Status: ' . ucfirst($this->normalize_support_status($ticket['status'] ?? 'open'));
        $lines[] = 'Created: ' . ($ticket['created_at'] ?? '');
        $lines[] = 'Updated: ' . ($ticket['updated_at'] ?? '');
        if (!empty($ticket['closed_at'])) {
            $lines[] = 'Closed: ' . $ticket['closed_at'];
        }
        $lines[] = '';
        foreach ($messages as $msg) {
            $sender = $msg['sender_name'] ?? 'Support';
            $date = $msg['created_at'] ?? '';
            $lines[] = '[' . $date . '] ' . $sender . ': ' . ($msg['message'] ?? '');
            if (!empty($msg['attachments']) && is_array($msg['attachments'])) {
                foreach ($msg['attachments'] as $attachment) {
                    $lines[] = '  - Attachment: ' . ($attachment['filename'] ?? 'file') . ' (' . ($attachment['url'] ?? '') . ')';
                }
            }
        }
        return implode("\n", $lines);
    }

    private function generate_support_ticket_ref() {
        global $wpdb;
        $table = $this->get_support_ticket_table();
        $feedback_table = $this->get_support_feedback_table();
        $max_id = (int) $wpdb->get_var("SELECT MAX(id) FROM {$table}");
        $next = $max_id + 1;
        do {
            $ref = 'CMN-SUP-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $exists = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE ticket_ref = %s",
                $ref
            ));
            $next++;
        } while ($exists > 0);
        return $ref;
    }

    private function get_support_user_role_type($user_id) {
        if ($this->is_candidate_user($user_id)) {
            return 'candidate';
        }
        if ($this->is_school_user($user_id)) {
            return 'school';
        }
        return 'staff';
    }

    private function can_access_support_ticket($ticket, $user_id = 0) {
        if (!$ticket) {
            return false;
        }
        $user_id = $user_id ?: get_current_user_id();
        if ($this->is_staff_user($user_id)) {
            return true;
        }
        if ((int) $this->get_support_owner_user_id($ticket) !== (int) $user_id) {
            return false;
        }
        $expected_role = $this->get_support_user_role_type($user_id);
        $ticket_role = sanitize_key((string) ($ticket['user_role_type'] ?? ''));
        if ($ticket_role === '') {
            return true;
        }
        return $ticket_role === $expected_role;
    }

    private function get_support_ticket($ticket_id) {
        global $wpdb;
        $table = $this->get_support_ticket_table();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $ticket_id), ARRAY_A);
    }

    private function get_support_ticket_by_ref($ticket_ref) {
        $ticket_ref = sanitize_text_field((string) $ticket_ref);
        if ($ticket_ref === '') {
            return null;
        }
        global $wpdb;
        $table = $this->get_support_ticket_table();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE ticket_ref = %s", $ticket_ref), ARRAY_A);
    }

    private function resolve_support_requested_ticket($ticket_id = 0, $ticket_ref = '') {
        $ticket = null;
        $ticket_id = (int) $ticket_id;
        $ticket_ref = sanitize_text_field((string) $ticket_ref);
        if ($ticket_id > 0) {
            $ticket = $this->get_support_ticket($ticket_id);
        } elseif ($ticket_ref !== '') {
            $ticket = $this->get_support_ticket_by_ref($ticket_ref);
        }
        return $ticket ?: null;
    }

    private function map_support_filter_for_status($normalized_status) {
        $normalized_status = $this->normalize_support_status($normalized_status);
        if ($normalized_status === 'new') {
            return 'new';
        }
        if ($normalized_status === 'closed') {
            return 'closed';
        }
        return 'open';
    }

    private function support_ticket_matches_filter($ticket, $filter) {
        $filter = sanitize_key((string) $filter);
        $normalized = $this->normalize_support_ticket_for_view($ticket, $this->is_staff_user());
        $status = $normalized['status'] ?? 'open';
        $feedback_count = isset($normalized['feedback_count']) ? (int) $normalized['feedback_count'] : 0;
        if ($filter === 'all') {
            return true;
        }
        if ($filter === 'active' || $filter === '' || $filter === 'new_open') {
            return in_array($status, ['new', 'open'], true);
        }
        if ($filter === 'new') {
            return $status === 'new';
        }
        if ($filter === 'open') {
            return $status === 'open';
        }
        if ($filter === 'closed') {
            return $status === 'closed';
        }
        if ($filter === 'needs_feedback') {
            return $status === 'closed' && $feedback_count < 1;
        }
        return true;
    }

    private function insert_support_system_message_once($ticket_id, $message, $sender_user_id = 0, $sender_type = 'admin') {
        global $wpdb;
        $ticket_id = (int) $ticket_id;
        $message = trim((string) $message);
        if (!$ticket_id || $message === '') {
            return;
        }
        $table = $this->get_support_message_table();
        $last = $wpdb->get_row($wpdb->prepare(
            "SELECT sender_type, message, created_at FROM {$table} WHERE ticket_id = %d ORDER BY id DESC LIMIT 1",
            $ticket_id
        ), ARRAY_A);
        if ($last) {
            $same_sender = sanitize_key((string) ($last['sender_type'] ?? '')) === sanitize_key($sender_type);
            $same_message = trim((string) ($last['message'] ?? '')) === $message;
            $within_window = false;
            if (!empty($last['created_at'])) {
                $within_window = (time() - strtotime((string) $last['created_at'])) <= 30;
            }
            if ($same_sender && $same_message && $within_window) {
                return;
            }
        }
        $wpdb->insert($table, [
            'ticket_id' => $ticket_id,
            'sender_user_id' => (int) $sender_user_id,
            'sender_type' => sanitize_key($sender_type),
            'message' => $message,
            'attachment_ids' => null,
            'created_at' => current_time('mysql'),
        ], ['%d', '%d', '%s', '%s', '%s', '%s']);
    }

    private function get_support_ticket_messages($ticket_id) {
        global $wpdb;
        $table = $this->get_support_message_table();
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE ticket_id = %d ORDER BY created_at ASC",
            $ticket_id
        ), ARRAY_A);
    }

    private function get_admin_users_for_support() {
        $admins = get_users([
            'role__in' => ['administrator', 'cmn_admin'],
            'fields' => ['ID'],
        ]);
        return array_map(function ($user) {
            return (int) $user->ID;
        }, $admins);
    }

    private function notify_admins_support($ticket_id, $ticket_ref, $subject) {
        foreach ($this->get_admin_users_for_support() as $admin_id) {
            $portal_link = $this->get_support_link_for_user($admin_id, $ticket_id, $ticket_ref);
            $this->add_notification($admin_id, 'support_new_ticket', 'New support ticket', "{$ticket_ref} – {$subject}", $portal_link);
        }
    }

    private function notify_admins_support_reply($ticket_id, $ticket_ref) {
        foreach ($this->get_admin_users_for_support() as $admin_id) {
            $portal_link = $this->get_support_link_for_user($admin_id, $ticket_id, $ticket_ref);
            $this->add_notification($admin_id, 'support_reply', 'Support ticket reply', "{$ticket_ref} updated.", $portal_link);
        }
    }

    private function get_support_link_for_user($user_id, $ticket_id, $ticket_ref = '') {
        $ticket_value = (int) $ticket_id;
        if ($ticket_value < 1 && $ticket_ref !== '') {
            $ticket = $this->get_support_ticket_by_ref($ticket_ref);
            if ($ticket) {
                $ticket_value = (int) $ticket['id'];
            }
        }
        if ($ticket_value < 1) {
            return $this->get_portal_base_url();
        }
        if ($this->is_candidate_user($user_id)) {
            return add_query_arg([
                'candidate' => 'support',
                'ticket_id' => $ticket_value,
            ], $this->get_portal_base_url());
        }
        if ($this->is_school_user($user_id)) {
            return add_query_arg([
                'school' => 'support',
                'ticket_id' => $ticket_value,
            ], $this->get_portal_base_url());
        }
        return add_query_arg([
            'view' => 'support',
            'hub' => '1',
            'ticket_id' => $ticket_value,
        ], $this->get_portal_base_url());
    }

    private function extract_ticket_ref_from_text($text) {
        $text = (string) $text;
        if (preg_match('/CMN-SUP-\d{6}/i', $text, $matches)) {
            return strtoupper($matches[0]);
        }
        return '';
    }

    private function resolve_support_notification_link($item, $user_id, $fallback = '') {
        $type = sanitize_key((string) ($item['type'] ?? ''));
        if (!in_array($type, ['support_reply', 'support_new', 'support_new_ticket', 'support_ticket_closed', 'support_ticket_reopened', 'support_feedback'], true)) {
            return $fallback;
        }
        if (!empty($item['link_url']) && strpos((string) $item['link_url'], 'ticket_id=') !== false) {
            return $item['link_url'];
        }
        $ticket_ref = $this->extract_ticket_ref_from_text(($item['message'] ?? '') . ' ' . ($item['title'] ?? ''));
        $ticket_id = 0;
        if ($ticket_ref !== '') {
            $ticket = $this->get_support_ticket_by_ref($ticket_ref);
            if ($ticket) {
                $ticket_id = (int) $ticket['id'];
            }
        }
        return $this->get_support_link_for_user($user_id, $ticket_id, $ticket_ref);
    }

    private function send_support_email_to_user($user_id, $subject, $message, $log_type = 'support_update') {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }
        if ($this->is_candidate_user($user_id)) {
            return $this->send_candidate_email($user->user_email, $subject, $message, [
                'type' => $log_type,
            ]);
        }
        $previous_log_type = $GLOBALS['cmn_school_mail_log_type'] ?? null;
        $GLOBALS['cmn_school_mail_log_type'] = $log_type;
        $sent = $this->send_school_email($user->user_email, $subject, $message);
        if ($previous_log_type !== null) {
            $GLOBALS['cmn_school_mail_log_type'] = $previous_log_type;
        } else {
            unset($GLOBALS['cmn_school_mail_log_type']);
        }
        return $sent;
    }

    private function can_manage_staff_users($user_id = 0) {
        $user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();
        if (!$user || !$user->ID) {
            return false;
        }
        if ($this->is_super_admin_user()) {
            return true;
        }
        return in_array('cmn_admin', (array) $user->roles, true);
    }

    private function get_candidate_ids_with_deletion_request() {
        $user_ids = get_users([
            'fields' => 'ID',
            'meta_key' => 'cmn_deletion_requested',
            'meta_value' => '1',
        ]);
        if (!$user_ids) {
            return [];
        }
        $candidate_posts = get_posts([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'cmn_user_id',
                    'value' => array_map('intval', (array) $user_ids),
                    'compare' => 'IN',
                    'type' => 'NUMERIC',
                ],
            ],
        ]);
        return array_map('intval', (array) $candidate_posts);
    }

    private function is_super_admin_user_id($user_id) {
        if (!$user_id) {
            return false;
        }
        $user = get_user_by('id', $user_id);
        if (!$user || !$user->ID) {
            return false;
        }
        return strtolower($user->user_email) === 'jaynorton17@gmail.com';
    }

    private function generate_unique_username($seed) {
        $base = sanitize_user($seed, true);
        if ($base === '') {
            $base = 'staff';
        }
        $username = $base;
        $suffix = 2;
        while (username_exists($username)) {
            $username = $base . '-' . $suffix;
            $suffix++;
        }
        return $username;
    }

    private function build_password_reset_link($user) {
        if (!$user instanceof WP_User) {
            return '';
        }
        $key = get_password_reset_key($user);
        if (is_wp_error($key)) {
            return '';
        }
        return $this->get_portal_reset_url($user->user_login, $key);
    }

    private function send_staff_welcome_email($user, $plain_password = '') {
        if (!$user instanceof WP_User) {
            return false;
        }
        $login_url = $this->get_portal_base_url();
        $reset_link = $this->build_password_reset_link($user);
        $subject = 'Your CoverMeNow ONE Staff Login';
        $message = "Welcome to CoverMeNow ONE.\n\nUsername: {$user->user_login}\nLogin: {$login_url}\n\nSet your password here:\n{$reset_link}\n\nIf you need any help, reply to this email.";
        $previous_log_type = $GLOBALS['cmn_school_mail_log_type'] ?? null;
        $GLOBALS['cmn_school_mail_log_type'] = 'staff_welcome';
        $sent = $this->send_school_email($user->user_email, $subject, $message);
        if ($previous_log_type !== null) {
            $GLOBALS['cmn_school_mail_log_type'] = $previous_log_type;
        } else {
            unset($GLOBALS['cmn_school_mail_log_type']);
        }
        return $sent;
    }

    private function send_staff_reset_email($user) {
        if (!$user instanceof WP_User) {
            return false;
        }
        $reset_link = $this->build_password_reset_link($user);
        if ($reset_link === '') {
            return false;
        }
        $subject = 'Reset your CoverMeNow ONE password';
        $message = "Use the link below to reset your password:\n{$reset_link}\n\nIf you did not request this, please ignore this email.";
        $previous_log_type = $GLOBALS['cmn_school_mail_log_type'] ?? null;
        $GLOBALS['cmn_school_mail_log_type'] = 'staff_reset';
        $sent = $this->send_school_email($user->user_email, $subject, $message);
        if ($previous_log_type !== null) {
            $GLOBALS['cmn_school_mail_log_type'] = $previous_log_type;
        } else {
            unset($GLOBALS['cmn_school_mail_log_type']);
        }
        return $sent;
    }

    private function log_mail_context_error($message) {
        error_log('CMN Mail Context Error: ' . $message);
    }

    private function get_candidate_from_email() {
        return defined('CMN_SMTP_FROM_EMAIL') ? CMN_SMTP_FROM_EMAIL : 'candidate@covermenow.co.uk';
    }

    private function get_candidate_from_name() {
        return defined('CMN_SMTP_FROM_NAME') ? CMN_SMTP_FROM_NAME : 'CoverMeNow Candidates';
    }

    private function get_school_from_email() {
        return defined('CMN_SCHOOL_SMTP_FROM_EMAIL') ? CMN_SCHOOL_SMTP_FROM_EMAIL : 'school@covermenow.co.uk';
    }

    private function get_school_from_name() {
        return defined('CMN_SCHOOL_SMTP_FROM_NAME') ? CMN_SCHOOL_SMTP_FROM_NAME : 'CoverMeNow Schools';
    }

    private function begin_candidate_mail_context() {
        if (!empty($GLOBALS['cmn_school_mail_context'])) {
            $this->log_mail_context_error('Attempted to set candidate context while school context active.');
            return null;
        }
        $already = !empty($GLOBALS['cmn_candidate_mail_context']);
        $GLOBALS['cmn_candidate_mail_context'] = true;
        return $already ? false : true;
    }

    private function begin_school_mail_context() {
        if (!empty($GLOBALS['cmn_candidate_mail_context'])) {
            $this->log_mail_context_error('Attempted to set school context while candidate context active.');
            return null;
        }
        $already = !empty($GLOBALS['cmn_school_mail_context']);
        $GLOBALS['cmn_school_mail_context'] = true;
        return $already ? false : true;
    }

    private function send_cmn_mail($to, $subject, $message, $from_email, $from_name = 'CoverMeNow ONE', $reply_to = '', $log_context = []) {
        if (!$to) {
            return false;
        }
        $headers = [];
        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
        if ($reply_to) {
            $headers[] = 'Reply-To: ' . $reply_to;
        }
        $sent = wp_mail($to, $subject, $message, $headers);
        $this->log_email($to, $subject, $log_context, $sent ? 'sent' : 'failed');
        return $sent;
    }

    private function get_candidate_notification_pref_defaults() {
        return [
            'cmn_notify_email_support_updates' => '1',
            'cmn_notify_email_booking_request' => '1',
            'cmn_notify_email_booking_confirmed' => '1',
            'cmn_notify_email_booking_cancelled' => '1',
            'cmn_notify_email_profile_reminders' => '1',
            'cmn_notify_email_learning_courses' => '1',
        ];
    }

    private function get_theme_scheme_choices() {
        return [
            'default' => 'CMN ONE Default',
            'contrast' => 'High Contrast Dark',
            'light' => 'Light Mode',
            'teal' => 'Alternative Accent',
        ];
    }

    private function normalize_theme_scheme($scheme) {
        $scheme = sanitize_key((string) $scheme);
        if ($scheme === 'midnight') {
            $scheme = 'contrast';
        }
        $allowed = array_keys($this->get_theme_scheme_choices());
        if (!in_array($scheme, $allowed, true)) {
            return 'default';
        }
        return $scheme;
    }

    private function get_user_theme_scheme($user_id) {
        $user_id = (int) $user_id;
        if ($user_id < 1) {
            return 'default';
        }
        $scheme = get_user_meta($user_id, 'cmn_theme_scheme', true);
        if ($scheme === '') {
            $legacy = get_user_meta($user_id, 'cmn_theme', true);
            if ($legacy !== '') {
                $scheme = $legacy;
            }
        }
        return $this->normalize_theme_scheme($scheme);
    }

    private function update_user_theme_scheme($user_id, $scheme) {
        $user_id = (int) $user_id;
        if ($user_id < 1) {
            return;
        }
        $normalized = $this->normalize_theme_scheme($scheme);
        update_user_meta($user_id, 'cmn_theme_scheme', $normalized);
        update_user_meta($user_id, 'cmn_theme', $normalized);
    }

    private function ensure_candidate_notification_defaults($user_id) {
        $user_id = (int) $user_id;
        if (!$user_id) {
            return;
        }
        foreach ($this->get_candidate_notification_pref_defaults() as $meta_key => $default) {
            $existing = get_user_meta($user_id, $meta_key, true);
            if ($existing === '') {
                update_user_meta($user_id, $meta_key, $default);
            }
        }
        $this->update_user_theme_scheme($user_id, $this->get_user_theme_scheme($user_id));
    }

    private function resolve_candidate_notify_key_from_context($context) {
        if (!empty($context['notify_key'])) {
            return sanitize_key((string) $context['notify_key']);
        }
        $type = sanitize_key((string) ($context['type'] ?? ''));
        $map = [
            'support_ticket_received' => 'cmn_notify_email_support_updates',
            'support_ticket_reply' => 'cmn_notify_email_support_updates',
            'candidate_availability_request' => 'cmn_notify_email_booking_request',
            'candidate_booking_confirmed' => 'cmn_notify_email_booking_confirmed',
            'candidate_booking_update' => 'cmn_notify_email_booking_confirmed',
            'candidate_booking_declined' => 'cmn_notify_email_booking_cancelled',
            'candidate_status_update' => 'cmn_notify_email_profile_reminders',
            'learning_opt_in_confirmation' => 'cmn_notify_email_learning_courses',
        ];
        return $map[$type] ?? '';
    }

    private function can_send_candidate_email_for_context($to, $context) {
        $notify_key = $this->resolve_candidate_notify_key_from_context($context);
        if ($notify_key === '') {
            return true;
        }
        $user_id = isset($context['user_id']) ? (int) $context['user_id'] : 0;
        if (!$user_id) {
            $user = get_user_by('email', (string) $to);
            if ($user instanceof WP_User) {
                $user_id = (int) $user->ID;
            }
        }
        if (!$user_id || !$this->is_candidate_user($user_id)) {
            return true;
        }
        $this->ensure_candidate_notification_defaults($user_id);
        return get_user_meta($user_id, $notify_key, true) !== '0';
    }

    public function send_candidate_email($to, $subject, $message, $context = []) {
        if (!$to) {
            return false;
        }
        if (!$this->can_send_candidate_email_for_context($to, $context)) {
            $this->log_email($to, $subject, $context, 'skipped', 'Candidate email preference disabled');
            return true;
        }
        $had_context = !empty($GLOBALS['cmn_candidate_mail_context']);
        $context_state = $this->begin_candidate_mail_context();
        if ($context_state === null) {
            return false;
        }
        $from_email = $this->get_candidate_from_email();
        $from_name = $this->get_candidate_from_name();
        $headers = [
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $from_email,
        ];
        $sent = wp_mail($to, $subject, $message, $headers);
        $this->log_email($to, $subject, $context, $sent ? 'sent' : 'failed');
        if ($had_context) {
            $GLOBALS['cmn_candidate_mail_context'] = true;
        } elseif ($context_state) {
            unset($GLOBALS['cmn_candidate_mail_context']);
        }
        return $sent;
    }

    public function send_school_email($to, $subject, $message, $headers = [], $attachments = []) {
        if (!$to) {
            return false;
        }
        $had_context = !empty($GLOBALS['cmn_school_mail_context']);
        $context_state = $this->begin_school_mail_context();
        if ($context_state === null) {
            return false;
        }
        $from_email = $this->get_school_from_email();
        $from_name = $this->get_school_from_name();
        $default_headers = [
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $from_email,
        ];
        if ($headers) {
            if (is_array($headers)) {
                $default_headers = array_merge($default_headers, $headers);
            } else {
                $default_headers[] = $headers;
            }
        }
        $sent = wp_mail($to, $subject, $message, $default_headers, $attachments);
        $log_type = !empty($GLOBALS['cmn_school_mail_log_type']) ? $GLOBALS['cmn_school_mail_log_type'] : 'school_email';
        $this->log_email($to, $subject, ['type' => $log_type], $sent ? 'sent' : 'failed');
        if ($had_context) {
            $GLOBALS['cmn_school_mail_context'] = true;
        } elseif ($context_state) {
            unset($GLOBALS['cmn_school_mail_context']);
        }
        return $sent;
    }

    public function configure_candidate_smtp($phpmailer) {
        if (!empty($GLOBALS['cmn_candidate_mail_context']) && !empty($GLOBALS['cmn_school_mail_context'])) {
            $this->log_mail_context_error('Both candidate and school mail contexts were active. Using candidate context.');
        }
        if (!empty($GLOBALS['cmn_candidate_mail_context'])) {
            if (!defined('CMN_SMTP_HOST') || !defined('CMN_SMTP_PORT') || !defined('CMN_SMTP_USERNAME') || !defined('CMN_SMTP_PASSWORD')) {
                return;
            }
            $phpmailer->isSMTP();
            $phpmailer->Host = CMN_SMTP_HOST;
            $phpmailer->Port = CMN_SMTP_PORT;
            $phpmailer->SMTPSecure = defined('CMN_SMTP_ENCRYPTION') ? CMN_SMTP_ENCRYPTION : '';
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = CMN_SMTP_USERNAME;
            $phpmailer->Password = CMN_SMTP_PASSWORD;
            if (defined('CMN_SMTP_FROM_EMAIL') && defined('CMN_SMTP_FROM_NAME')) {
                $phpmailer->setFrom(CMN_SMTP_FROM_EMAIL, CMN_SMTP_FROM_NAME, false);
                $phpmailer->addReplyTo(CMN_SMTP_FROM_EMAIL, CMN_SMTP_FROM_NAME);
            }
            return;
        }
        if (!empty($GLOBALS['cmn_school_mail_context'])) {
            if (!defined('CMN_SCHOOL_SMTP_HOST') || !defined('CMN_SCHOOL_SMTP_PORT') || !defined('CMN_SCHOOL_SMTP_USERNAME') || !defined('CMN_SCHOOL_SMTP_PASSWORD')) {
                return;
            }
            $phpmailer->isSMTP();
            $phpmailer->Host = CMN_SCHOOL_SMTP_HOST;
            $phpmailer->Port = CMN_SCHOOL_SMTP_PORT;
            $phpmailer->SMTPSecure = defined('CMN_SCHOOL_SMTP_ENCRYPTION') ? CMN_SCHOOL_SMTP_ENCRYPTION : '';
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = CMN_SCHOOL_SMTP_USERNAME;
            $phpmailer->Password = CMN_SCHOOL_SMTP_PASSWORD;
            if (defined('CMN_SCHOOL_SMTP_FROM_EMAIL') && defined('CMN_SCHOOL_SMTP_FROM_NAME')) {
                $phpmailer->setFrom(CMN_SCHOOL_SMTP_FROM_EMAIL, CMN_SCHOOL_SMTP_FROM_NAME, false);
                $phpmailer->addReplyTo(CMN_SCHOOL_SMTP_FROM_EMAIL, CMN_SCHOOL_SMTP_FROM_NAME);
            }
        }
    }

    public function filter_mail_from($from) {
        if (!empty($GLOBALS['cmn_candidate_mail_context']) && !empty($GLOBALS['cmn_school_mail_context'])) {
            $this->log_mail_context_error('Both mail contexts active during wp_mail_from. Using candidate sender.');
        }
        if (!empty($GLOBALS['cmn_candidate_mail_context'])) {
            return $this->get_candidate_from_email();
        }
        if (!empty($GLOBALS['cmn_school_mail_context'])) {
            return $this->get_school_from_email();
        }
        return $from;
    }

    public function filter_mail_from_name($name) {
        if (!empty($GLOBALS['cmn_candidate_mail_context']) && !empty($GLOBALS['cmn_school_mail_context'])) {
            $this->log_mail_context_error('Both mail contexts active during wp_mail_from_name. Using candidate sender.');
        }
        if (!empty($GLOBALS['cmn_candidate_mail_context'])) {
            return $this->get_candidate_from_name();
        }
        if (!empty($GLOBALS['cmn_school_mail_context'])) {
            return $this->get_school_from_name();
        }
        return $name;
    }

    public function handle_candidate_mail_failed($wp_error) {
        if (empty($GLOBALS['cmn_candidate_mail_context']) && empty($GLOBALS['cmn_school_mail_context'])) {
            return;
        }
        $message = $wp_error instanceof WP_Error ? $wp_error->get_error_message() : 'Unknown mail error';
        if (!empty($GLOBALS['cmn_candidate_mail_context']) && !empty($GLOBALS['cmn_school_mail_context'])) {
            $this->log_mail_context_error('Mail failed while both contexts were active.');
        }
        if (!empty($GLOBALS['cmn_candidate_mail_context'])) {
            error_log('CMN Candidate SMTP Error: ' . $message);
            unset($GLOBALS['cmn_candidate_mail_context']);
        }
        if (!empty($GLOBALS['cmn_school_mail_context'])) {
            error_log('CMN School SMTP Error: ' . $message);
            unset($GLOBALS['cmn_school_mail_context']);
        }
    }

    public function handle_candidate_mail_succeeded($mail_data) {
        if (!empty($GLOBALS['cmn_candidate_mail_context']) && !empty($GLOBALS['cmn_school_mail_context'])) {
            $this->log_mail_context_error('Mail succeeded while both contexts were active.');
        }
        if (!empty($GLOBALS['cmn_candidate_mail_context'])) {
            unset($GLOBALS['cmn_candidate_mail_context']);
        }
        if (!empty($GLOBALS['cmn_school_mail_context'])) {
            unset($GLOBALS['cmn_school_mail_context']);
        }
    }

    private function is_candidate_user_object($user) {
        if (!$user instanceof WP_User) {
            return false;
        }
        $roles = (array) $user->roles;
        return in_array('cmn_candidate', $roles, true) || in_array('cmn_candidate_pending', $roles, true);
    }

    public function flag_candidate_password_reset($message, $key, $user_login, $user_data) {
        if ($this->is_candidate_user_object($user_data)) {
            if (!empty($GLOBALS['cmn_school_mail_context'])) {
                $this->log_mail_context_error('Attempted to set candidate context for password reset while school context active.');
                return $message;
            }
            $GLOBALS['cmn_candidate_mail_context'] = true;
        }
        $user_id = $user_data instanceof WP_User ? $user_data->ID : 0;
        if ($user_id && $this->is_admin_user($user_id)) {
            return $message;
        }
        $reset_url = $this->get_portal_reset_url($user_login, $key);
        $body = "To reset your CoverMeNow ONE password, visit the following address:\n\n{$reset_url}\n\nIf you did not request this, please ignore this email.";
        return $body;
    }

    public function flag_candidate_password_reset_title($title, $user_login, $user_data) {
        if ($this->is_candidate_user_object($user_data)) {
            if (!empty($GLOBALS['cmn_school_mail_context'])) {
                $this->log_mail_context_error('Attempted to set candidate context for password reset title while school context active.');
                return $title;
            }
            $GLOBALS['cmn_candidate_mail_context'] = true;
        }
        return $title;
    }

    public function flag_candidate_password_change_email($pass_change_email, $user, $blogname) {
        if ($this->is_candidate_user_object($user)) {
            if (!empty($GLOBALS['cmn_school_mail_context'])) {
                $this->log_mail_context_error('Attempted to set candidate context for password change email while school context active.');
                return $pass_change_email;
            }
            $GLOBALS['cmn_candidate_mail_context'] = true;
        }
        return $pass_change_email;
    }

    private function get_notifications_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_notifications';
    }

    private function get_email_log_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_email_log';
    }

    private function log_email($to, $subject, $context = [], $status = 'sent', $error_message = '') {
        global $wpdb;
        $table = $this->get_email_log_table();
        $type = sanitize_text_field($context['type'] ?? 'general');
        $wpdb->insert($table, [
            'to_email' => sanitize_email($to),
            'subject' => sanitize_text_field($subject),
            'type' => $type,
            'related_school_domain' => sanitize_text_field($context['related_school_domain'] ?? ''),
            'related_candidate_id' => isset($context['related_candidate_id']) ? (int) $context['related_candidate_id'] : null,
            'related_request_id' => isset($context['related_request_id']) ? (int) $context['related_request_id'] : null,
            'sent_at' => current_time('mysql'),
            'status' => sanitize_text_field($status),
            'error_message' => sanitize_textarea_field($error_message),
        ], ['%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s']);
    }

    private function add_notification($user_id, $type, $title, $message = '', $link_url = '') {
        global $wpdb;
        $user_id = (int) $user_id;
        if (!$user_id) {
            return 0;
        }
        $table = $this->get_notifications_table();
        $wpdb->insert($table, [
            'user_id' => $user_id,
            'type' => sanitize_text_field($type),
            'title' => sanitize_text_field($title),
            'message' => sanitize_textarea_field($message),
            'link_url' => esc_url_raw($link_url),
            'is_read' => 0,
            'created_at' => current_time('mysql'),
        ], ['%d', '%s', '%s', '%s', '%s', '%d', '%s']);
        return (int) $wpdb->insert_id;
    }

    private function get_user_notifications($user_id, $limit = 10) {
        global $wpdb;
        $table = $this->get_notifications_table();
        $user_id = (int) $user_id;
        $limit = (int) $limit;
        if ($limit < 1) {
            $limit = 10;
        }
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
            $user_id,
            $limit
        ), ARRAY_A);
    }

    private function count_unread_notifications($user_id) {
        global $wpdb;
        $table = $this->get_notifications_table();
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND is_read = 0",
            (int) $user_id
        ));
    }

    private function mark_notifications_read($user_id) {
        global $wpdb;
        $table = $this->get_notifications_table();
        $wpdb->update($table, [
            'is_read' => 1,
        ], [
            'user_id' => (int) $user_id,
        ], ['%d'], ['%d']);
    }

    private function mark_single_notification_read($user_id, $notification_id) {
        global $wpdb;
        $table = $this->get_notifications_table();
        $wpdb->update($table, [
            'is_read' => 1,
        ], [
            'id' => (int) $notification_id,
            'user_id' => (int) $user_id,
        ], ['%d'], ['%d', '%d']);
    }

    private function clear_notifications($user_id) {
        global $wpdb;
        $table = $this->get_notifications_table();
        $wpdb->delete($table, [
            'user_id' => (int) $user_id,
        ], ['%d']);
    }

    private function get_notifications_payload($user_id, $limit = 10) {
        return [
            'unread' => $this->count_unread_notifications($user_id),
            'items' => $this->get_user_notifications($user_id, $limit),
        ];
    }

    private function render_notifications_bell($user_id) {
        $user_id = (int) $user_id;
        if (!$user_id) {
            return '';
        }
        $unread = $this->count_unread_notifications($user_id);
        $items = $this->get_user_notifications($user_id, 10);
        ob_start();
        ?>
        <div class="cmn-bell" data-bell>
            <button class="cmn-bell-btn" type="button" data-bell-toggle>
                <span class="cmn-bell-icon">🔔</span>
                <?php if ($unread) : ?>
                    <span class="cmn-bell-count"><?php echo esc_html($unread); ?></span>
                <?php endif; ?>
            </button>
            <div class="cmn-bell-panel" data-bell-panel>
                <div class="cmn-bell-header">
                    <strong>Notifications</strong>
                    <div class="cmn-bell-actions">
                        <button class="cmn-ghost cmn-btn-mini" type="button" data-bell-mark>Mark all read</button>
                        <button class="cmn-ghost cmn-btn-mini" type="button" data-bell-clear>Clear all</button>
                    </div>
                </div>
                <?php if ($items) : ?>
                    <div class="cmn-bell-list">
                        <?php foreach ($items as $item) : ?>
                            <?php
                            $is_unread = empty($item['is_read']);
                            $raw_link = !empty($item['link_url']) ? (string) $item['link_url'] : '';
                            $link_url = $this->resolve_support_notification_link($item, $user_id, $raw_link);
                            $link_url = $link_url ? esc_url($link_url) : '';
                            $item_class = 'cmn-bell-item' . ($is_unread ? ' is-unread' : '');
                            $item_id = (int) ($item['id'] ?? 0);
                            ?>
                            <?php if ($link_url) : ?>
                                <a class="<?php echo esc_attr($item_class); ?>" href="<?php echo esc_url($link_url); ?>" data-notification-id="<?php echo esc_attr($item_id); ?>" data-notification-link>
                                    <strong><?php echo esc_html($item['title']); ?></strong>
                                    <?php if (!empty($item['message'])) : ?><span><?php echo esc_html($item['message']); ?></span><?php endif; ?>
                                </a>
                            <?php else : ?>
                                <div class="<?php echo esc_attr($item_class); ?>" data-notification-id="<?php echo esc_attr($item_id); ?>">
                                    <strong><?php echo esc_html($item['title']); ?></strong>
                                    <?php if (!empty($item['message'])) : ?><span><?php echo esc_html($item['message']); ?></span><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="cmn-empty">No notifications yet.</div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle_mark_notifications_read() {
        if (!check_ajax_referer('cmn_mark_notifications_read', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        $this->mark_notifications_read($user_id);
        wp_send_json_success($this->get_notifications_payload($user_id));
    }

    public function handle_notifications_mark_all_read() {
        if (!check_ajax_referer('cmn_mark_notifications_read', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        $this->mark_notifications_read($user_id);
        wp_send_json_success($this->get_notifications_payload($user_id));
    }

    public function handle_notifications_clear_all() {
        if (!check_ajax_referer('cmn_mark_notifications_read', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        $this->clear_notifications($user_id);
        wp_send_json_success($this->get_notifications_payload($user_id));
    }

    public function handle_notifications_mark_read() {
        if (!check_ajax_referer('cmn_mark_notifications_read', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $notification_id = intval($_POST['notification_id'] ?? 0);
        if (!$notification_id) {
            wp_send_json_error(['message' => 'Notification not found.'], 404);
        }
        $user_id = get_current_user_id();
        $this->mark_single_notification_read($user_id, $notification_id);
        wp_send_json_success($this->get_notifications_payload($user_id));
    }

    private function get_email_templates_by_type($type) {
        $type = sanitize_text_field($type);
        $templates = [];
        if ($type === 'candidate') {
            $name = 'John Smith';
            $verify_link = 'https://covermenow.co.uk/portal/?view=candidate-verify&uid=1&token=TEST123';
            $reset_link = $this->get_portal_reset_url('john.smith', 'TEST123');
            $templates[] = [
                'id' => 'verification',
                'subject' => '[TEST MODE] Verify your email for CoverMeNow ONE',
                'message' => "Hi {$name},\n\nThanks for registering with CoverMeNow ONE.\n\nPlease verify your email to continue:\n{$verify_link}\n\nOnce verified, you can complete the rest of your profile.\n\nIf you did not request this, please ignore this email.",
            ];
            $templates[] = [
                'id' => 'welcome',
                'subject' => '[TEST MODE] Welcome to CoverMeNow ONE',
                'message' => "Hi {$name},\n\nWelcome to CoverMeNow ONE.\n\nYour profile is ready to complete. Log in anytime to update availability, upload documents, and manage bookings.\n\nThanks,\nCoverMeNow Candidates",
            ];
            $templates[] = [
                'id' => 'admin_notification',
                'subject' => '[TEST MODE] New Candidate Registration Request',
                'message' => "A new candidate registration request was submitted.\n\nCandidate: {$name}\nEmail: test@example.com\nLocation: London\n\nReview in the CRM.",
            ];
            $templates[] = [
                'id' => 'password_reset',
                'subject' => '[TEST MODE] Reset your CoverMeNow ONE password',
                'message' => "Hi {$name},\n\nYou requested a password reset. Use the link below to set a new password:\n{$reset_link}\n\nIf you did not request this, please ignore this email.",
            ];
        }
        if ($type === 'school') {
            $school_name = 'Test Primary School';
            $location = 'London';
            $profile_link = 'https://covermenow.co.uk/school-registration/?token=TEST123';
            $requested_date = '01 January 2026';
            $templates[] = [
                'id' => 'registration_confirmation',
                'subject' => '[TEST MODE] School Application Received',
                'message' => "Hi {$school_name},\n\nThanks for applying to CoverMeNow ONE.\nLocation: {$location}\n\nWe’ve received your application and will be in touch shortly.",
            ];
            $templates[] = [
                'id' => 'profile_completion',
                'subject' => '[TEST MODE] Complete Your CoverMeNow Profile',
                'message' => "Hi {$school_name},\n\nPlease complete your CoverMeNow ONE profile using the secure link below:\n{$profile_link}\n\nThis link expires in 7 days.",
            ];
            $templates[] = [
                'id' => 'booking_received',
                'subject' => '[TEST MODE] Booking request received',
                'message' => "We’ve received your booking request and aim to confirm within 10 minutes.\n\nDate: {$requested_date}",
            ];
            $templates[] = [
                'id' => 'booking_confirmed',
                'subject' => '[TEST MODE] Candidate confirmed',
                'message' => "Your candidate has been confirmed for {$requested_date}.",
            ];
            $templates[] = [
                'id' => 'booking_declined',
                'subject' => '[TEST MODE] Booking update',
                'message' => "We’re unable to confirm this request. Please select another available candidate.",
            ];
        }
        return $templates;
    }

    public function handle_send_test_emails() {
        if (!check_ajax_referer('cmn_send_test_emails', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        $user_id = get_current_user_id();
        if (!$this->is_admin_user($user_id)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $email = sanitize_email($_POST['email'] ?? '');
        $type = sanitize_text_field($_POST['email_type'] ?? '');
        if (!$email || !is_email($email)) {
            wp_send_json_error(['message' => 'Enter a valid email address.'], 400);
        }

        $templates = $this->get_email_templates_by_type($type);
        if (!$templates) {
            wp_send_json_error(['message' => 'Email type not implemented yet.'], 400);
        }

        $results = [];
        $from_email = '';
        $from_name = '';
        if ($type === 'candidate') {
            $from_email = $this->get_candidate_from_email();
            $from_name = $this->get_candidate_from_name();
            foreach ($templates as $template) {
                $sent = $this->send_candidate_email($email, $template['subject'], $template['message'], [
                    'type' => 'test_candidate',
                ]);
                $results[] = [
                    'type' => $template['id'],
                    'status' => $sent ? 'sent' : 'failed',
                ];
            }
        } elseif ($type === 'school') {
            $from_email = $this->get_school_from_email();
            $from_name = $this->get_school_from_name();
            $previous_log_type = $GLOBALS['cmn_school_mail_log_type'] ?? null;
            $GLOBALS['cmn_school_mail_log_type'] = 'test_school';
            foreach ($templates as $template) {
                $sent = $this->send_school_email($email, $template['subject'], $template['message']);
                $results[] = [
                    'type' => $template['id'],
                    'status' => $sent ? 'sent' : 'failed',
                ];
            }
            if ($previous_log_type !== null) {
                $GLOBALS['cmn_school_mail_log_type'] = $previous_log_type;
            } else {
                unset($GLOBALS['cmn_school_mail_log_type']);
            }
        } else {
            wp_send_json_error(['message' => 'Email type not implemented yet.'], 400);
        }

        wp_send_json_success([
            'results' => $results,
            'from_email' => $from_email,
            'from_name' => $from_name,
            'from' => $from_email,
        ]);
    }

    public function register_shortcodes() {
        add_shortcode('cmn_login', [$this, 'render_login_shortcode']);
        add_shortcode('cmn_portal', [$this, 'render_portal_shortcode']);
        add_shortcode('cmn_staff_dashboard', [$this, 'render_staff_dashboard_shortcode']);
        add_shortcode('cmn_staff_schools', [$this, 'render_staff_schools_shortcode']);
        add_shortcode('cmn_staff_candidates', [$this, 'render_staff_candidates_shortcode']);
        add_shortcode('cmn_staff_bookings', [$this, 'render_staff_bookings_shortcode']);
        add_shortcode('cmn_school_dashboard', [$this, 'render_school_dashboard_shortcode']);
        add_shortcode('cmn_candidate_dashboard', [$this, 'render_candidate_dashboard_shortcode']);
        add_shortcode('cmn_register_school', [$this, 'render_register_school_shortcode']);
        add_shortcode('cmn_register_candidate', [$this, 'render_register_candidate_shortcode']);
        add_shortcode('cmn_school_landing', [$this, 'render_school_landing_shortcode']);
        add_shortcode('cmn_candidate_landing', [$this, 'render_candidate_landing_shortcode']);
        add_shortcode('cmn_available_wall', [$this, 'render_available_wall_shortcode']);
    }

    public function render_login_shortcode($portal_only = false) {
        ob_start();
        $candidate_page = get_page_by_title('Candidate Registration');
        $school_page = get_page_by_title('School Registration');
        $candidate_url = $candidate_page ? get_permalink($candidate_page) : home_url('/candidate-registration');
        $school_url = $school_page ? get_permalink($school_page) : home_url('/school-registration');
        $portal_url = $this->get_portal_base_url();
        $redirect_to = isset($_GET['redirect_to']) ? esc_url_raw(wp_unslash($_GET['redirect_to'])) : '';
        $container_class = $portal_only ? 'cmn-login-page cmn-portal-login' : 'cmn-login-page';
        ?>
        <div class="<?php echo esc_attr($container_class); ?>">
            <a class="cmn-back-link" href="<?php echo esc_url(home_url('/')); ?>">Back to main website</a>
            <main class="screen">
                <section class="panel panel-left">
                    <header class="brand">
                        <h1>
                            <span class="brand-main">CoverMeNow</span>
                            <span class="brand-accent">ONE</span>
                        </h1>
                        <p><span class="tag-white">One</span> <span class="brand-accent">system</span>. <span class="tag-white">Total</span> <span class="brand-accent">cover</span>.</p>
                    </header>

                    <div class="cmn-hero-stack">
                        <div class="cmn-hero-block">
                            <div class="cmn-hero-callout">
                                <strong>Built to remove early-morning supply chaos.</strong>
                                <span>Availability, approvals, and control in one place. Reliable cover. Admin approved. No chaos.</span>
                            </div>
                        </div>
                        <div class="cmn-hero-block">
                            <div class="cmn-hero-steps">
                                <h3>How It Works</h3>
                                <div class="cmn-steps-grid">
                                    <div class="cmn-step">
                                        <span class="cmn-step-number">1</span>
                                        <div class="cmn-step-icon" aria-hidden="true">
                                            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/use1.png'); ?>" alt="" />
                                        </div>
                                    <div class="cmn-step-text">
                                        <strong>Availability confirmed</strong>
                                        <span>Candidates confirm availability the evening before.</span>
                                    </div>
                                </div>
                                <div class="cmn-step">
                                    <span class="cmn-step-number">2</span>
                                        <div class="cmn-step-icon" aria-hidden="true">
                                            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/use2.png'); ?>" alt="" />
                                    </div>
                                    <div class="cmn-step-text">
                                        <strong>View local availability</strong>
                                        <span>Log in and see vetted local staff available to you.</span>
                                    </div>
                                </div>
                                <div class="cmn-step">
                                    <span class="cmn-step-number">3</span>
                                        <div class="cmn-step-icon" aria-hidden="true">
                                            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/use3.png'); ?>" alt="" />
                                    </div>
                                    <div class="cmn-step-text">
                                        <strong>Book and confirm</strong>
                                        <span>Book through the system and receive confirmation within 10 minutes.</span>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="cmn-hero-block cmn-hero-cta-block">
                            <div class="cmn-hero-ctas">
                                <a class="cmn-cta-primary cmn-register-link" href="<?php echo esc_url($school_url); ?>">Apply to Register (School)</a>
                                <a class="cmn-cta-primary cmn-register-link" href="<?php echo esc_url($candidate_url); ?>">Apply to Register (Candidate)</a>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="panel panel-right">
                    <div class="login">
                        <div class="login-brand">
                            <h1>
                                <span class="brand-main">CoverMeNow</span>
                                <span class="brand-accent">ONE</span>
                            </h1>
                            <p><span class="tag-white">One</span> <span class="brand-accent">system</span>. <span class="tag-white">Total</span> <span class="brand-accent">cover</span>.</p>
                        </div>
                        <h2>Log In</h2>
                        <?php if (isset($_GET['cmn_verified']) && $_GET['cmn_verified'] === '1') : ?>
                            <div class="cmn-register-success">
                                <h4>Email verified</h4>
                                <p>Your email has been verified. Please log in to complete your profile.</p>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_GET['cmn_error'])) : ?>
                            <div class="cmn-register-error">
                                <?php echo esc_html(sanitize_text_field($_GET['cmn_error'])); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_GET['cmn_notice'])) : ?>
                            <div class="cmn-register-success">
                                <?php echo esc_html(sanitize_text_field($_GET['cmn_notice'])); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!is_user_logged_in()) : ?>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('cmn_portal_login', 'cmn_portal_login_nonce'); ?>
                            <input type="hidden" name="action" value="cmn_portal_login">
                            <?php if ($redirect_to) : ?>
                                <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
                            <?php endif; ?>
                            <label>
                                Email
                                <input type="text" name="log" placeholder="Email">
                            </label>
                            <label>
                                Password
                                <div class="password-field">
                                    <input type="password" name="pwd" placeholder="Password">
                                </div>
                            </label>
                            <label class="remember">
                                <input type="checkbox" name="rememberme" value="forever">
                                Remember me
                            </label>
                            <button class="primary" type="submit">Log In</button>
                            <a class="link" href="<?php echo esc_url($this->get_portal_forgot_url()); ?>">Forgot password?</a>
                        </form>
                        <?php else : ?>
                            <p>You are already logged in.</p>
                            <a class="primary cmn-button-link" href="<?php echo esc_url($portal_url); ?>">Go to Portal</a>
                        <?php endif; ?>
                        <p class="policy">Protected by CoverMeNow safeguarding policy</p>
                    </div>
                </section>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_portal_forgot_password_shortcode() {
        ob_start();
        ?>
        <section class="cmn-portal cmn-portal-auth">
            <div class="cmn-panel-card cmn-auth-card">
                <div class="cmn-auth-heading">
                    <span class="cmn-auth-brand">CoverMeNow <span>ONE</span></span>
                    <h2>Reset your password</h2>
                </div>
                <p class="cmn-muted">Enter your email or username and we’ll send a reset link.</p>
                <?php if (isset($_GET['cmn_notice'])) : ?>
                    <div class="cmn-register-success"><?php echo esc_html(sanitize_text_field($_GET['cmn_notice'])); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['cmn_error'])) : ?>
                    <div class="cmn-register-error"><?php echo esc_html(sanitize_text_field($_GET['cmn_error'])); ?></div>
                <?php endif; ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-form">
                    <?php wp_nonce_field('cmn_portal_forgot_password', 'cmn_portal_forgot_password_nonce'); ?>
                    <input type="hidden" name="action" value="cmn_portal_forgot_password">
                    <label>Email or Username
                        <input type="text" name="user_login" required>
                    </label>
                    <button class="cmn-primary" type="submit">Send reset link</button>
                    <a class="cmn-ghost cmn-button-link" href="<?php echo esc_url($this->get_portal_login_url()); ?>">Back to login</a>
                </form>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    public function render_portal_reset_password_shortcode() {
        $login = sanitize_text_field($_GET['login'] ?? '');
        $key = sanitize_text_field($_GET['key'] ?? '');
        ob_start();
        ?>
        <section class="cmn-portal cmn-portal-auth">
            <div class="cmn-panel-card cmn-auth-card">
                <div class="cmn-auth-heading">
                    <span class="cmn-auth-brand">CoverMeNow <span>ONE</span></span>
                    <h2>Set your password</h2>
                </div>
                <?php
                $is_valid = false;
                $validation_error = '';
                if ($login && $key) {
                    $checked_user = check_password_reset_key($key, $login);
                    if (is_wp_error($checked_user)) {
                        $validation_error = $checked_user->get_error_message();
                    } else {
                        $is_valid = true;
                    }
                } else {
                    $validation_error = 'This link is invalid or has expired.';
                }
                if (isset($_GET['cmn_error'])) {
                    $validation_error = sanitize_text_field($_GET['cmn_error']);
                }
                ?>
                <?php if (!$is_valid) : ?>
                    <div class="cmn-register-error"><?php echo esc_html($validation_error ?: 'This link is invalid or has expired.'); ?></div>
                    <a class="cmn-primary cmn-button-link" href="<?php echo esc_url($this->get_portal_forgot_url()); ?>">Request a new link</a>
                <?php else : ?>
                    <?php if (isset($_GET['cmn_error'])) : ?>
                        <div class="cmn-register-error"><?php echo esc_html(sanitize_text_field($_GET['cmn_error'])); ?></div>
                    <?php endif; ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-form">
                        <?php wp_nonce_field('cmn_portal_reset_password', 'cmn_portal_reset_password_nonce'); ?>
                        <input type="hidden" name="action" value="cmn_portal_reset_password">
                        <input type="hidden" name="login" value="<?php echo esc_attr($login); ?>">
                        <input type="hidden" name="key" value="<?php echo esc_attr($key); ?>">
                        <label>New password
                            <input type="password" name="pass1" required>
                        </label>
                        <label>Confirm password
                            <input type="password" name="pass2" required>
                        </label>
                        <p class="cmn-muted-small">Password must be at least 10 characters.</p>
                        <button class="cmn-primary" type="submit">Save password</button>
                        <a class="cmn-ghost cmn-button-link" href="<?php echo esc_url($this->get_portal_base_url()); ?>">Back to portal</a>
                    </form>
                <?php endif; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    public function render_candidate_verify_shortcode() {
        $user_id = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
        $token = isset($_GET['token']) ? sanitize_text_field(wp_unslash($_GET['token'])) : '';
        $login = sanitize_text_field($_GET['login'] ?? '');
        $key = sanitize_text_field($_GET['key'] ?? '');
        $verification = ['success' => false, 'message' => 'This verification link is invalid or has expired.'];
        if ($user_id && $token !== '') {
            $verification = $this->verify_candidate_email_token($user_id, $token);
        }
        $set_password_url = '';
        if ($user_id) {
            $user = get_user_by('id', $user_id);
            if ($user instanceof WP_User) {
                $key_for_user = get_password_reset_key($user);
                if (!is_wp_error($key_for_user)) {
                    $set_password_url = $this->get_portal_reset_url($user->user_login, $key_for_user);
                }
            }
        }
        if ($login && $key) {
            $set_password_url = add_query_arg([
                'view' => 'candidate-verify',
                'uid' => $user_id,
                'token' => rawurlencode($token),
                'login' => rawurlencode($login),
                'key' => rawurlencode($key),
            ], $this->get_portal_base_url());
        }
        ob_start();
        ?>
        <section class="cmn-portal cmn-portal-auth">
            <div class="cmn-panel-card cmn-auth-card">
                <div class="cmn-auth-heading">
                    <span class="cmn-auth-brand">CoverMeNow <span>ONE</span></span>
                    <h2>Candidate Verification</h2>
                </div>
                <div class="<?php echo $verification['success'] ? 'cmn-register-success' : 'cmn-register-error'; ?>">
                    <?php echo esc_html($verification['message']); ?>
                </div>
                <?php if (!$verification['success']) : ?>
                    <a class="cmn-primary cmn-button-link" href="<?php echo esc_url($this->get_portal_forgot_url()); ?>">Resend verification email</a>
                <?php endif; ?>
                <?php if ($verification['success'] && $set_password_url && !$login && !$key) : ?>
                    <a class="cmn-primary cmn-button-link" href="<?php echo esc_url($set_password_url); ?>">Set password</a>
                <?php endif; ?>
                <?php if ($login && $key) : ?>
                    <?php
                    $checked_user = check_password_reset_key($key, $login);
                    $is_valid = !is_wp_error($checked_user);
                    ?>
                    <?php if (!$is_valid) : ?>
                        <div class="cmn-register-error">This password link is invalid or has expired.</div>
                        <a class="cmn-ghost cmn-button-link" href="<?php echo esc_url($this->get_portal_forgot_url()); ?>">Request a new link</a>
                    <?php else : ?>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-form">
                            <?php wp_nonce_field('cmn_portal_reset_password', 'cmn_portal_reset_password_nonce'); ?>
                            <input type="hidden" name="action" value="cmn_portal_reset_password">
                            <input type="hidden" name="login" value="<?php echo esc_attr($login); ?>">
                            <input type="hidden" name="key" value="<?php echo esc_attr($key); ?>">
                            <label>New password
                                <input type="password" name="pass1" required>
                            </label>
                            <label>Confirm password
                                <input type="password" name="pass2" required>
                            </label>
                            <p class="cmn-muted-small">Password must be at least 10 characters.</p>
                            <button class="cmn-primary" type="submit">Save password</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                <a class="cmn-ghost cmn-button-link" href="<?php echo esc_url($this->get_portal_base_url()); ?>">Back to portal</a>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    public function render_portal_shortcode() {
        $preview = isset($_GET['as']) ? sanitize_text_field($_GET['as']) : '';
        if ($preview && $this->can_preview_dashboards()) {
            if ($preview === 'candidate') {
                return $this->render_candidate_dashboard_shortcode();
            }
            if ($preview === 'school') {
                return $this->render_school_dashboard_shortcode();
            }
        }

        $view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : '';
        if ($view === 'candidate-verify') {
            return $this->render_candidate_verify_shortcode();
        }
        if ($view === 'candidate-settings') {
            if (!is_user_logged_in()) {
                return $this->render_login_shortcode(true);
            }
            if (!$this->is_candidate_user()) {
                return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>This section is available to candidates only.</p></div></section>';
            }
            $_GET['candidate'] = 'settings';
            return $this->render_candidate_dashboard_shortcode();
        }
        if (!is_user_logged_in()) {
            $ticket_id = isset($_GET['ticket_id']) ? (int) $_GET['ticket_id'] : 0;
            if ($ticket_id > 0 && (($view === 'support') || (isset($_GET['candidate']) && $_GET['candidate'] === 'support') || (isset($_GET['school']) && $_GET['school'] === 'support'))) {
                $_GET['view'] = 'login';
                $_GET['redirect_to'] = esc_url_raw($this->get_current_url());
                return $this->render_login_shortcode(true);
            }
            if ($view === 'login') {
                return $this->render_login_shortcode(true);
            }
            if ($view === 'forgot-password') {
                return $this->render_portal_forgot_password_shortcode();
            }
            if ($view === 'candidate-verify') {
                return $this->render_candidate_verify_shortcode();
            }
            if ($view === 'reset-password' || $view === 'set-password') {
                return $this->render_portal_reset_password_shortcode();
            }
            return $this->render_login_shortcode(true);
        }
        if ($view === 'clients') {
            $_GET['cmn_status'] = 'client';
            return $this->render_staff_schools_shortcode();
        }
        if ($view === 'leads') {
            $_GET['cmn_status'] = 'lead';
            return $this->render_staff_schools_shortcode();
        }
        if ($view === 'schools') {
            return $this->render_staff_schools_shortcode();
        }
        if ($view === 'candidates') {
            return $this->render_staff_candidates_shortcode();
        }
        if ($view === 'cv-converter' || $view === 'cv_converter') {
            return $this->render_staff_cv_converter_shortcode();
        }
        if ($view === 'requests') {
            return $this->render_staff_requests_shortcode();
        }
        if ($view === 'war-room' || $view === 'war_room') {
            return $this->render_staff_war_room_shortcode();
        }
        if ($view === 'bookings') {
            return $this->render_staff_bookings_shortcode();
        }
        if ($view === 'analytics') {
            return $this->render_staff_analytics_shortcode();
        }
        if ($view === 'contacts') {
            return $this->render_staff_contacts_shortcode();
        }
        if ($view === 'settings') {
            return $this->render_staff_settings_shortcode();
        }
        if ($view === 'invoicing') {
            return $this->render_staff_invoicing_shortcode();
        }
        if ($view === 'support') {
            if ($this->is_candidate_user()) {
                $_GET['candidate'] = 'support';
                return $this->render_candidate_dashboard_shortcode();
            }
            if ($this->is_school_user()) {
                $_GET['school'] = 'support';
                return $this->render_school_dashboard_shortcode();
            }
            return $this->render_staff_support_shortcode();
        }
        if ($view === 'staff') {
            return $this->render_staff_staff_shortcode();
        }

        $user = wp_get_current_user();
        if (in_array('cmn_admin', (array) $user->roles, true)
            || in_array('cmn_staff', (array) $user->roles, true)
            || in_array('cmn_account_manager', (array) $user->roles, true)
            || in_array('administrator', (array) $user->roles, true)) {
            return $this->render_staff_dashboard_shortcode();
        }
        if (in_array('cmn_school_manager', (array) $user->roles, true) || in_array('cmn_school_staff', (array) $user->roles, true)) {
            return $this->render_school_dashboard_shortcode();
        }
        if (in_array('cmn_candidate', (array) $user->roles, true)) {
            return $this->render_candidate_dashboard_shortcode();
        }
        if (in_array('cmn_candidate_pending', (array) $user->roles, true)) {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Email verification required</h3><p>Please verify your email address to access your profile. Check your inbox for the verification link.</p></div></section>';
        }

        return $this->render_staff_dashboard_shortcode();
    }

    public function render_staff_dashboard_shortcode() {
        $tasks = $this->get_activity_items(['task', 'call'], 8);
        $pending_review_candidates = $this->get_candidates_awaiting_document_review(10);
        $alert_missing_dbs = $this->count_missing_dbs();
        $alert_missing_id = $this->count_missing_id();
        $alert_availability = 0;
        $feedback_overview = $this->get_booking_feedback_analytics('all', 30);
        $alert_low_feedback = (int) ($feedback_overview['totals']['low_count'] ?? 0);
        $portal_url = $this->get_portal_base_url();

        ob_start();
        ?>
        <header class="cmn-school-header cmn-dashboard-header">
            <div class="cmn-header-row">
                <div>
                    <h2>Dashboard</h2>
                </div>
            </div>
        </header>
        <div class="cmn-admin-grid">
            <div class="cmn-admin-column">
                <div class="cmn-dashboard-card cmn-card-tone-4">
                    <div class="cmn-panel-header">
                        <h3>Tasks, Reminders & Callbacks</h3>
                        <button class="cmn-btn-secondary" type="button">Add Task</button>
                    </div>
                    <div class="cmn-task-list">
                        <?php if ($tasks) : ?>
                            <?php foreach ($tasks as $task) : ?>
                                <?php
                                $task_type = $task['activity_type'] ?? '';
                                $task_detail = $task['notes'] ?? '';
                                $task_date = $task['due_date'] ?? '';
                                $task_label = $task_date ? date_i18n('M j, Y', strtotime($task_date)) : 'No date';
                                $is_overdue = $task_date && strtotime($task_date) < strtotime(date('Y-m-d'));
                                ?>
                                <div class="cmn-task-item<?php echo $is_overdue ? ' is-overdue' : ''; ?>">
                                    <div>
                                        <strong><?php echo esc_html($task['subject'] ?? 'Task'); ?></strong>
                                        <span><?php echo esc_html($task_detail ? wp_trim_words($task_detail, 8, '…') : ucfirst($task_type)); ?></span>
                                    </div>
                                    <div class="cmn-task-meta"><?php echo esc_html($task_label); ?><?php echo $is_overdue ? ' · Overdue' : ''; ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="cmn-empty">No tasks added yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="cmn-dashboard-card cmn-card-tone-4 cmn-awaiting-review-card">
                    <div class="cmn-panel-header">
                        <h3>Candidates Awaiting Document Review</h3>
                        <span class="cmn-status-chip is-pending"><?php echo esc_html(count($pending_review_candidates)); ?></span>
                    </div>
                    <?php if ($pending_review_candidates) : ?>
                        <div class="cmn-awaiting-review-list">
                            <?php foreach ($pending_review_candidates as $review_row) : ?>
                                <div class="cmn-awaiting-review-item">
                                    <div>
                                        <strong><?php echo esc_html((string) ($review_row['candidate_name'] ?? 'Candidate')); ?></strong>
                                        <span><?php echo esc_html((string) ($review_row['candidate_email'] ?? '')); ?></span>
                                        <span>Pending: <?php echo esc_html(implode(', ', (array) ($review_row['pending_docs'] ?? []))); ?></span>
                                        <span>Uploaded: <?php echo esc_html((string) ($review_row['uploaded_at_label'] ?? '—')); ?></span>
                                    </div>
                                    <a class="cmn-ghost cmn-btn-mini" href="<?php echo esc_url(add_query_arg(['view' => 'candidates', 'candidate_id' => (int) ($review_row['candidate_id'] ?? 0)], $portal_url) . '#candidate-documents'); ?>">Review</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="cmn-empty">No candidates are waiting for document review.</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="cmn-admin-column">
                <div class="cmn-dashboard-card cmn-card-tone-4">
                    <div class="cmn-panel-header">
                        <h3>Alerts</h3>
                        <span class="cmn-status-chip is-pending"><?php echo esc_html($alert_missing_dbs + $alert_missing_id + $alert_availability + $alert_low_feedback); ?></span>
                    </div>
                    <div class="cmn-request-row cmn-alert-row">
                        <span class="cmn-priority-bar"></span>
                        <div class="cmn-request-content">
                            <strong>Missing DBS</strong>
                            <span><?php echo esc_html($alert_missing_dbs); ?> candidates</span>
                        </div>
                    </div>
                    <div class="cmn-request-row cmn-alert-row">
                        <span class="cmn-priority-bar"></span>
                        <div class="cmn-request-content">
                            <strong>Missing ID</strong>
                            <span><?php echo esc_html($alert_missing_id); ?> candidates</span>
                        </div>
                    </div>
                    <div class="cmn-request-row cmn-alert-row">
                        <span class="cmn-priority-bar"></span>
                        <div class="cmn-request-content">
                            <strong>Cancelled Availability</strong>
                            <span><?php echo esc_html($alert_availability); ?> candidate</span>
                        </div>
                    </div>
                    <div class="cmn-request-row cmn-alert-row">
                        <span class="cmn-priority-bar"></span>
                        <div class="cmn-request-content">
                            <strong>Low Feedback Alerts (≤2)</strong>
                            <span><?php echo esc_html($alert_low_feedback); ?> flagged</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $inner = ob_get_clean();
        return $this->render_staff_shell('dashboard', $inner);
    }

    public function render_staff_schools_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        if (!$this->is_staff_user()) {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>This section is available to admin staff only.</p></div></section>';
        }
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        $school_identifier = isset($_GET['school_id']) ? sanitize_text_field($_GET['school_id']) : '';
        if ($school_identifier !== '') {
            $school_post_id = $this->resolve_school_identifier($school_identifier);
            if ($school_post_id && !$this->user_can_access_school($school_post_id)) {
                return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>You do not have access to this school.</p></div></section>';
            }
            if ($school_post_id) {
                return $this->render_staff_shell('schools', $this->render_frontend_school_profile($school_post_id));
            }
        }
        $view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'schools';
        $status = isset($_GET['cmn_status']) ? sanitize_text_field($_GET['cmn_status']) : '';
        $search = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
        if ($search === '' && isset($_GET['s'])) {
            $search = sanitize_text_field($_GET['s']);
        }
        $bucket = isset($_GET['cmn_bucket']) ? sanitize_text_field($_GET['cmn_bucket']) : '';
        $stage = isset($_GET['cmn_stage']) ? sanitize_text_field($_GET['cmn_stage']) : '';
        $manager_id = isset($_GET['cmn_manager']) ? intval($_GET['cmn_manager']) : 0;
        $location_filter = isset($_GET['cmn_location']) ? sanitize_text_field($_GET['cmn_location']) : '';
        if ($status === '') {
            if ($view === 'clients') {
                $status = 'client';
            } elseif ($view === 'leads') {
                $status = 'lead';
            }
        }
        if ($status === '') {
            if ($bucket === 'clients') {
                $status = 'client';
            } elseif ($bucket === 'leads') {
                $status = 'lead';
            }
        }
        if ($status === '') {
            $status = 'lead';
        }
        if ($status === 'all') {
            $status = 'all';
        }
        $import_message = isset($_GET['cmn_imported']) ? sanitize_text_field($_GET['cmn_imported']) : '';
        $import_note = isset($_GET['cmn_import_msg']) ? sanitize_text_field(wp_unslash($_GET['cmn_import_msg'])) : '';
        $import_token = isset($_GET['cmn_import_token']) ? sanitize_text_field($_GET['cmn_import_token']) : '';
        $import_data = $import_token ? get_transient('cmn_import_' . $import_token) : null;
        if ($import_data && (int) ($import_data['user_id'] ?? 0) !== get_current_user_id()) {
            $import_data = null;
            $import_token = '';
        }
        $import_headers = $import_data['headers'] ?? [];
        $bulk_active = $import_data ? ' is-active' : '';
        $add_active = '';

        $args = [
            'post_type' => 'cmn_school',
            'posts_per_page' => 200,
        ];
        $current_user_id = get_current_user_id();
        $assigned_school_ids = [];
        if ($this->is_account_manager_user($current_user_id) && !$this->is_admin_user($current_user_id) && !$this->is_staff_role($current_user_id)) {
            $assigned_school_ids = $this->get_assigned_school_ids_for_account_manager($current_user_id);
            if (!$assigned_school_ids) {
                $assigned_school_ids = [0];
            }
            $args['post__in'] = $assigned_school_ids;
        }
        $meta_query = [];
        if ($status !== '' && $status !== 'all') {
            $meta_query[] = [
                'key' => 'cmn_status',
                'value' => $status,
            ];
        }
        if ($stage !== '') {
            $meta_query[] = [
                'key' => 'cmn_pipeline_stage',
                'value' => $stage,
            ];
        }
        if ($location_filter !== '') {
            $meta_query[] = [
                'key' => 'cmn_location',
                'value' => $location_filter,
                'compare' => 'LIKE',
            ];
        }
        if ($manager_id) {
            $manager = get_user_by('id', $manager_id);
            $manager_query = [
                'relation' => 'OR',
                [
                    'key' => 'cmn_account_manager_user',
                    'value' => (string) $manager_id,
                ],
            ];
            if ($manager) {
                $manager_query[] = [
                    'key' => 'cmn_account_manager',
                    'value' => $manager->display_name,
                    'compare' => 'LIKE',
                ];
                $manager_query[] = [
                    'key' => 'cmn_account_manager_name',
                    'value' => $manager->display_name,
                    'compare' => 'LIKE',
                ];
                $manager_query[] = [
                    'key' => 'cmn_account_manager_email',
                    'value' => $manager->user_email,
                    'compare' => 'LIKE',
                ];
            }
            $meta_query[] = $manager_query;
        }
        if ($meta_query) {
            $args['meta_query'] = $meta_query;
        }
        if ($search !== '') {
            global $wpdb;
            $like = '%' . $wpdb->esc_like($search) . '%';
            $meta_keys = [
                'cmn_school_id',
                'cmn_location',
                'cmn_email',
                'cmn_school_email_domain',
                'cmn_phone',
                'cmn_status',
                'cmn_pipeline_stage',
                'cmn_account_manager',
                'cmn_account_manager_name',
                'cmn_account_manager_email',
                'cmn_cover_manager',
                'cmn_cover_manager_email',
                'cmn_email_name',
                'cmn_contact_name',
                'cmn_contact_email',
                'cmn_contact_phone',
                'cmn_contact_role',
            ];
            $placeholders = implode(',', array_fill(0, count($meta_keys), '%s'));
            $sql = "SELECT DISTINCT p.ID
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
                WHERE p.post_type = 'cmn_school'
                  AND p.post_status = 'publish'
                  AND (p.post_title LIKE %s OR (pm.meta_key IN ($placeholders) AND pm.meta_value LIKE %s))";
            $params = array_merge([$like], $meta_keys, [$like]);
            $matched_ids = $wpdb->get_col($wpdb->prepare($sql, $params));
            if (!$matched_ids) {
                $matched_ids = [0];
            }
            $matched_ids = array_map('intval', $matched_ids);
            if (!empty($args['post__in'])) {
                $matched_ids = array_values(array_intersect($args['post__in'], $matched_ids));
                if (!$matched_ids) {
                    $matched_ids = [0];
                }
            }
            $args['post__in'] = $matched_ids;
        }
        $query = new WP_Query($args);
        $view_param = 'schools';
        $redirect_url = add_query_arg(array_filter([
            'view' => $view_param,
            'cmn_status' => $status ?: null,
            'cmn_stage' => $stage ?: null,
            'cmn_manager' => $manager_id ?: null,
            'cmn_location' => $location_filter ?: null,
            'q' => $search ?: null,
        ]), $portal_url);
        $manager_users = $this->get_account_manager_users();
        $active_nav = 'schools';
        $filter_count = 0;
        if ($stage !== '') {
            $filter_count++;
        }
        if ($manager_id) {
            $filter_count++;
        }
        if ($location_filter !== '') {
            $filter_count++;
        }
        $filter_label = $filter_count ? 'Filters (' . $filter_count . ')' : 'Filters';
        $base_url = add_query_arg(['view' => $view_param], $portal_url);
        $segment_base = array_filter([
            'view' => 'schools',
            'cmn_stage' => $stage ?: null,
            'cmn_manager' => $manager_id ?: null,
            'cmn_location' => $location_filter ?: null,
            'q' => $search ?: null,
        ]);
        $segment_leads_url = add_query_arg(array_merge($segment_base, ['cmn_status' => 'lead']), $portal_url);
        $segment_clients_url = add_query_arg(array_merge($segment_base, ['cmn_status' => 'client']), $portal_url);
        $segment_all_url = add_query_arg(array_merge($segment_base, ['cmn_status' => 'all']), $portal_url);

        ob_start();
        ?>
        <header class="cmn-school-header">
            <div class="cmn-header-row">
                <div>
                    <h2>Schools CRM</h2>
                </div>
                <div class="cmn-header-actions">
                    <div class="cmn-action-menu" data-action-menu>
                        <button class="cmn-btn-secondary cmn-btn-mini" type="button">Add School</button>
                        <div class="cmn-action-dropdown">
                            <button class="cmn-btn-ghost" type="button" data-action-target="add">Add New School</button>
                            <button class="cmn-btn-ghost" type="button" data-action-target="bulk">Bulk Upload</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cmn-segmented" role="tablist" aria-label="Schools view">
                <a class="cmn-segment<?php echo $status === 'lead' ? ' is-active' : ''; ?>" href="<?php echo esc_url($segment_leads_url); ?>">Leads</a>
                <a class="cmn-segment<?php echo $status === 'client' ? ' is-active' : ''; ?>" href="<?php echo esc_url($segment_clients_url); ?>">Clients</a>
                <a class="cmn-segment<?php echo ($status !== 'lead' && $status !== 'client') ? ' is-active' : ''; ?>" href="<?php echo esc_url($segment_all_url); ?>">All</a>
            </div>
        </header>
        <?php if ($import_message) : ?>
            <div class="cmn-panel-card">
                <strong><?php echo esc_html($import_note ?: 'Import complete.'); ?></strong>
            </div>
        <?php endif; ?>
        <div class="cmn-action-panels">
            <div class="cmn-panel-card cmn-action-panel<?php echo $add_active; ?>" data-panel="add">
                <h3>Add New School</h3>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-form">
                    <?php wp_nonce_field('cmn_add_school', 'cmn_add_school_nonce'); ?>
                    <input type="hidden" name="action" value="cmn_add_school">
                    <input type="hidden" name="cmn_status" value="lead">
                    <div class="cmn-form-grid">
                        <label>School Name *
                            <input type="text" name="cmn_school_name" required>
                        </label>
                        <label>Location *
                            <input type="text" name="cmn_location" required>
                        </label>
                        <label>Contact Number *
                            <input type="text" name="cmn_phone" required>
                        </label>
                        <label>School Email *
                            <input type="email" name="cmn_email" required>
                        </label>
                        <label>Account Manager
                            <input type="text" name="cmn_account_manager">
                        </label>
                        <label>Cover Manager Name *
                            <input type="text" name="cmn_cover_manager" required>
                        </label>
                        <label>Cover Manager Email *
                            <input type="email" name="cmn_cover_manager_email" required>
                        </label>
                        <label>Email Name *
                            <input type="text" name="cmn_email_name" required>
                        </label>
                    <label>Spoke to CM
                        <select name="cmn_spoke_to_cm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </label>
                    <label>Switchboard
                        <input type="text" name="cmn_switchboard">
                    </label>
                    <label>Website
                        <input type="text" name="cmn_website">
                    </label>
                    </div>
                    <div class="cmn-form-group">
                        <span class="cmn-form-label">Primary Contact (Optional)</span>
                        <div class="cmn-form-grid">
                            <label>Contact Name
                                <input type="text" name="cmn_contact_name">
                            </label>
                            <label>Contact Email
                                <input type="email" name="cmn_contact_email">
                            </label>
                            <label>Contact Phone
                                <input type="text" name="cmn_contact_phone">
                            </label>
                            <label>Contact Role / Title
                                <input type="text" name="cmn_contact_role">
                            </label>
                        </div>
                    </div>
                    <button class="cmn-ghost" type="submit">Add School</button>
                </form>
            </div>
            <div class="cmn-panel-card cmn-action-panel<?php echo $bulk_active; ?>" data-panel="bulk">
                <h3>Bulk Upload</h3>
                <?php if ($import_data) : ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-form">
                        <?php wp_nonce_field('cmn_import_schools', 'cmn_import_schools_nonce'); ?>
                        <input type="hidden" name="action" value="cmn_import_schools">
                        <input type="hidden" name="cmn_import_step" value="map">
                        <input type="hidden" name="cmn_import_token" value="<?php echo esc_attr($import_token); ?>">
                        <p class="cmn-muted">Map your CSV columns to each required field. All imports are saved as leads.</p>
                        <div class="cmn-form-grid">
                            <?php
                            $required_fields = [
                                'cmn_school_name' => 'School Name *',
                                'cmn_location' => 'Location *',
                                'cmn_phone' => 'Contact Number *',
                                'cmn_email' => 'School Email *',
                                'cmn_cover_manager' => 'Cover Manager Name *',
                                'cmn_cover_manager_email' => 'Cover Manager Email *',
                                'cmn_email_name' => 'Email Name *',
                            ];
                            $optional_fields = [
                                'cmn_account_manager' => 'Account Manager',
                                'cmn_contact_name' => 'Contact Name',
                                'cmn_contact_email' => 'Contact Email',
                                'cmn_contact_phone' => 'Contact Phone',
                                'cmn_contact_role' => 'Contact Role / Title',
                                'cmn_spoke_to_cm' => 'Spoke to CM',
                                'cmn_switchboard' => 'Switchboard',
                                'cmn_website' => 'Website',
                                'cmn_school_id' => 'School ID',
                                'cmn_status' => 'Status',
                                'cmn_pipeline_stage' => 'Pipeline Stage',
                            ];
                            $options = [];
                            foreach ($import_headers as $idx => $label) {
                                $name = trim((string) $label);
                                if ($name === '') {
                                    $name = 'Column ' . ($idx + 1);
                                }
                                $options[] = ['value' => (string) $idx, 'label' => $name];
                            }
                            $render_select = function ($name, $label, $required = false) use ($options) {
                                echo '<label>' . esc_html($label);
                                echo '<select name="cmn_map[' . esc_attr($name) . ']"' . ($required ? ' required' : '') . '>';
                                if (!$required) {
                                    echo '<option value="">Skip</option>';
                                }
                                foreach ($options as $opt) {
                                    echo '<option value="' . esc_attr($opt['value']) . '">' . esc_html($opt['label']) . '</option>';
                                }
                                echo '</select>';
                                echo '</label>';
                            };
                            foreach ($required_fields as $key => $label) {
                                $render_select($key, $label, true);
                            }
                            foreach ($optional_fields as $key => $label) {
                                $render_select($key, $label, false);
                            }
                            ?>
                        </div>
                        <div class="cmn-form-actions">
                            <button class="cmn-ghost" type="submit">Run Import</button>
                            <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['view' => 'schools'], $portal_url)); ?>">Cancel</a>
                        </div>
                    </form>
                <?php else : ?>
                    <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-form">
                        <?php wp_nonce_field('cmn_import_schools', 'cmn_import_schools_nonce'); ?>
                        <input type="hidden" name="action" value="cmn_import_schools">
                        <input type="hidden" name="cmn_import_step" value="upload">
                        <label>Spreadsheet File
                            <input type="file" name="cmn_csv" accept=".csv,.xlsx" required>
                        </label>
                        <p class="cmn-muted">Upload a CSV or .xlsx file. We’ll ask you to map columns next. Status defaults to lead.</p>
                        <button class="cmn-ghost" type="submit">Upload File</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <form method="get" class="cmn-school-toolbar" data-school-toolbar>
            <input type="hidden" name="view" value="<?php echo esc_attr($view_param); ?>">
            <input type="hidden" name="cmn_status" value="<?php echo esc_attr($status); ?>">
            <div class="cmn-toolbar-row">
                <div class="cmn-toolbar-search">
                    <input type="search" name="q" placeholder="Search schools..." value="<?php echo esc_attr($search); ?>" aria-label="Search schools">
                    <button class="cmn-btn-secondary cmn-btn-mini" type="submit">Search</button>
                </div>
                <div class="cmn-toolbar-controls">
                    <button class="cmn-btn-secondary cmn-btn-mini" type="button" data-filter-toggle><?php echo esc_html($filter_label); ?></button>
                    <a class="cmn-btn-ghost cmn-btn-mini" href="<?php echo esc_url($base_url); ?>">Clear</a>
                </div>
            </div>
            <div class="cmn-filter-panel<?php echo $filter_count ? ' is-open' : ''; ?>" data-filter-panel>
                <div class="cmn-filter-grid">
                    <label>Pipeline Stage
                        <select name="cmn_stage">
                            <option value="">All Pipeline Stages</option>
                            <?php foreach (['new_lead', 'contacted', 'demo', 'negotiation', 'won', 'lost'] as $opt) : ?>
                                <option value="<?php echo esc_attr($opt); ?>"<?php echo $stage === $opt ? ' selected' : ''; ?>><?php echo esc_html(ucwords(str_replace('_', ' ', $opt))); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Account Manager
                        <select name="cmn_manager">
                            <option value="">All Account Managers</option>
                            <?php foreach ($manager_users as $manager) : ?>
                                <option value="<?php echo esc_attr($manager->ID); ?>"<?php echo (int) $manager_id === (int) $manager->ID ? ' selected' : ''; ?>>
                                    <?php echo esc_html($manager->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Location
                        <input type="text" name="cmn_location" value="<?php echo esc_attr($location_filter); ?>" placeholder="e.g. London">
                    </label>
                </div>
                <div class="cmn-filter-actions">
                    <button class="cmn-ghost" type="submit">Apply Filters</button>
                </div>
            </div>
        </form>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-bulk-form">
            <?php wp_nonce_field('cmn_bulk_schools', 'cmn_bulk_schools_nonce'); ?>
            <input type="hidden" name="action" value="cmn_bulk_schools">
            <input type="hidden" name="cmn_redirect" value="<?php echo esc_url($redirect_url); ?>">
            <div class="cmn-bulk-actions">
                <select name="cmn_bulk_action">
                    <option value="">Bulk actions</option>
                    <option value="delete">Delete selected</option>
                </select>
                <button class="cmn-ghost" type="submit">Apply</button>
            </div>
            <table class="cmn-approval-table cmn-schools-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="cmn-select-all-schools" aria-label="Select all schools"></th>
                        <th>School</th>
                        <th>Location</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Pipeline</th>
                        <th>Profile</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($query->have_posts()) : ?>
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <tr>
                            <td><input type="checkbox" class="cmn-school-select" name="cmn_school_ids[]" value="<?php echo esc_attr(get_the_ID()); ?>"></td>
                            <td>
                                <?php the_title(); ?>
                                <?php $school_code = get_post_meta(get_the_ID(), 'cmn_school_id', true); ?>
                                <?php if ($school_code) : ?>
                                    <div class="cmn-table-meta">ID: <?php echo esc_html($school_code); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html(get_post_meta(get_the_ID(), 'cmn_location', true)); ?></td>
                            <td><?php echo esc_html(get_post_meta(get_the_ID(), 'cmn_email', true)); ?></td>
                            <?php
                            $status_value = get_post_meta(get_the_ID(), 'cmn_status', true);
                            $pipeline_value = get_post_meta(get_the_ID(), 'cmn_pipeline_stage', true);
                            $status_label = $status_value ? ucfirst($status_value) : '—';
                            $pipeline_label = $pipeline_value ? ucwords(str_replace('_', ' ', $pipeline_value)) : '—';
                            ?>
                            <td><span class="cmn-pill cmn-pill--status"><?php echo esc_html($status_label); ?></span></td>
                            <td><span class="cmn-pill cmn-pill--pipeline"><?php echo esc_html($pipeline_label); ?></span></td>
                            <td><a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['view' => 'schools', 'school_id' => $school_code], home_url('/portal'))); ?>">View</a></td>
                        </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <tr><td colspan="7">No schools found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </form>
        <?php
        $inner = ob_get_clean();
        return $this->render_staff_shell($active_nav, $inner);
    }

    public function render_staff_candidates_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        $candidate_id = isset($_GET['candidate_id']) ? intval($_GET['candidate_id']) : 0;
        if ($candidate_id) {
            if (!$this->user_can_view_candidate($candidate_id)) {
                return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>You do not have access to this candidate.</p></div></section>';
            }
            return $this->render_staff_shell('candidates', $this->render_staff_candidate_profile($candidate_id));
        }
        $status = isset($_GET['cmn_status']) ? sanitize_text_field($_GET['cmn_status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $args = [
            'post_type' => 'cmn_candidate',
            'posts_per_page' => 50,
            's' => $search,
        ];
        $user_id = get_current_user_id();
        if ($this->is_account_manager_user($user_id) && !$this->is_admin_user($user_id) && !$this->is_staff_role($user_id)) {
            $assigned_candidates = $this->get_assigned_candidate_ids_for_account_manager($user_id);
            if (!$assigned_candidates) {
                $assigned_candidates = [0];
            }
            $args['post__in'] = $assigned_candidates;
        }
        if ($status === 'deletion_requested') {
            $requested_ids = $this->get_candidate_ids_with_deletion_request();
            $args['post__in'] = $requested_ids ? $requested_ids : [0];
        } elseif ($status !== '') {
            $args['meta_query'] = [
                [
                    'key' => 'cmn_status',
                    'value' => $status,
                ],
            ];
        }
        $query = new WP_Query($args);

        ob_start();
        ?>
        <header class="cmn-school-header">
            <h2>Candidates CRM</h2>
            <p>Front-end CRM for staff. No WordPress admin required.</p>
        </header>
        <form method="get" class="cmn-filters">
            <input type="hidden" name="view" value="candidates">
            <input type="search" name="s" placeholder="Search candidates..." value="<?php echo esc_attr($search); ?>">
            <select name="cmn_status">
                <option value="">All Statuses</option>
                <?php foreach (['approved', 'rejected', 'deletion_requested'] as $opt) : ?>
                    <option value="<?php echo esc_attr($opt); ?>"<?php echo $status === $opt ? ' selected' : ''; ?>><?php echo esc_html(ucfirst(str_replace('_', ' ', $opt))); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="cmn-ghost" type="submit">Filter</button>
        </form>
        <table class="cmn-approval-table">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Location</th>
                    <th>Email</th>
                    <th>Documents</th>
                    <th>Status</th>
                    <th>Profile</th>
                    <?php if ($this->can_manage_staff_users()) : ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if ($query->have_posts()) : ?>
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <?php
                    $candidate_user_id = (int) get_post_meta(get_the_ID(), 'cmn_user_id', true);
                    $deletion_requested = $candidate_user_id ? get_user_meta($candidate_user_id, 'cmn_deletion_requested', true) : '';
                    $status_value = strtolower((string) get_post_meta(get_the_ID(), 'cmn_status', true));
                    if ($status_value === '' || $status_value === 'pending') {
                        $status_value = 'approved';
                    }
                    if ($deletion_requested) {
                        $status_value = 'deletion_requested';
                    }
                    $dbs_status = $this->get_candidate_doc_status((int) get_the_ID(), $candidate_user_id, 'dbs');
                    $id_status = $this->get_candidate_doc_status((int) get_the_ID(), $candidate_user_id, 'id');
                    $cv_status = $this->get_candidate_doc_status((int) get_the_ID(), $candidate_user_id, 'cv');
                    $doc_states = [
                        (string) ($dbs_status['doc_status'] ?? 'not_uploaded'),
                        (string) ($id_status['doc_status'] ?? 'not_uploaded'),
                        (string) ($cv_status['doc_status'] ?? 'not_uploaded'),
                    ];
                    $doc_uploaded_count = 0;
                    $doc_approved_count = 0;
                    $doc_pending_count = 0;
                    $doc_rejected_count = 0;
                    foreach ($doc_states as $doc_state) {
                        if ($doc_state !== 'not_uploaded') {
                            $doc_uploaded_count++;
                        }
                        if ($doc_state === 'approved') {
                            $doc_approved_count++;
                        } elseif ($doc_state === 'pending') {
                            $doc_pending_count++;
                        } elseif ($doc_state === 'rejected') {
                            $doc_rejected_count++;
                        }
                    }
                    ?>
                    <tr>
                        <td><?php the_title(); ?></td>
                        <td><?php echo esc_html(get_post_meta(get_the_ID(), 'cmn_location', true)); ?></td>
                        <td><?php echo esc_html(get_post_meta(get_the_ID(), 'cmn_email', true)); ?></td>
                        <td>
                            <span class="cmn-muted"><?php echo esc_html($doc_uploaded_count); ?>/3 uploaded</span><br>
                            <span class="cmn-muted">A: <?php echo esc_html($doc_approved_count); ?> · P: <?php echo esc_html($doc_pending_count); ?> · R: <?php echo esc_html($doc_rejected_count); ?></span>
                        </td>
                        <td><?php echo esc_html(str_replace('_', ' ', ucfirst($status_value))); ?></td>
                        <td>
                            <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['view' => 'candidates', 'candidate_id' => get_the_ID()], home_url('/portal'))); ?>">View profile</a>
                            <br>
                            <a class="cmn-ghost cmn-btn-mini" href="<?php echo esc_url(add_query_arg(['view' => 'candidates', 'candidate_id' => get_the_ID()], home_url('/portal')) . '#candidate-documents'); ?>">View docs</a>
                        </td>
                        <?php if ($this->can_manage_staff_users()) : ?>
                            <td>
                                <?php if ($deletion_requested && $candidate_user_id) : ?>
                                    <button class="cmn-danger" type="button" data-candidate-delete-btn data-candidate-id="<?php echo esc_attr(get_the_ID()); ?>" data-candidate-name="<?php echo esc_attr(get_the_title()); ?>" data-candidate-email="<?php echo esc_attr(get_post_meta(get_the_ID(), 'cmn_email', true)); ?>">Delete candidate</button>
                                <?php else : ?>
                                    <span class="cmn-muted">-</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; wp_reset_postdata(); ?>
            <?php else : ?>
                <tr><td colspan="<?php echo $this->can_manage_staff_users() ? '7' : '6'; ?>">No candidates found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php
        $inner = ob_get_clean();
        return $this->render_staff_shell('candidates', $inner);
    }

    public function render_staff_cv_converter_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        $user_id = get_current_user_id();
        if (!$this->user_can_manage_cv_converter($user_id)) {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>This section is available to staff and account managers.</p></div></section>';
        }
        $search = isset($_GET['s']) ? sanitize_text_field((string) $_GET['s']) : '';
        if ($search === '' && isset($_GET['q'])) {
            $search = sanitize_text_field((string) $_GET['q']);
        }
        $args = [
            'post_type' => 'cmn_candidate',
            'posts_per_page' => 100,
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        if ($search !== '') {
            $args['s'] = $search;
        }
        if ($this->is_account_manager_user($user_id) && !$this->is_admin_user($user_id) && !$this->is_staff_role($user_id)) {
            $assigned_candidates = $this->get_assigned_candidate_ids_for_account_manager($user_id);
            if (!$assigned_candidates) {
                $assigned_candidates = [0];
            }
            $args['post__in'] = $assigned_candidates;
        }
        $query = new WP_Query($args);
        $portal_url = $this->get_portal_base_url();

        ob_start();
        ?>
        <header class="cmn-school-header">
            <div class="cmn-header-row">
                <div>
                    <h2>CV Converter</h2>
                    <p>Convert candidate original CVs and place formatted versions on candidate profiles.</p>
                </div>
            </div>
        </header>
        <form method="get" class="cmn-filters">
            <input type="hidden" name="view" value="cv-converter">
            <input type="search" name="s" placeholder="Search candidates..." value="<?php echo esc_attr($search); ?>">
            <button class="cmn-ghost" type="submit">Search</button>
            <a class="cmn-ghost cmn-btn-mini" href="<?php echo esc_url(add_query_arg(['view' => 'cv-converter'], $portal_url)); ?>">Clear</a>
        </form>
        <table class="cmn-approval-table cmn-cv-converter-table">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Original CV</th>
                    <th>Formatted CV</th>
                    <th>Last converted at/by</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($query->have_posts()) : ?>
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <?php
                    $candidate_id = (int) get_the_ID();
                    $candidate_user_id = (int) $this->get_candidate_user_id($candidate_id);
                    $original_attachment_id = $this->get_candidate_cv_original_attachment_id($candidate_id, $candidate_user_id);
                    $original_uploaded_at = (string) get_post_meta($candidate_id, 'cmn_cv_original_uploaded_at', true);
                    if ($original_uploaded_at === '' && $candidate_user_id > 0) {
                        $original_uploaded_at = (string) get_user_meta($candidate_user_id, 'cmn_doc_cv_uploaded_at', true);
                    }
                    $formatted = $this->get_candidate_cv_formatted_status($candidate_id);
                    $formatted_label = $formatted['available'] ? 'Yes' : 'No';
                    if (!empty($formatted['outdated'])) {
                        $formatted_label = 'No (outdated)';
                    }
                    $generated_at_label = (string) ($formatted['generated_at_label'] ?? '');
                    $generated_by_label = (string) ($formatted['generated_by_name'] ?? '');
                    $candidate_profile_url = add_query_arg([
                        'view' => 'candidates',
                        'candidate_id' => $candidate_id,
                    ], $portal_url);
                    ?>
                    <tr data-cv-row="<?php echo esc_attr((string) $candidate_id); ?>">
                        <td>
                            <?php echo esc_html(get_the_title()); ?>
                            <div class="cmn-table-meta"><?php echo esc_html((string) get_post_meta($candidate_id, 'cmn_email', true)); ?></div>
                        </td>
                        <td>
                            <strong><?php echo $original_attachment_id > 0 ? 'Yes' : 'No'; ?></strong>
                            <div class="cmn-table-meta"><?php echo esc_html($original_uploaded_at !== '' ? date_i18n('M j, Y g:ia', strtotime($original_uploaded_at)) : '—'); ?></div>
                        </td>
                        <td>
                            <strong><?php echo esc_html($formatted_label); ?></strong>
                            <div class="cmn-table-meta"><?php echo esc_html((string) ($formatted['version'] !== '' ? ('Version ' . $formatted['version']) : '—')); ?></div>
                        </td>
                        <td>
                            <?php echo esc_html($generated_at_label !== '' ? $generated_at_label : '—'); ?>
                            <div class="cmn-table-meta"><?php echo esc_html($generated_by_label !== '' ? $generated_by_label : '—'); ?></div>
                        </td>
                        <td>
                            <button class="cmn-ghost cmn-btn-mini" type="button" data-download-original-cv="<?php echo esc_attr((string) $candidate_id); ?>"<?php echo $original_attachment_id > 0 ? '' : ' disabled'; ?>>Download original CV</button>
                            <button class="cmn-primary cmn-btn-mini" type="button" data-open-cv-converter="<?php echo esc_attr((string) $candidate_id); ?>"<?php echo $original_attachment_id > 0 ? '' : ' disabled'; ?>>Open Converter</button>
                            <a class="cmn-ghost cmn-btn-mini" href="<?php echo esc_url($candidate_profile_url); ?>">View profile</a>
                            <div class="cmn-muted" data-cv-row-message="<?php echo esc_attr((string) $candidate_id); ?>"></div>
                        </td>
                    </tr>
                <?php endwhile; wp_reset_postdata(); ?>
            <?php else : ?>
                <tr><td colspan="5">No candidates found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php
        return $this->render_staff_shell('cv_converter', ob_get_clean());
    }

    private function render_staff_candidate_profile($candidate_id) {
        $candidate_id = (int) $candidate_id;
        $candidate = get_post($candidate_id);
        if (!$candidate || $candidate->post_type !== 'cmn_candidate') {
            return '<div class="cmn-panel-card"><h3>Candidate not found</h3><p>The selected candidate could not be loaded.</p></div>';
        }

        $candidate_user_id = (int) $this->get_candidate_user_id($candidate_id);
        $portal_url = $this->get_portal_base_url();
        $back_url = add_query_arg(['view' => 'candidates'], $portal_url);

        $first_name = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'first_name', true) : '';
        $last_name = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'last_name', true) : '';
        $profile_name = trim($first_name . ' ' . $last_name);
        if ($profile_name === '') {
            $profile_name = $candidate->post_title;
        }
        $profile_email = (string) get_post_meta($candidate_id, 'cmn_email', true);
        if ($profile_email === '' && $candidate_user_id) {
            $candidate_user = get_user_by('id', $candidate_user_id);
            if ($candidate_user && $candidate_user->user_email) {
                $profile_email = (string) $candidate_user->user_email;
            }
        }
        $profile_phone = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'phone', true) : '';
        if ($profile_phone === '') {
            $profile_phone = (string) get_post_meta($candidate_id, 'cmn_phone', true);
        }
        $location = (string) get_post_meta($candidate_id, 'cmn_location', true);
        $postcode = (string) get_post_meta($candidate_id, 'cmn_postcode', true);
        $role_type = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'role_type', true) : '';
        if ($role_type === '') {
            $roles = (array) get_post_meta($candidate_id, 'cmn_roles', true);
            $role_type = isset($roles[0]) ? (string) $roles[0] : '';
        }
        $travel_radius = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'travel_radius', true) : '';
        if ($travel_radius === '') {
            $travel_radius = (string) get_post_meta($candidate_id, 'cmn_travel_distance', true);
        }
        $status_value = strtolower((string) get_post_meta($candidate_id, 'cmn_status', true));
        if ($status_value === '' || $status_value === 'pending') {
            $status_value = 'approved';
        }
        $doc_review_message = isset($_GET['cmn_doc_review_msg']) ? sanitize_text_field(wp_unslash((string) $_GET['cmn_doc_review_msg'])) : '';

        $completion_percent = $candidate_user_id ? (int) get_user_meta($candidate_user_id, 'cmn_profile_completion_pct', true) : 0;
        if ($candidate_user_id) {
            $completion_percent = (int) $this->update_candidate_profile_completion($candidate_id, $candidate_user_id);
        }

        $tomorrow_date = $this->get_tomorrow_date();
        $available_tomorrow = $tomorrow_date ? $this->has_candidate_availability($candidate_id, $tomorrow_date) : false;
        $calendar_summary = $this->get_candidate_calendar_summary($candidate_id);
        $next_available_label = $calendar_summary['next_available_label'] ?? 'Not set';
        $available_count = isset($calendar_summary['available_count']) ? (int) $calendar_summary['available_count'] : 0;
        $unavailable_count = isset($calendar_summary['unavailable_count']) ? (int) $calendar_summary['unavailable_count'] : 0;

        $doc_map = [
            'id' => 'Photo ID',
            'dbs' => 'DBS',
            'cv' => 'CV',
        ];
        $docs = [];
        foreach ($doc_map as $doc_type => $label) {
            $status = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, $doc_type);
            $download_url = '';
            if (!empty($status['uploaded'])) {
                if (!empty($status['attachment_id'])) {
                    $download_url = add_query_arg([
                        'action' => 'cmn_candidate_download_doc',
                        'candidate_id' => $candidate_id,
                        'doc_type' => $doc_type,
                        'cmn_nonce' => wp_create_nonce('cmn_candidate_download_doc_' . $candidate_id . '_' . $doc_type . '_' . get_current_user_id()),
                    ], admin_url('admin-post.php'));
                } elseif (!empty($status['url']) && $this->is_staff_user()) {
                    $download_url = esc_url_raw($status['url']);
                }
            }
            $docs[$doc_type] = [
                'label' => $label,
                'status' => $status,
                'download_url' => $download_url,
            ];
        }
        $doc_summary = $this->get_candidate_doc_summary([
            'dbs' => $docs['dbs']['status'],
            'id' => $docs['id']['status'],
            'cv' => $docs['cv']['status'],
        ]);
        $this->sync_candidate_admin_verification_status($candidate_id, $candidate_user_id, [
            'dbs' => $docs['dbs']['status'],
            'id' => $docs['id']['status'],
            'cv' => $docs['cv']['status'],
        ]);

        $roles_meta = (array) get_post_meta($candidate_id, 'cmn_roles', true);
        $availability_days = (array) get_post_meta($candidate_id, 'cmn_availability_days', true);
        $verification_status = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'cmn_admin_verification_status', true) : '';
        $email_verified = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'cmn_email_verified', true) : '';
        $profile_snapshot = [
            'House Number' => (string) get_post_meta($candidate_id, 'cmn_house_number', true),
            'Address Line 1' => (string) get_post_meta($candidate_id, 'cmn_address_line1', true),
            'Address Line 2' => (string) get_post_meta($candidate_id, 'cmn_address_line2', true),
            'Address Line 3' => (string) get_post_meta($candidate_id, 'cmn_address_line3', true),
            'Town / City' => (string) get_post_meta($candidate_id, 'cmn_town', true),
            'County' => (string) get_post_meta($candidate_id, 'cmn_county', true),
            'Postcode' => $postcode,
            'Roles (registration)' => $roles_meta ? implode(', ', array_filter(array_map('sanitize_text_field', $roles_meta))) : '',
            'Role Other' => (string) get_post_meta($candidate_id, 'cmn_roles_other', true),
            'Travel Distance' => (string) get_post_meta($candidate_id, 'cmn_travel_distance', true),
            'Driving Licence' => (string) get_post_meta($candidate_id, 'cmn_driving_licence', true),
            'Car Owner' => (string) get_post_meta($candidate_id, 'cmn_car_owner', true),
            'DBS Update Service' => (string) get_post_meta($candidate_id, 'cmn_dbs_update_service', true),
            'Availability Days' => $availability_days ? implode(', ', array_filter(array_map('sanitize_text_field', $availability_days))) : '',
            'No DBS selected' => ((string) get_post_meta($candidate_id, 'cmn_no_dbs', true) === '1') ? 'Yes' : 'No',
            'Email Verified' => $email_verified === '1' ? 'Yes' : 'No',
            'Admin Verification Status' => $verification_status !== '' ? str_replace('_', ' ', ucfirst($verification_status)) : '',
            'Notes' => (string) get_post_meta($candidate_id, 'cmn_notes', true),
        ];

        ob_start();
        ?>
        <header class="cmn-school-header">
            <div class="cmn-header-row">
                <div>
                    <h2>Candidate Profile</h2>
                    <p>Full profile and compliance documents for staff review.</p>
                </div>
                <a class="cmn-ghost" href="<?php echo esc_url($back_url); ?>">Back to candidates</a>
            </div>
        </header>
        <?php if ($doc_review_message !== '') : ?>
            <div class="cmn-panel-card"><strong><?php echo esc_html($doc_review_message); ?></strong></div>
        <?php endif; ?>

        <div class="cmn-profile-progress cmn-staff-candidate-progress">
            <div>
                <span>Profile Completion</span>
                <div class="cmn-progress-bar"><span style="width: <?php echo esc_attr($completion_percent); ?>%;"></span></div>
            </div>
            <strong><?php echo esc_html($completion_percent); ?>% Complete</strong>
        </div>

        <div class="cmn-profile-grid cmn-staff-candidate-profile">
            <div class="cmn-dashboard-card">
                <div class="cmn-card-header">
                    <h3>Personal Details</h3>
                    <span class="cmn-status-chip is-approved"><?php echo esc_html(ucfirst(str_replace('_', ' ', $status_value))); ?></span>
                </div>
                <p><strong><?php echo esc_html($profile_name); ?></strong></p>
                <p><?php echo esc_html($profile_email ?: 'Email not set'); ?></p>
                <p><?php echo esc_html($profile_phone ?: 'Phone not set'); ?></p>
                <p>Location: <?php echo esc_html($location ?: 'Not set'); ?></p>
                <p>Postcode: <?php echo esc_html($postcode ?: 'Not set'); ?></p>
            </div>

            <div class="cmn-dashboard-card">
                <div class="cmn-card-header">
                    <h3>Role & Preferences</h3>
                    <span class="cmn-status-chip <?php echo esc_attr($doc_summary['badge_class']); ?>"><?php echo esc_html($doc_summary['badge_label']); ?></span>
                </div>
                <p>Role Type: <?php echo esc_html($role_type ?: 'Not set'); ?></p>
                <p>Travel Radius: <?php echo esc_html($travel_radius ?: 'Not set'); ?></p>
                <p>Available tomorrow: <?php echo esc_html($available_tomorrow ? 'Yes' : 'No'); ?></p>
                <p>Next available date: <?php echo esc_html($next_available_label); ?></p>
                <p>Planner summary: <?php echo esc_html($available_count); ?> available / <?php echo esc_html($unavailable_count); ?> unavailable</p>
            </div>

            <div class="cmn-dashboard-card cmn-dashboard-card-wide">
                <div class="cmn-card-header">
                    <h3>Full Registration Snapshot</h3>
                </div>
                <div class="cmn-profile-meta-grid">
                    <?php foreach ($profile_snapshot as $label => $value) : ?>
                        <div class="cmn-profile-meta-item">
                            <span class="cmn-profile-meta-label"><?php echo esc_html($label); ?></span>
                            <strong class="cmn-profile-meta-value"><?php echo esc_html($value !== '' ? $value : 'Not set'); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="cmn-dashboard-card cmn-compliance-status">
                <div class="cmn-card-header">
                    <h3>Compliance Status</h3>
                    <span class="cmn-status-chip <?php echo esc_attr($doc_summary['badge_class']); ?>"><?php echo esc_html($doc_summary['badge_label']); ?></span>
                </div>
                <ul class="cmn-status-list">
                    <li class="<?php echo ($docs['dbs']['status']['doc_status'] ?? '') === 'approved' ? 'is-ok' : 'is-warn'; ?>">DBS <?php echo esc_html($docs['dbs']['status']['status_label'] ?? 'Not Uploaded'); ?></li>
                    <li class="<?php echo ($docs['id']['status']['doc_status'] ?? '') === 'approved' ? 'is-ok' : 'is-warn'; ?>">ID <?php echo esc_html($docs['id']['status']['status_label'] ?? 'Not Uploaded'); ?></li>
                    <li class="<?php echo ($docs['cv']['status']['doc_status'] ?? '') === 'approved' ? 'is-ok' : 'is-warn'; ?>">CV <?php echo esc_html($docs['cv']['status']['status_label'] ?? 'Not Uploaded'); ?></li>
                </ul>
            </div>

            <div class="cmn-dashboard-card cmn-doc-upload-card cmn-dashboard-card-wide" id="candidate-documents">
                <div class="cmn-card-header">
                    <h3>Uploaded Documents</h3>
                </div>
                <div class="cmn-doc-actions">
                    <?php foreach ($docs as $doc_type => $doc) : ?>
                        <?php
                        $doc_status = $doc['status'];
                        $is_uploaded = !empty($doc_status['uploaded']);
                        ?>
                        <div class="cmn-doc-card cmn-doc-tile">
                            <div class="cmn-doc-card-head">
                                <strong><?php echo esc_html($doc['label']); ?></strong>
                                <span class="cmn-status-chip <?php echo esc_attr($doc_status['badge_class'] ?? ($is_uploaded ? 'is-pending' : 'is-declined')); ?>"><?php echo esc_html($doc_status['status_label'] ?? ($is_uploaded ? 'Pending Review' : 'Not Uploaded')); ?></span>
                            </div>
                            <div class="cmn-doc-card-meta">
                                <div class="cmn-doc-line"><?php echo esc_html($is_uploaded ? ($doc_status['filename'] ?: 'Uploaded file') : 'Not uploaded'); ?></div>
                                <div class="cmn-doc-subline">
                                    <span><?php echo esc_html($doc_status['uploaded_at_label'] ?: '-'); ?></span>
                                    <span><?php echo esc_html($doc_status['filesize_label'] ?: '-'); ?></span>
                                </div>
                                <?php if (!empty($doc_status['review_reason']) && ($doc_status['doc_status'] ?? '') === 'rejected') : ?>
                                    <div class="cmn-doc-subline"><span>Reason: <?php echo esc_html($doc_status['review_reason']); ?></span></div>
                                <?php endif; ?>
                            </div>
                            <div class="cmn-doc-buttons">
                                <?php if ($is_uploaded && !empty($doc['download_url'])) : ?>
                                    <a class="cmn-ghost cmn-doc-link" href="<?php echo esc_url($doc['download_url']); ?>" target="_blank" rel="noopener noreferrer">View / Download</a>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-doc-review-form">
                                        <?php wp_nonce_field('cmn_staff_review_candidate_doc', 'cmn_staff_review_candidate_doc_nonce'); ?>
                                        <input type="hidden" name="action" value="cmn_staff_review_candidate_doc">
                                        <input type="hidden" name="candidate_id" value="<?php echo esc_attr($candidate_id); ?>">
                                        <input type="hidden" name="doc_type" value="<?php echo esc_attr($doc_type); ?>">
                                        <textarea name="review_reason" rows="2" placeholder="Reason (only used for rejection)"></textarea>
                                        <div class="cmn-doc-review-actions">
                                            <button class="cmn-primary" type="submit" name="review_action" value="approved">Approve</button>
                                            <button class="cmn-ghost" type="submit" name="review_action" value="rejected">Reject</button>
                                        </div>
                                    </form>
                                <?php else : ?>
                                    <span class="cmn-muted">No file uploaded</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_war_room_column_key($request) {
        $status = strtolower((string) ($request['status'] ?? 'requested'));
        if ($status === 'pending') {
            $status = 'requested';
        }
        if (in_array($status, ['accepted', 'confirmed'], true)) {
            return 'accepted';
        }
        if (in_array($status, ['declined', 'cancelled'], true)) {
            return 'declined';
        }
        if ($status === 'expired') {
            return 'expired';
        }
        if (in_array($status, ['candidate_contacted', 'staff_reviewing', 'negotiation'], true)) {
            return 'negotiations';
        }
        if ($status === 'requested') {
            $request_id = (int) ($request['id'] ?? 0);
            if ($request_id > 0) {
                $booking_id = $this->get_booking_id_for_request($request_id);
                if ($booking_id > 0) {
                    $active_thread = sanitize_key((string) get_post_meta($booking_id, 'cmn_active_thread', true));
                    if ($active_thread === 'pay_negotiation') {
                        return 'negotiations';
                    }
                }
            }
            return 'pending';
        }
        return 'pending';
    }

    public function render_staff_war_room_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        if (!$this->is_admin_user()) {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>War Room is available to admins only.</p></div></section>';
        }

        $portal_url = $this->get_portal_base_url();
        $requests = $this->get_candidate_requests_for_status('all', 250);
        $columns = [
            'pending' => ['label' => 'Pending Requests', 'items' => []],
            'negotiations' => ['label' => 'Negotiations', 'items' => []],
            'accepted' => ['label' => 'Accepted', 'items' => []],
            'declined' => ['label' => 'Declined', 'items' => []],
            'expired' => ['label' => 'Expired', 'items' => []],
        ];

        foreach ($requests as $request) {
            $key = $this->get_war_room_column_key($request);
            if (!isset($columns[$key])) {
                $key = 'pending';
            }
            $columns[$key]['items'][] = $request;
        }

        ob_start();
        ?>
        <header class="cmn-school-header cmn-dashboard-header">
            <div class="cmn-header-row">
                <div>
                    <h2>Booking War Room</h2>
                    <p>Live booking operations board. Refreshes every 10 seconds.</p>
                </div>
                <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['view' => 'requests'], $portal_url)); ?>">Open Requests</a>
            </div>
        </header>
        <section class="cmn-war-room" data-war-room-root data-refresh-seconds="10">
            <?php foreach ($columns as $column_key => $column) : ?>
                <div class="cmn-war-room-column" data-war-room-column="<?php echo esc_attr($column_key); ?>">
                    <div class="cmn-war-room-column-head">
                        <h3><?php echo esc_html($column['label']); ?></h3>
                        <span class="cmn-status-chip"><?php echo esc_html((string) count($column['items'])); ?></span>
                    </div>
                    <div class="cmn-war-room-cards">
                        <?php if (!$column['items']) : ?>
                            <div class="cmn-empty">No items.</div>
                        <?php else : ?>
                            <?php foreach ($column['items'] as $request) : ?>
                                <?php
                                $request_id = (int) ($request['id'] ?? 0);
                                $school_name = $this->get_school_name_by_domain((string) ($request['school_email_domain'] ?? '')) ?: 'School';
                                $candidate = get_post((int) ($request['candidate_id'] ?? 0));
                                $candidate_name = $candidate ? $candidate->post_title : 'Candidate';
                                $charge_rate = isset($request['school_charge_rate']) && (float) $request['school_charge_rate'] > 0
                                    ? (float) $request['school_charge_rate']
                                    : $this->get_request_school_charge_rate($this->get_request_candidate_pay_rate((int) ($request['candidate_id'] ?? 0), (int) ($request['school_id'] ?? 0), $request), $request);
                                $expires_at = $this->get_request_expires_at($request);
                                $expires_ts = $expires_at ? strtotime($expires_at) : 0;
                                $view_status = sanitize_key((string) ($request['status'] ?? 'requested'));
                                if ($view_status === 'pending') {
                                    $view_status = 'requested';
                                }
                                $view_url = add_query_arg(['view' => 'requests', 'cmn_status' => $view_status], $portal_url);
                                ?>
                                <article class="cmn-war-room-card">
                                    <div class="cmn-war-room-line"><strong><?php echo esc_html($school_name); ?></strong></div>
                                    <div class="cmn-war-room-line"><?php echo esc_html($candidate_name); ?></div>
                                    <div class="cmn-war-room-line">Day rate: <strong>£<?php echo esc_html(number_format((float) $charge_rate, 2)); ?></strong></div>
                                    <?php if ($column_key === 'pending') : ?>
                                        <div class="cmn-war-room-line cmn-war-room-countdown" data-war-room-countdown<?php echo $expires_ts > 0 ? ' data-expires-ts="' . esc_attr((string) $expires_ts) . '"' : ''; ?>>
                                            <?php echo esc_html($expires_ts > 0 ? 'Calculating…' : 'No timer'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="cmn-war-room-actions">
                                        <a class="cmn-ghost cmn-btn-mini" href="<?php echo esc_url($view_url); ?>">View</a>
                                        <?php if (in_array($column_key, ['pending', 'expired'], true) && $request_id > 0) : ?>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                                <?php wp_nonce_field('cmn_update_candidate_request', 'cmn_update_candidate_request_nonce'); ?>
                                                <input type="hidden" name="action" value="cmn_update_candidate_request">
                                                <input type="hidden" name="cmn_request_id" value="<?php echo esc_attr((string) $request_id); ?>">
                                                <input type="hidden" name="cmn_request_action" value="refresh">
                                                <button class="cmn-ghost cmn-btn-mini" type="submit">Refresh request</button>
                                            </form>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                                <?php wp_nonce_field('cmn_update_candidate_request', 'cmn_update_candidate_request_nonce'); ?>
                                                <input type="hidden" name="action" value="cmn_update_candidate_request">
                                                <input type="hidden" name="cmn_request_id" value="<?php echo esc_attr((string) $request_id); ?>">
                                                <input type="hidden" name="cmn_request_action" value="no_longer_needed">
                                                <button class="cmn-ghost cmn-btn-mini" type="submit">Mark no longer needed</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
        <?php
        return $this->render_staff_shell('war_room', ob_get_clean());
    }

    public function render_staff_requests_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        if (!$this->is_staff_user()) {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>This section is available to staff only.</p></div></section>';
        }
        $status_filter = isset($_GET['cmn_status']) ? sanitize_text_field($_GET['cmn_status']) : 'requested';
        $message = isset($_GET['cmn_request_msg']) ? sanitize_text_field(wp_unslash($_GET['cmn_request_msg'])) : '';
        $user_id = get_current_user_id();
        $assigned_domains = [];
        if ($this->is_account_manager_user($user_id) && !$this->is_admin_user($user_id) && !$this->is_staff_role($user_id)) {
            $assigned_ids = $this->get_assigned_school_ids_for_account_manager($user_id);
            foreach ($assigned_ids as $school_id) {
                $domain = get_post_meta($school_id, 'cmn_school_email_domain', true);
                if ($domain) {
                    $assigned_domains[] = $domain;
                }
            }
            $assigned_domains = array_values(array_unique($assigned_domains));
            if (!$assigned_domains) {
                $assigned_domains = ['__none__'];
            }
        }
        $requests = $this->get_candidate_requests_for_status($status_filter, 100, $assigned_domains);
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');

        ob_start();
        ?>
        <header class="cmn-school-header">
            <div class="cmn-header-row">
                <div>
                    <h2>Candidate Requests</h2>
                    <p>Manage school requests and confirm candidate availability.</p>
                </div>
            </div>
        </header>
        <?php if ($message) : ?>
            <div class="cmn-panel-card"><strong><?php echo esc_html($message); ?></strong></div>
        <?php endif; ?>
        <form method="get" class="cmn-filters">
            <input type="hidden" name="view" value="requests">
            <select name="cmn_status">
                <?php foreach (['requested' => 'Requested', 'accepted' => 'Accepted', 'declined' => 'Declined', 'expired' => 'Expired', 'cancelled' => 'Cancelled', 'confirmed' => 'Confirmed', 'all' => 'All'] as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>"<?php echo $status_filter === $value ? ' selected' : ''; ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="cmn-ghost" type="submit">Filter</button>
        </form>
        <table class="cmn-approval-table cmn-request-table">
            <thead>
                <tr>
                    <th>School</th>
                    <th>Candidate</th>
                    <th>Date</th>
                    <th>Requested</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($requests) : ?>
                <?php foreach ($requests as $request) : ?>
                    <?php
                    $candidate = get_post((int) $request['candidate_id']);
                    $candidate_name = $candidate ? $candidate->post_title : 'Candidate';
                    $school_name = $this->get_school_name_by_domain($request['school_email_domain'] ?? '');
                    $requested_date = $request['requested_date'] ?? '';
                    $requested_at = $request['requested_at'] ?? '';
                    $status = $request['status'] ?? 'pending';
                    $status_label = ucfirst($status);
                    if ($status === 'pending') {
                        $status = 'requested';
                        $status_label = 'Requested';
                    }
                    $pay_rate = isset($request['candidate_pay_rate']) ? (float) $request['candidate_pay_rate'] : 0;
                    $expires_at = $this->get_request_expires_at($request);
                    $expired_now = $status === 'requested' && $expires_at && strtotime($expires_at) <= current_time('timestamp', true);
                    $request_booking_id = $this->get_booking_id_for_request((int) ($request['id'] ?? 0));
                    ?>
                    <tr>
                        <td><?php echo esc_html($school_name ?: 'School'); ?></td>
                        <td><?php echo esc_html($candidate_name); ?></td>
                        <td><?php echo esc_html($requested_date ? date_i18n('M j, Y', strtotime($requested_date)) : ''); ?></td>
                        <td><?php echo esc_html($requested_at ? date_i18n('g:ia', strtotime($requested_at)) : ''); ?></td>
                        <td><span class="cmn-pill cmn-pill--<?php echo esc_attr($status); ?>"><?php echo esc_html($status_label); ?></span></td>
                        <td>
                            <?php if ($status === 'requested') : ?>
                                <button class="cmn-ghost cmn-btn-mini" type="button" data-request-toggle="confirm-<?php echo esc_attr($request['id']); ?>">Confirm</button>
                                <button class="cmn-ghost cmn-btn-mini" type="button" data-request-toggle="decline-<?php echo esc_attr($request['id']); ?>">Decline</button>
                                <?php if ($expired_now) : ?><span class="cmn-muted"><br>Expired</span><?php endif; ?>
                            <?php elseif ($status === 'expired') : ?>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                    <?php wp_nonce_field('cmn_update_candidate_request', 'cmn_update_candidate_request_nonce'); ?>
                                    <input type="hidden" name="action" value="cmn_update_candidate_request">
                                    <input type="hidden" name="cmn_request_id" value="<?php echo esc_attr($request['id']); ?>">
                                    <input type="hidden" name="cmn_request_action" value="refresh">
                                    <button class="cmn-ghost cmn-btn-mini" type="submit">Refresh request</button>
                                </form>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                    <?php wp_nonce_field('cmn_update_candidate_request', 'cmn_update_candidate_request_nonce'); ?>
                                    <input type="hidden" name="action" value="cmn_update_candidate_request">
                                    <input type="hidden" name="cmn_request_id" value="<?php echo esc_attr($request['id']); ?>">
                                    <input type="hidden" name="cmn_request_action" value="no_longer_needed">
                                    <button class="cmn-ghost cmn-btn-mini" type="submit">No longer needed</button>
                                </form>
                            <?php else : ?>
                                <span class="cmn-muted">—</span>
                            <?php endif; ?>
                            <?php if ($request_booking_id && in_array($status, ['accepted', 'confirmed', 'declined', 'cancelled'], true)) : ?>
                                <br>
                                <a class="cmn-ghost cmn-btn-mini" href="<?php echo esc_url(add_query_arg(['view' => 'requests', 'cmn_booking_chat' => $request_booking_id, 'cmn_thread_type' => $status === 'declined' ? 'decline_followup' : 'booking_details'], $portal_url)); ?>">Open booking chat</a>
                            <?php endif; ?>
                            <?php if ($request_booking_id && $status === 'requested') : ?>
                                <br>
                                <a class="cmn-ghost cmn-btn-mini" href="<?php echo esc_url(add_query_arg(['view' => 'requests', 'cmn_booking_chat' => $request_booking_id, 'cmn_thread_type' => 'pay_negotiation'], $portal_url)); ?>">Open pay negotiation</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($status === 'requested') : ?>
                        <tr class="cmn-request-panel-row" data-request-panel="confirm-<?php echo esc_attr($request['id']); ?>">
                            <td colspan="6">
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-request-panel">
                                    <?php wp_nonce_field('cmn_update_candidate_request', 'cmn_update_candidate_request_nonce'); ?>
                                    <input type="hidden" name="action" value="cmn_update_candidate_request">
                                    <input type="hidden" name="cmn_request_id" value="<?php echo esc_attr($request['id']); ?>">
                                    <input type="hidden" name="cmn_request_action" value="confirm">
                                    <label>Confirmed availability
                                        <select name="cmn_confirmed">
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                        </select>
                                    </label>
                                    <label>Internal note (optional)
                                        <textarea name="cmn_internal_note" rows="2" placeholder="Spoke to candidate at 7:45am"></textarea>
                                    </label>
                                    <label>Candidate pay rate (£)
                                        <input type="number" step="0.01" min="0" name="cmn_candidate_pay_rate" value="<?php echo esc_attr($pay_rate > 0 ? number_format($pay_rate, 2, '.', '') : ''); ?>">
                                    </label>
                                    <button class="cmn-primary" type="submit">Submit</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="cmn-request-panel-row" data-request-panel="decline-<?php echo esc_attr($request['id']); ?>">
                            <td colspan="6">
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-request-panel">
                                    <?php wp_nonce_field('cmn_update_candidate_request', 'cmn_update_candidate_request_nonce'); ?>
                                    <input type="hidden" name="action" value="cmn_update_candidate_request">
                                    <input type="hidden" name="cmn_request_id" value="<?php echo esc_attr($request['id']); ?>">
                                    <input type="hidden" name="cmn_request_action" value="decline">
                                    <label>Decline reason (optional)
                                        <textarea name="cmn_internal_note" rows="2"></textarea>
                                    </label>
                                    <button class="cmn-ghost" type="submit">Decline Request</button>
                                </form>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="6">No requests found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php
        $staff_chat_booking_id = isset($_GET['cmn_booking_chat']) ? (int) $_GET['cmn_booking_chat'] : 0;
        $staff_chat_thread_type = sanitize_key((string) ($_GET['cmn_thread_type'] ?? 'booking_details'));
        if (!in_array($staff_chat_thread_type, ['booking_details', 'pay_negotiation', 'decline_followup'], true)) {
            $staff_chat_thread_type = 'booking_details';
        }
        if ($staff_chat_booking_id) :
            $staff_chat_thread = $this->get_booking_thread_by_booking($staff_chat_booking_id, $staff_chat_thread_type);
            if ($staff_chat_thread && $this->user_can_access_booking_thread((int) $staff_chat_thread['id'], get_current_user_id())) :
                $staff_booking_ref = get_the_title($staff_chat_booking_id);
                if (!$staff_booking_ref) {
                    $staff_booking_ref = 'Booking #' . (int) $staff_chat_booking_id;
                }
        ?>
            <div class="cmn-dashboard-card cmn-request-chat-panel">
                <div class="cmn-card-header">
                    <h3><?php echo esc_html($staff_chat_thread_type === 'pay_negotiation' ? 'Pay Negotiation Chat' : 'Booking Chat'); ?></h3>
                    <span class="cmn-muted"><?php echo esc_html($staff_booking_ref); ?></span>
                </div>
                <div class="cmn-support-messages" data-booking-chat data-thread-id="<?php echo esc_attr((int) $staff_chat_thread['id']); ?>" data-thread-role="<?php echo esc_attr($this->is_admin_user() ? 'admin' : 'account_manager'); ?>">
                    <?php foreach ($this->get_booking_thread_messages((int) $staff_chat_thread['id']) as $chat_msg) : ?>
                        <?php
                        $chat_sender_role = sanitize_key((string) ($chat_msg['sender_role_type'] ?? 'system'));
                        $chat_class = 'is-system';
                        if ($chat_sender_role === 'candidate' || $chat_sender_role === 'school') {
                            $chat_class = 'is-user is-' . $chat_sender_role;
                        } elseif (in_array($chat_sender_role, ['account_manager', 'admin'], true)) {
                            $chat_class = 'is-admin is-' . $chat_sender_role;
                        }
                        ?>
                        <div class="cmn-support-bubble cmn-booking-bubble <?php echo esc_attr($chat_class); ?>">
                            <div class="cmn-support-meta"><?php echo esc_html(ucfirst(str_replace('_', ' ', $chat_msg['sender_role_type']))); ?> · <?php echo esc_html(date_i18n('M j, g:ia', strtotime($chat_msg['created_at']))); ?></div>
                            <div class="cmn-support-text"><?php echo esc_html($chat_msg['message']); ?></div>
                            <?php $chat_attachments = $this->get_booking_message_attachments($chat_msg); ?>
                            <?php if ($chat_attachments) : ?>
                                <div class="cmn-support-attachments">
                                    <?php foreach ($chat_attachments as $attachment) : ?>
                                        <a class="cmn-support-attachment-chip" href="<?php echo esc_url($attachment['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($attachment['filename']); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-support-reply" enctype="multipart/form-data">
                    <?php wp_nonce_field('cmn_booking_chat', 'cmn_booking_chat_nonce'); ?>
                    <input type="hidden" name="action" value="cmn_booking_chat_post">
                    <input type="hidden" name="cmn_thread_id" value="<?php echo esc_attr((int) $staff_chat_thread['id']); ?>">
                    <textarea name="cmn_message" rows="3" required placeholder="Type your message..."></textarea>
                    <input type="file" name="cmn_attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.txt">
                    <button class="cmn-primary" type="submit">Send</button>
                </form>
            </div>
        <?php
            endif;
        endif;
        ?>
        <?php
        $inner = ob_get_clean();
        return $this->render_staff_shell('requests', $inner);
    }

    public function render_staff_bookings_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        $status = isset($_GET['cmn_status']) ? sanitize_text_field($_GET['cmn_status']) : '';
        $args = [
            'post_type' => 'cmn_booking',
            'posts_per_page' => 50,
        ];
        if ($status !== '') {
            $args['meta_query'] = [
                [
                    'key' => 'cmn_status',
                    'value' => $status,
                ],
            ];
        }
        $query = new WP_Query($args);

        ob_start();
        ?>
        <header class="cmn-school-header">
            <h2>Bookings</h2>
            <p>Review and approve bookings here.</p>
        </header>
            <form method="get" class="cmn-filters">
                <input type="hidden" name="view" value="bookings">
                <select name="cmn_status">
                    <option value="">All Statuses</option>
                    <?php foreach (['requested', 'candidate_invited', 'candidate_accepted', 'candidate_declined', 'accepted', 'confirmed', 'completed', 'approved', 'declined', 'expired'] as $opt) : ?>
                        <option value="<?php echo esc_attr($opt); ?>"<?php echo $status === $opt ? ' selected' : ''; ?>><?php echo esc_html(ucfirst(str_replace('_', ' ', $opt))); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="cmn-ghost" type="submit">Filter</button>
            </form>
            <table class="cmn-approval-table">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>School</th>
                        <th>Dates</th>
                        <th>Role</th>
                        <th>Candidate</th>
                        <th>Rate</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($query->have_posts()) : ?>
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <?php
                        $status_val = get_post_meta(get_the_ID(), 'cmn_status', true);
                        $school_id = (int) get_post_meta(get_the_ID(), 'cmn_school_id', true);
                        $candidate_id = (int) get_post_meta(get_the_ID(), 'cmn_candidate_id', true);
                        $school_name = $school_id ? get_the_title($school_id) : 'Unassigned';
                        $candidate_name = $candidate_id ? get_the_title($candidate_id) : 'Unassigned';
                        $start_date = get_post_meta(get_the_ID(), 'cmn_start_date', true);
                        $end_date = get_post_meta(get_the_ID(), 'cmn_end_date', true);
                        $time_range = trim(get_post_meta(get_the_ID(), 'cmn_start_time', true) . ' - ' . get_post_meta(get_the_ID(), 'cmn_end_time', true));
                        $rate = $candidate_id ? $this->get_candidate_rate($candidate_id, $school_id) : '';
                        $deadline = (int) get_post_meta(get_the_ID(), 'cmn_candidate_deadline', true);
                        $remaining = $deadline ? max(0, $deadline - time()) : 0;
                        ?>
                        <tr>
                            <td><?php the_title(); ?></td>
                            <td><?php echo esc_html($school_name); ?></td>
                            <td><?php echo esc_html($start_date); ?><?php echo $end_date ? ' to ' . esc_html($end_date) : ''; ?><br><span class="cmn-muted"><?php echo esc_html($time_range); ?></span></td>
                            <td><?php echo esc_html(get_post_meta(get_the_ID(), 'cmn_role', true)); ?></td>
                            <td><?php echo esc_html($candidate_name); ?></td>
                            <td><?php echo $rate !== '' ? '£' . esc_html(number_format($rate, 0)) : '—'; ?></td>
                            <td><?php echo esc_html($status_val); ?><?php if ($status_val === 'candidate_invited' && $remaining > 0) : ?><br><span class="cmn-muted">Expires in <?php echo esc_html(ceil($remaining / 60)); ?> mins</span><?php endif; ?></td>
                            <td>
                                <?php if (in_array($status_val, ['requested', 'expired', 'candidate_declined'], true)) : ?>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                        <?php wp_nonce_field('cmn_send_candidate_invite', 'cmn_send_candidate_invite_nonce'); ?>
                                        <input type="hidden" name="action" value="cmn_send_candidate_invite">
                                        <input type="hidden" name="cmn_booking_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                        <select name="cmn_candidate_id">
                                            <?php
                                            $options = $school_id ? $this->get_assigned_candidates($school_id) : [];
                                            if (!$options) {
                                                $options = wp_list_pluck($this->get_available_candidates($school_id), 'ID');
                                            }
                                            foreach ($options as $opt_id) :
                                                $label = get_the_title($opt_id);
                                            ?>
                                                <option value="<?php echo esc_attr($opt_id); ?>"><?php echo esc_html($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="cmn-ghost" type="submit">Send to Candidate</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($status_val === 'candidate_accepted') : ?>
                                    <?php echo $this->render_status_form('booking', get_the_ID(), 'approved', 'Approve'); ?>
                                <?php endif; ?>
                                <?php if (!in_array($status_val, ['approved', 'declined'], true)) : ?>
                                    <?php echo $this->render_status_form('booking', get_the_ID(), 'declined', 'Decline'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <tr><td colspan="8">No bookings found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        <?php
        $inner = ob_get_clean();
        return $this->render_staff_shell('bookings', $inner);
    }

    public function render_staff_analytics_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        if (!$this->is_staff_user()) {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>This section is available to staff only.</p></div></section>';
        }
        $total_schools = $this->count_total_posts('cmn_school');
        $total_candidates = $this->count_total_posts('cmn_candidate');
        $total_bookings = $this->count_total_posts('cmn_booking');
        $feedback_filter = isset($_GET['cmn_feedback_filter']) ? sanitize_key((string) $_GET['cmn_feedback_filter']) : 'all';
        if (!in_array($feedback_filter, ['all', 'low'], true)) {
            $feedback_filter = 'all';
        }
        $feedback_analytics = $this->get_booking_feedback_analytics($feedback_filter, 60);
        $all_feedback_url = add_query_arg(['view' => 'analytics', 'cmn_feedback_filter' => 'all'], $this->get_portal_base_url());
        $low_feedback_url = add_query_arg(['view' => 'analytics', 'cmn_feedback_filter' => 'low'], $this->get_portal_base_url());

        ob_start();
        ?>
        <header class="cmn-school-header">
            <h2>Analytics</h2>
            <p>Usage and activity snapshots across the portal.</p>
        </header>
        <div class="cmn-portal-grid">
            <div class="cmn-dashboard-card">
                <h3>Total Schools</h3>
                <p><?php echo esc_html($total_schools); ?> registered</p>
            </div>
            <div class="cmn-dashboard-card">
                <h3>Total Candidates</h3>
                <p><?php echo esc_html($total_candidates); ?> registered</p>
            </div>
            <div class="cmn-dashboard-card">
                <h3>Total Bookings</h3>
                <p><?php echo esc_html($total_bookings); ?> created</p>
            </div>
        </div>
        <div class="cmn-portal-grid">
            <div class="cmn-dashboard-card">
                <h3>Approval Pipeline</h3>
                <p>Pending schools: <?php echo esc_html($this->count_by_status('cmn_school', 'pending')); ?></p>
                <p>Pending candidates: <?php echo esc_html($this->count_by_status('cmn_candidate', 'pending')); ?></p>
            </div>
            <div class="cmn-dashboard-card">
                <h3>Cover Demand</h3>
                <p>Open booking requests: <?php echo esc_html($this->count_bookings_by_status('requested')); ?></p>
                <p>Bookings today: <?php echo esc_html($this->count_bookings_today()); ?></p>
            </div>
        </div>
        <div class="cmn-portal-grid">
            <div class="cmn-dashboard-card">
                <h3>Booking Feedback</h3>
                <p>Total feedback entries: <?php echo esc_html((int) ($feedback_analytics['totals']['feedback_count'] ?? 0)); ?></p>
                <p>Average overall score: <?php echo esc_html(number_format((float) ($feedback_analytics['totals']['avg_overall'] ?? 0), 2)); ?>/5</p>
                <p>Auto-flagged low ratings (≤2): <?php echo esc_html((int) ($feedback_analytics['totals']['low_count'] ?? 0)); ?></p>
                <div class="cmn-support-filters">
                    <a class="cmn-ghost<?php echo $feedback_filter === 'all' ? ' is-active' : ''; ?>" href="<?php echo esc_url($all_feedback_url); ?>">All feedback</a>
                    <a class="cmn-ghost<?php echo $feedback_filter === 'low' ? ' is-active' : ''; ?>" href="<?php echo esc_url($low_feedback_url); ?>">Low overall (≤2)</a>
                </div>
            </div>
        </div>
        <div class="cmn-portal-grid">
            <div class="cmn-dashboard-card cmn-dashboard-card-wide">
                <h3>Recent Feedback</h3>
                <?php if (!empty($feedback_analytics['recent'])) : ?>
                    <table class="cmn-approval-table">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>School</th>
                                <th>Candidate</th>
                                <th>Overall</th>
                                <th>Tags</th>
                                <th>Comment</th>
                                <th>Flag</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($feedback_analytics['recent'] as $feedback_row) : ?>
                            <tr>
                                <td><?php echo esc_html($feedback_row['created_at'] ? date_i18n('M j, Y g:ia', strtotime((string) $feedback_row['created_at'])) : ''); ?></td>
                                <td><?php echo esc_html((string) ($feedback_row['school_name'] ?? 'School')); ?></td>
                                <td><?php echo esc_html((string) ($feedback_row['candidate_name'] ?? 'Candidate')); ?></td>
                                <td><?php echo esc_html((int) ($feedback_row['stars_overall'] ?? 0)); ?>/5</td>
                                <td><?php echo esc_html(!empty($feedback_row['tags']) ? implode(', ', (array) $feedback_row['tags']) : '—'); ?></td>
                                <td><?php echo esc_html((string) ($feedback_row['comment'] ?: '—')); ?></td>
                                <td><?php echo !empty($feedback_row['is_low']) ? '<span class="cmn-status-chip is-declined">Review</span>' : '<span class="cmn-status-chip is-approved">OK</span>'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="cmn-empty">No booking feedback yet.</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="cmn-portal-grid">
            <div class="cmn-dashboard-card">
                <h3>School Trends</h3>
                <?php if (!empty($feedback_analytics['school_trends'])) : ?>
                    <div class="cmn-list">
                        <?php foreach ($feedback_analytics['school_trends'] as $trend) : ?>
                            <div class="cmn-list-item">
                                <strong><?php echo esc_html((string) ($trend['label'] ?: 'School')); ?></strong>
                                <span><?php echo esc_html(number_format((float) ($trend['avg'] ?? 0), 2)); ?>/5 · <?php echo esc_html((int) ($trend['count'] ?? 0)); ?> feedback</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="cmn-empty">No school trend data yet.</div>
                <?php endif; ?>
            </div>
            <div class="cmn-dashboard-card">
                <h3>Candidate Trends</h3>
                <?php if (!empty($feedback_analytics['candidate_trends'])) : ?>
                    <div class="cmn-list">
                        <?php foreach ($feedback_analytics['candidate_trends'] as $trend) : ?>
                            <div class="cmn-list-item">
                                <strong><?php echo esc_html((string) ($trend['label'] ?: 'Candidate')); ?></strong>
                                <span><?php echo esc_html(number_format((float) ($trend['avg'] ?? 0), 2)); ?>/5 · <?php echo esc_html((int) ($trend['count'] ?? 0)); ?> feedback</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="cmn-empty">No candidate trend data yet.</div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        $inner = ob_get_clean();
        return $this->render_staff_shell('analytics', $inner);
    }

    public function render_staff_invoicing_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        ob_start();
        ?>
        <header class="cmn-school-header">
            <h2>Invoicing</h2>
            <p>Track invoices, timesheets, and payments.</p>
        </header>
        <div class="cmn-portal-grid">
            <div class="cmn-dashboard-card">
                <h3>Outstanding Invoices</h3>
                <p>0 outstanding (placeholder)</p>
            </div>
            <div class="cmn-dashboard-card">
                <h3>Pending Timesheets</h3>
                <p>0 pending (placeholder)</p>
            </div>
            <div class="cmn-dashboard-card">
                <h3>Recent Bookings</h3>
                <div class="cmn-list">
                    <?php echo $this->render_latest_bookings(); ?>
                </div>
            </div>
        </div>
        <?php
        $inner = ob_get_clean();
        return $this->render_staff_shell('invoicing', $inner);
    }

    public function render_staff_support_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        if (!$this->is_staff_user()) {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>This section is available to staff only.</p></div></section>';
        }
        ob_start();
        ?>
        <header class="cmn-school-header">
            <h2>Support</h2>
            <p>Manage support tickets and reply to users.</p>
        </header>
        <div class="cmn-support-hub-wrap" data-support-root data-support-mode="admin">
            <div class="cmn-support-dashboard" data-support-dashboard>
                <button class="cmn-support-tile is-active" type="button" data-support-tile="open">
                    <span>Open tickets</span>
                    <strong data-support-count="open">0</strong>
                </button>
                <button class="cmn-support-tile" type="button" data-support-tile="closed">
                    <span>Closed tickets</span>
                    <strong data-support-count="closed">0</strong>
                </button>
                <button class="cmn-support-tile" type="button" data-support-tile="needs_feedback">
                    <span>Needs feedback</span>
                    <strong data-support-count="needs_feedback">0</strong>
                </button>
                <button class="cmn-support-tile" type="button" data-support-tile="feedback_insights">
                    <span>Recent feedback</span>
                    <strong data-support-count="feedback_avg">0.0/5</strong>
                </button>
            </div>
            <div class="cmn-support-hub" data-support-shell>
            <div class="cmn-support-sidebar">
                <div class="cmn-support-filters">
                    <button class="cmn-ghost is-active" type="button" data-support-filter="active">New + Open</button>
                    <button class="cmn-ghost" type="button" data-support-filter="new">New</button>
                    <button class="cmn-ghost" type="button" data-support-filter="open">Open</button>
                    <button class="cmn-ghost" type="button" data-support-filter="closed">Closed</button>
                    <button class="cmn-ghost" type="button" data-support-filter="needs_feedback">Needs feedback</button>
                </div>
                <div class="cmn-support-list" data-support-list>
                    <div class="cmn-muted">Loading tickets...</div>
                </div>
            </div>
            <div class="cmn-support-thread" data-support-thread>
                <div class="cmn-support-thread-header">
                    <div>
                        <strong data-support-thread-title>Support Hub</strong>
                        <div class="cmn-muted" data-support-thread-ref>Select a ticket to view the conversation.</div>
                        <div class="cmn-status-chip is-pending" data-support-feedback-badge hidden>Feedback received</div>
                    </div>
                    <div class="cmn-support-thread-actions">
                        <button class="cmn-ghost" type="button" data-support-close-ticket disabled>Close Ticket</button>
                        <button class="cmn-ghost" type="button" data-support-reopen-ticket disabled>Reopen</button>
                        <button class="cmn-ghost" type="button" data-support-save-transcript disabled>Save Transcript</button>
                        <button class="cmn-ghost" type="button" data-support-email-transcript disabled>Send Transcript to Email</button>
                    </div>
                </div>
                <div class="cmn-support-messages" data-support-messages></div>
                <form class="cmn-support-reply" data-support-reply>
                    <textarea name="message" rows="4" placeholder="Type your reply..." required></textarea>
                    <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.txt">
                    <button class="cmn-primary" type="submit">Send Reply</button>
                </form>
                <div class="cmn-support-feedback" data-support-feedback></div>
            </div>
            </div>
            <div class="cmn-support-insights-modal" data-support-insights-modal hidden>
                <div class="cmn-support-insights-modal__overlay" data-support-insights-close></div>
                <div class="cmn-support-insights-modal__card" role="dialog" aria-modal="true" aria-label="Feedback insights">
                    <div class="cmn-support-insights-header">
                        <h3>Feedback insights</h3>
                        <button class="cmn-ghost cmn-btn-mini" type="button" data-support-insights-close>Close</button>
                    </div>
                    <div class="cmn-support-insights-filters">
                        <button class="cmn-ghost is-active" type="button" data-support-insights-filter="recent">Recent</button>
                        <button class="cmn-ghost" type="button" data-support-insights-filter="lowest">Lowest scores</button>
                        <button class="cmn-ghost" type="button" data-support-insights-filter="unresolved">Resolved = No</button>
                        <button class="cmn-ghost" type="button" data-support-insights-filter="overall_lte_3">Overall ≤ 3</button>
                    </div>
                    <div class="cmn-support-insights-list" data-support-insights-list>
                        <div class="cmn-muted">No feedback yet.</div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $inner = ob_get_clean();
        return $this->render_staff_shell('support', $inner);
    }

    public function render_staff_contacts_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        if (!$this->is_staff_user()) {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>This section is available to staff only.</p></div></section>';
        }
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $contact_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : 0;
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        $message = isset($_GET['cmn_contact_msg']) ? sanitize_text_field(wp_unslash($_GET['cmn_contact_msg'])) : '';
        $import_token = isset($_GET['cmn_contact_import_token']) ? sanitize_text_field($_GET['cmn_contact_import_token']) : '';
        $import_data = $import_token ? get_transient('cmn_contact_import_' . $import_token) : null;
        $import_headers = $import_data['headers'] ?? [];

        $contact_query_args = [
            'post_type' => 'cmn_contact',
            'posts_per_page' => 100,
            's' => $search,
        ];
        $user_id = get_current_user_id();
        $assigned_domains = [];
        if ($this->is_account_manager_user($user_id) && !$this->is_admin_user($user_id) && !$this->is_staff_role($user_id)) {
            $assigned_school_ids = $this->get_assigned_school_ids_for_account_manager($user_id);
            if ($assigned_school_ids) {
                foreach ($assigned_school_ids as $school_id) {
                    $domain = get_post_meta($school_id, 'cmn_school_email_domain', true);
                    if ($domain) {
                        $assigned_domains[] = $domain;
                    }
                }
            }
            $contact_ids = [];
            foreach (array_unique($assigned_domains) as $domain) {
                foreach ($this->get_school_contact_ids($domain) as $contact_id) {
                    $contact_ids[] = (int) $contact_id;
                }
            }
            $contact_ids = array_values(array_unique(array_filter($contact_ids)));
            if (!$contact_ids) {
                $contact_ids = [0];
            }
            $contact_query_args['post__in'] = $contact_ids;
        }
        $contacts_query = new WP_Query($contact_query_args);

        $edit_contact = null;
        if ($contact_id) {
            $edit_contact = get_post($contact_id);
            if (!$edit_contact || $edit_contact->post_type !== 'cmn_contact') {
                $edit_contact = null;
            }
            if ($edit_contact && $assigned_domains) {
                $links = $this->get_contact_school_links($edit_contact->ID);
                $contact_domains = [];
                foreach ($links as $link) {
                    if (!empty($link['school_email_domain'])) {
                        $contact_domains[] = $link['school_email_domain'];
                    }
                }
                if (!array_intersect($assigned_domains, $contact_domains)) {
                    $edit_contact = null;
                }
            }
        }
        $linked_schools = $edit_contact ? $this->get_contact_school_links($edit_contact->ID) : [];
        $primary_school_domain = $edit_contact ? $this->get_contact_primary_school_id($edit_contact->ID) : '';
        $linked_domains = [];
        if ($linked_schools) {
            foreach ($linked_schools as $link) {
                if (!empty($link['school_email_domain'])) {
                    $linked_domains[] = $link['school_email_domain'];
                }
            }
        }

        ob_start();
        ?>
        <header class="cmn-school-header">
            <div class="cmn-header-row">
                <div>
                    <h2>Contacts</h2>
                    <p>Manage contacts and assign them to schools.</p>
                </div>
            </div>
        </header>
        <?php if ($message) : ?>
            <div class="cmn-panel-card"><strong><?php echo esc_html($message); ?></strong></div>
        <?php endif; ?>
        <div class="cmn-portal-grid">
            <div class="cmn-dashboard-card">
                <h3><?php echo $edit_contact ? 'Edit Contact' : 'Add Contact'; ?></h3>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-form">
                    <?php if ($edit_contact) : ?>
                        <?php wp_nonce_field('cmn_update_contact', 'cmn_update_contact_nonce'); ?>
                        <input type="hidden" name="action" value="cmn_update_contact">
                        <input type="hidden" name="cmn_contact_id" value="<?php echo esc_attr($edit_contact->ID); ?>">
                    <?php else : ?>
                        <?php wp_nonce_field('cmn_add_contact', 'cmn_add_contact_nonce'); ?>
                        <input type="hidden" name="action" value="cmn_add_contact">
                    <?php endif; ?>
                    <label>Name *
                        <input type="text" name="cmn_contact_name" value="<?php echo esc_attr($edit_contact ? get_post_meta($edit_contact->ID, 'cmn_contact_name', true) : ''); ?>" required>
                    </label>
                    <label>Email
                        <input type="email" name="cmn_contact_email" value="<?php echo esc_attr($edit_contact ? get_post_meta($edit_contact->ID, 'cmn_contact_email', true) : ''); ?>">
                    </label>
                    <label>Phone
                        <input type="text" name="cmn_contact_phone" value="<?php echo esc_attr($edit_contact ? get_post_meta($edit_contact->ID, 'cmn_contact_phone', true) : ''); ?>">
                    </label>
                    <label>Role / Title
                        <input type="text" name="cmn_contact_role" value="<?php echo esc_attr($edit_contact ? get_post_meta($edit_contact->ID, 'cmn_contact_role', true) : ''); ?>">
                    </label>
                    <label>Primary School (optional)
                        <select name="cmn_contact_primary_school_domain">
                            <option value="">Unassigned</option>
                            <?php foreach ($this->get_school_options() as $school) : ?>
                                <?php
                                $domain = get_post_meta($school->ID, 'cmn_school_email_domain', true);
                                $school_code = get_post_meta($school->ID, 'cmn_school_id', true);
                                if (!$domain) {
                                    continue;
                                }
                                $selected = $primary_school_domain === $domain ? ' selected' : '';
                                ?>
                                <option value="<?php echo esc_attr($domain); ?>"<?php echo $selected; ?>>
                                    <?php echo esc_html($school->post_title . ($school_code ? ' · ' . $school_code : '')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Additional Schools (optional)
                        <div class="cmn-checkbox-grid">
                            <?php foreach ($this->get_school_options() as $school) : ?>
                                <?php
                                $domain = get_post_meta($school->ID, 'cmn_school_email_domain', true);
                                $school_code = get_post_meta($school->ID, 'cmn_school_id', true);
                                if (!$domain) {
                                    continue;
                                }
                                $checked = in_array($domain, $linked_domains, true) && $domain !== $primary_school_domain ? ' checked' : '';
                                ?>
                                <label class="cmn-checkbox-row">
                                    <input type="checkbox" name="cmn_contact_school_domains[]" value="<?php echo esc_attr($domain); ?>"<?php echo $checked; ?>>
                                    <span><?php echo esc_html($school->post_title . ($school_code ? ' · ' . $school_code : '')); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </label>
                    <button class="cmn-ghost" type="submit"><?php echo $edit_contact ? 'Update Contact' : 'Add Contact'; ?></button>
                </form>
            </div>
            <div class="cmn-dashboard-card">
                <h3>Bulk Upload</h3>
                <?php if ($import_data) : ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-form">
                        <?php wp_nonce_field('cmn_import_contacts', 'cmn_import_contacts_nonce'); ?>
                        <input type="hidden" name="action" value="cmn_import_contacts">
                        <input type="hidden" name="cmn_import_step" value="map">
                        <input type="hidden" name="cmn_contact_import_token" value="<?php echo esc_attr($import_token); ?>">
                        <p class="cmn-muted">Map columns to the contact fields. Contacts are created even without a school assignment.</p>
                        <div class="cmn-form-grid">
                            <?php
                            $required_fields = [
                                'cmn_contact_name' => 'Contact Name *',
                            ];
                            $optional_fields = [
                                'cmn_contact_email' => 'Contact Email',
                                'cmn_contact_phone' => 'Contact Phone',
                                'cmn_contact_role' => 'Contact Role / Title',
                                'cmn_contact_school_id' => 'Assign School (School ID)',
                                'cmn_contact_school_email' => 'Assign School (School Email)',
                                'cmn_contact_school_name' => 'Assign School (School Name)',
                            ];
                            $options = [];
                            foreach ($import_headers as $idx => $label) {
                                $name = trim((string) $label);
                                if ($name === '') {
                                    $name = 'Column ' . ($idx + 1);
                                }
                                $options[] = ['value' => (string) $idx, 'label' => $name];
                            }
                            $render_select = function ($name, $label, $required = false) use ($options) {
                                echo '<label>' . esc_html($label);
                                echo '<select name="cmn_contact_map[' . esc_attr($name) . ']"' . ($required ? ' required' : '') . '>';
                                if (!$required) {
                                    echo '<option value="">Skip</option>';
                                }
                                foreach ($options as $opt) {
                                    echo '<option value="' . esc_attr($opt['value']) . '">' . esc_html($opt['label']) . '</option>';
                                }
                                echo '</select>';
                                echo '</label>';
                            };
                            foreach ($required_fields as $key => $label) {
                                $render_select($key, $label, true);
                            }
                            foreach ($optional_fields as $key => $label) {
                                $render_select($key, $label, false);
                            }
                            ?>
                        </div>
                        <div class="cmn-form-actions">
                            <button class="cmn-ghost" type="submit">Run Import</button>
                            <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['view' => 'contacts'], $portal_url)); ?>">Cancel</a>
                        </div>
                    </form>
                <?php else : ?>
                    <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-form">
                        <?php wp_nonce_field('cmn_import_contacts', 'cmn_import_contacts_nonce'); ?>
                        <input type="hidden" name="action" value="cmn_import_contacts">
                        <input type="hidden" name="cmn_import_step" value="upload">
                        <label>Spreadsheet File
                            <input type="file" name="cmn_contacts_file" accept=".csv,.xlsx" required>
                        </label>
                        <p class="cmn-muted">Upload a CSV or .xlsx file. You will map columns next.</p>
                        <button class="cmn-ghost" type="submit">Upload File</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <div class="cmn-dashboard-card">
            <form method="get" class="cmn-filters">
                <input type="hidden" name="view" value="contacts">
                <input type="search" name="s" placeholder="Search contacts..." value="<?php echo esc_attr($search); ?>">
                <button class="cmn-ghost" type="submit">Filter</button>
            </form>
            <table class="cmn-approval-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>School</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($contacts_query->have_posts()) : ?>
                    <?php while ($contacts_query->have_posts()) : $contacts_query->the_post(); ?>
                        <?php
                        $cid = get_the_ID();
                        $links = $this->get_contact_school_links($cid);
                        $school_names = [];
                        if ($links) {
                            foreach ($links as $link) {
                                $name = $this->get_school_name_by_domain($link['school_email_domain'] ?? '');
                                if ($name) {
                                    $school_names[] = $name;
                                }
                            }
                        }
                        $school_name = $school_names ? implode(', ', $school_names) : 'Unassigned';
                        ?>
                        <tr>
                            <td><?php echo esc_html(get_post_meta($cid, 'cmn_contact_name', true)); ?></td>
                            <td><?php echo esc_html(get_post_meta($cid, 'cmn_contact_email', true)); ?></td>
                            <td><?php echo esc_html(get_post_meta($cid, 'cmn_contact_phone', true)); ?></td>
                            <td><?php echo esc_html(get_post_meta($cid, 'cmn_contact_role', true)); ?></td>
                            <td><?php echo esc_html($school_name); ?></td>
                            <td>
                                <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['view' => 'contacts', 'contact_id' => $cid], $portal_url)); ?>">Edit</a>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                    <?php wp_nonce_field('cmn_delete_contact', 'cmn_delete_contact_nonce'); ?>
                                    <input type="hidden" name="action" value="cmn_delete_contact">
                                    <input type="hidden" name="cmn_contact_id" value="<?php echo esc_attr($cid); ?>">
                                    <button class="cmn-ghost" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <tr><td colspan="6">No contacts found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        $inner = ob_get_clean();
        return $this->render_staff_shell('contacts', $inner);
    }

    public function render_staff_staff_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        if (!$this->can_manage_staff_users()) {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>This section is available to admin staff only.</p></div></section>';
        }
        $staff_users = get_users([
            'role__in' => ['cmn_admin', 'cmn_staff', 'cmn_account_manager'],
        ]);
        ob_start();
        ?>
        <header class="cmn-school-header">
            <h2>Staff</h2>
            <p>Add staff members and assign their roles.</p>
        </header>
        <div class="cmn-portal-grid cmn-staff-page-grid">
            <div class="cmn-dashboard-card cmn-staff-add-card">
                <h3>Add Staff Member</h3>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-form" data-staff-add-form>
                    <?php wp_nonce_field('cmn_add_staff', 'cmn_add_staff_nonce'); ?>
                    <input type="hidden" name="action" value="cmn_add_staff">
                    <label>Name
                        <input type="text" name="cmn_staff_name" required>
                    </label>
                    <label>Username (optional)
                        <input type="text" name="cmn_staff_username" placeholder="Auto-generate if blank">
                    </label>
                    <p class="cmn-muted cmn-staff-hint">Leave blank to auto-generate from email.</p>
                    <label>Email
                        <input type="email" name="cmn_staff_email" required>
                    </label>
                    <div class="cmn-staff-inline-controls">
                        <label class="cmn-staff-role-field">Role
                            <select name="cmn_staff_role">
                                <option value="cmn_staff">Staff</option>
                                <option value="cmn_account_manager">Account Manager</option>
                                <option value="cmn_admin">Admin</option>
                            </select>
                        </label>
                        <button class="cmn-ghost" type="submit">Add Staff</button>
                    </div>
                    <div class="cmn-muted cmn-staff-form-msg" data-staff-form-msg></div>
                </form>
            </div>
            <div class="cmn-dashboard-card cmn-staff-table-card">
                <h3>Current Staff</h3>
                <?php if ($staff_users) : ?>
                    <div class="cmn-staff-table-wrap">
                    <table class="cmn-approval-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($staff_users as $staff) : ?>
                            <?php
                            $is_deactivated = get_user_meta($staff->ID, 'cmn_deactivated', true) ? true : false;
                            $role_label = implode(', ', $staff->roles);
                            ?>
                            <tr>
                                <td class="cmn-staff-name"><?php echo esc_html($staff->display_name); ?></td>
                                <td class="cmn-staff-username"><?php echo esc_html($staff->user_login); ?></td>
                                <td class="cmn-staff-email"><?php echo esc_html($staff->user_email); ?></td>
                                <td class="cmn-staff-role"><?php echo esc_html($role_label); ?></td>
                                <td class="cmn-staff-status">
                                    <span class="cmn-status-chip <?php echo $is_deactivated ? 'is-declined' : 'is-approved'; ?>">
                                        <?php echo $is_deactivated ? 'Deactivated' : 'Active'; ?>
                                    </span>
                                </td>
                                <td class="cmn-staff-actions">
                                    <button class="cmn-ghost cmn-btn-mini" type="button"
                                            data-staff-edit
                                            data-staff-id="<?php echo esc_attr($staff->ID); ?>"
                                            data-staff-name="<?php echo esc_attr($staff->display_name); ?>"
                                            data-staff-username="<?php echo esc_attr($staff->user_login); ?>"
                                            data-staff-email="<?php echo esc_attr($staff->user_email); ?>"
                                            data-staff-role="<?php echo esc_attr($staff->roles ? $staff->roles[0] : 'cmn_staff'); ?>">
                                        Edit
                                    </button>
                                    <button class="cmn-ghost cmn-btn-mini" type="button"
                                            data-staff-reset
                                            data-staff-id="<?php echo esc_attr($staff->ID); ?>">
                                        Reset Password
                                    </button>
                                    <button class="cmn-ghost cmn-btn-mini" type="button"
                                            data-staff-toggle
                                            data-staff-id="<?php echo esc_attr($staff->ID); ?>"
                                            data-staff-active="<?php echo $is_deactivated ? '0' : '1'; ?>">
                                        <?php echo $is_deactivated ? 'Reactivate' : 'Deactivate'; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php else : ?>
                    <p class="cmn-muted">No staff users found.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="cmn-modal" data-staff-modal>
            <div class="cmn-modal-content">
                <div class="cmn-modal-header">
                    <h3>Edit Staff Member</h3>
                    <button class="cmn-ghost cmn-btn-mini" type="button" data-staff-modal-close>Close</button>
                </div>
                <form class="cmn-form" data-staff-edit-form>
                    <input type="hidden" name="staff_id" value="">
                    <label>Name
                        <input type="text" name="staff_name" required>
                    </label>
                    <label>Username (read-only)
                        <input type="text" name="staff_username" readonly>
                    </label>
                    <label>Email
                        <input type="email" name="staff_email" required>
                    </label>
                    <label>Role
                        <select name="staff_role">
                            <option value="cmn_staff">Staff</option>
                            <option value="cmn_account_manager">Account Manager</option>
                            <option value="cmn_admin">Admin</option>
                        </select>
                    </label>
                    <button class="cmn-primary" type="submit">Save Changes</button>
                    <div class="cmn-muted cmn-staff-form-msg" data-staff-edit-msg></div>
                </form>
            </div>
        </div>
        <?php
        $inner = ob_get_clean();
        return $this->render_staff_shell('staff', $inner);
    }

    public function render_staff_settings_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        if (!$this->is_staff_user()) {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>Settings are available to staff only.</p></div></section>';
        }
        $can_manage_admin_tools = $this->is_admin_user();
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        $candidate_preview = add_query_arg(['as' => 'candidate'], $portal_url);
        $school_preview = add_query_arg(['as' => 'school'], $portal_url);
        $candidate_reg_page = get_page_by_title('Candidate Registration');
        $school_reg_page = get_page_by_title('School Registration');
        $candidate_reg_url = $candidate_reg_page ? get_permalink($candidate_reg_page) : home_url('/candidate-registration');
        $school_reg_url = $school_reg_page ? get_permalink($school_reg_page) : home_url('/school-registration');
        $candidate_admin_url = admin_url('post-new.php?post_type=cmn_candidate');
        $school_admin_url = admin_url('post-new.php?post_type=cmn_school');
        $upload_limit_warning = $this->get_candidate_doc_host_limit_warning();

        ob_start();
        ?>
        <header class="cmn-school-header">
            <h2>Settings</h2>
            <p>Personal colour scheme and admin tools.</p>
        </header>
        <div class="cmn-portal-grid">
            <div class="cmn-dashboard-card" data-theme-settings>
                <h3>Colour Scheme</h3>
                <p class="cmn-muted">Choose how the portal looks for your account.</p>
                <label>Theme
                    <select name="cmn_theme_scheme" data-theme-select>
                        <?php foreach ($this->get_theme_scheme_choices() as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="cmn-settings-actions">
                    <button class="cmn-primary" type="button" data-theme-save>Save scheme</button>
                    <span class="cmn-muted" data-theme-message></span>
                </div>
            </div>
            <?php if ($can_manage_admin_tools) : ?>
            <?php if ($upload_limit_warning !== '') : ?>
            <div class="cmn-dashboard-card">
                <h3>Upload Limit Warning</h3>
                <p><?php echo esc_html($upload_limit_warning); ?></p>
            </div>
            <?php endif; ?>
            <div class="cmn-dashboard-card">
                <h3>Preview Dashboards</h3>
                <p>View the portal as a candidate or school.</p>
                <div class="cmn-actions-grid">
                    <a class="cmn-primary cmn-button-link" href="<?php echo esc_url($candidate_preview); ?>">View as Candidate</a>
                    <a class="cmn-ghost" href="<?php echo esc_url($school_preview); ?>">View as School</a>
                </div>
            </div>
            <div class="cmn-dashboard-card">
                <h3>Create Records</h3>
                <p>Add a school or candidate directly.</p>
                <div class="cmn-actions-grid">
                    <a class="cmn-ghost" href="<?php echo esc_url($school_admin_url); ?>">Add School (Admin)</a>
                    <a class="cmn-ghost" href="<?php echo esc_url($candidate_admin_url); ?>">Add Candidate (Admin)</a>
                </div>
            </div>
            <div class="cmn-dashboard-card">
                <h3>Registration Links</h3>
                <p>Send external application links.</p>
                <div class="cmn-actions-grid">
                    <a class="cmn-ghost" href="<?php echo esc_url($school_reg_url); ?>">School Registration</a>
                    <a class="cmn-ghost" href="<?php echo esc_url($candidate_reg_url); ?>">Candidate Registration</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php if ($can_manage_admin_tools) : ?>
        <div class="cmn-dashboard-card cmn-email-test-console" data-email-test-console>
            <h3>Email Testing Console</h3>
            <p>Send test emails for each category to a specified address.</p>
            <div class="cmn-form-grid">
                <label>Email Type
                    <select name="cmn_email_test_type" data-email-test-type>
                        <option value="candidate">Candidate</option>
                        <option value="school">School</option>
                    </select>
                </label>
                <label>Send test emails to
                    <input type="email" name="cmn_email_test_address" placeholder="name@example.com" data-email-test-address required>
                </label>
            </div>
            <button class="cmn-primary cmn-email-test-submit" type="button" data-email-test-submit>Send Test Emails</button>
            <div class="cmn-email-test-results" data-email-test-results></div>
        </div>
        <?php endif; ?>
        <?php
        $inner = ob_get_clean();
        return $this->render_staff_shell('settings', $inner);
    }

    private function render_frontend_school_profile($school_id) {
        $school = get_post($school_id);
        if (!$school || $school->post_type !== 'cmn_school') {
            return '<div class="cmn-panel-card"><p>School not found.</p></div>';
        }
        $meta = function ($key) use ($school_id) {
            return get_post_meta($school_id, $key, true);
        };
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        $school_code = $meta('cmn_school_id');
        if ($school_code === '') {
            $school_code = $this->upsert_school_index($school_id);
        }
        $redirect_url = add_query_arg(['view' => 'schools', 'school_id' => $school_code], $portal_url);
        $pipeline_stage = $meta('cmn_pipeline_stage') ?: 'new_lead';
        $school_domain = $this->get_email_domain($meta('cmn_email'));
        if ($school_domain === '') {
            $school_domain = $meta('cmn_school_email_domain');
        }
        $contacts = $this->get_school_contacts_by_domain($school_domain);
        $open_tasks = $this->get_school_tasks($school_code, 5);
        $activities = $this->get_school_activities($school_code, 20);
        $convert_msg = isset($_GET['cmn_convert_msg']) ? sanitize_text_field(wp_unslash($_GET['cmn_convert_msg'])) : '';
        ob_start();
        ?>
        <header class="cmn-school-header">
            <h2><?php echo esc_html($school->post_title); ?></h2>
            <p>School profile</p>
        </header>
        <?php if ($convert_msg) : ?>
            <div class="cmn-panel-card"><strong><?php echo esc_html($convert_msg); ?></strong></div>
        <?php endif; ?>
        <div class="cmn-profile-grid">
            <div class="cmn-panel-card">
                <h3>Details</h3>
                <div class="cmn-meta-grid">
                    <div><strong>Status:</strong> <?php echo esc_html($meta('cmn_status')); ?></div>
                    <div><strong>Pipeline Stage:</strong> <?php echo esc_html(ucwords(str_replace('_', ' ', $pipeline_stage))); ?></div>
                    <div><strong>School ID:</strong> <?php echo esc_html($meta('cmn_school_id')); ?></div>
                    <div><strong>Location:</strong> <?php echo esc_html($meta('cmn_location')); ?></div>
                    <div><strong>Phone:</strong> <?php echo esc_html($meta('cmn_phone')); ?></div>
                    <div><strong>Switchboard:</strong> <?php echo esc_html($meta('cmn_switchboard')); ?></div>
                    <div><strong>Email:</strong> <?php echo esc_html($meta('cmn_email')); ?></div>
                    <div><strong>Website:</strong> <?php echo esc_html($meta('cmn_website')); ?></div>
                    <div><strong>Account Manager:</strong> <?php echo esc_html($meta('cmn_account_manager')); ?></div>
                    <div><strong>Cover Manager:</strong> <?php echo esc_html($meta('cmn_cover_manager')); ?></div>
                    <div><strong>Cover Manager Email:</strong> <?php echo esc_html($meta('cmn_cover_manager_email')); ?></div>
                    <div><strong>Spoke to CM:</strong> <?php echo esc_html($meta('cmn_spoke_to_cm')); ?></div>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-status-form">
                    <?php wp_nonce_field('cmn_update_status', 'cmn_update_status_nonce'); ?>
                    <input type="hidden" name="action" value="cmn_update_status">
                    <input type="hidden" name="cmn_entity_type" value="school">
                    <input type="hidden" name="cmn_entity_id" value="<?php echo esc_attr($school_code); ?>">
                    <input type="hidden" name="cmn_redirect" value="<?php echo esc_url($redirect_url); ?>">
            <label>Update Status
                <select name="cmn_status">
                    <?php foreach (['lead', 'client', 'pending', 'approved', 'rejected'] as $opt) : ?>
                        <option value="<?php echo esc_attr($opt); ?>"<?php echo $meta('cmn_status') === $opt ? ' selected' : ''; ?>><?php echo esc_html(ucfirst($opt)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Pipeline Stage
                <select name="cmn_pipeline_stage">
                    <?php foreach (['new_lead', 'contacted', 'demo', 'negotiation', 'won', 'lost'] as $opt) : ?>
                        <option value="<?php echo esc_attr($opt); ?>"<?php echo $pipeline_stage === $opt ? ' selected' : ''; ?>><?php echo esc_html(ucwords(str_replace('_', ' ', $opt))); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
                    <button class="cmn-ghost" type="submit">Save</button>
                </form>
                <?php if ($meta('cmn_status') === 'lead') : ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-status-form" data-confirm="Convert this lead to a client and email the profile link?">
                        <?php wp_nonce_field('cmn_convert_client', 'cmn_convert_client_nonce'); ?>
                        <input type="hidden" name="action" value="cmn_convert_client">
                        <input type="hidden" name="cmn_school_domain" value="<?php echo esc_attr($school_domain); ?>">
                        <input type="hidden" name="cmn_redirect" value="<?php echo esc_url($redirect_url); ?>">
                        <button class="cmn-primary" type="submit">Convert to Client</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="cmn-panel-card">
                <h3>Assign Account Manager</h3>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-status-form">
                    <?php wp_nonce_field('cmn_assign_account_manager', 'cmn_assign_account_manager_nonce'); ?>
                    <input type="hidden" name="action" value="cmn_assign_account_manager">
                    <input type="hidden" name="cmn_school_id" value="<?php echo esc_attr($school_id); ?>">
                    <input type="hidden" name="cmn_redirect" value="<?php echo esc_url($redirect_url); ?>">
                    <label>Account Manager
                        <select name="cmn_account_manager_user">
                            <option value="">Unassigned</option>
                            <?php foreach ($this->get_account_manager_users() as $manager) : ?>
                                <option value="<?php echo esc_attr($manager->ID); ?>"<?php echo (int) $meta('cmn_account_manager_user') === (int) $manager->ID ? ' selected' : ''; ?>>
                                    <?php echo esc_html($manager->display_name . ' (' . $manager->user_email . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="cmn-ghost" type="submit">Assign</button>
                </form>
            </div>
            <div class="cmn-panel-card">
                <h3>Contacts</h3>
                <div class="cmn-meta-grid">
                    <div><strong>Primary Contact:</strong> <?php echo esc_html($meta('cmn_contact1')); ?></div>
                    <div><strong>Role:</strong> <?php echo esc_html($meta('cmn_contact_role')); ?></div>
                    <div><strong>Contact Email:</strong> <?php echo esc_html($meta('cmn_contact1_email') ?: $meta('cmn_email')); ?></div>
                    <div><strong>Contact 2:</strong> <?php echo esc_html($meta('cmn_contact2')); ?></div>
                    <div><strong>Contact 2 Email:</strong> <?php echo esc_html($meta('cmn_contact2_email')); ?></div>
                    <div><strong>Contact 3:</strong> <?php echo esc_html($meta('cmn_contact3')); ?></div>
                    <div><strong>Contact 3 Email:</strong> <?php echo esc_html($meta('cmn_contact3_email')); ?></div>
                </div>
            </div>
            <div class="cmn-panel-card">
                <h3>Assigned Contacts</h3>
                <?php if ($contacts) : ?>
                    <ul class="cmn-activity-list">
                        <?php foreach ($contacts as $contact) : ?>
                            <li>
                                <strong><?php echo esc_html(get_post_meta($contact->ID, 'cmn_contact_name', true)); ?></strong>
                                <div class="cmn-muted"><?php echo esc_html(get_post_meta($contact->ID, 'cmn_contact_email', true)); ?> · <?php echo esc_html(get_post_meta($contact->ID, 'cmn_contact_phone', true)); ?></div>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                    <?php wp_nonce_field('cmn_unassign_contact', 'cmn_unassign_contact_nonce'); ?>
                                    <input type="hidden" name="action" value="cmn_unassign_contact">
                                    <input type="hidden" name="cmn_contact_id" value="<?php echo esc_attr($contact->ID); ?>">
                                    <input type="hidden" name="cmn_school_domain" value="<?php echo esc_attr($school_domain); ?>">
                                    <input type="hidden" name="cmn_redirect" value="<?php echo esc_url($redirect_url); ?>">
                                    <button class="cmn-ghost" type="submit">Unassign</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p class="cmn-muted">No contacts assigned yet.</p>
                <?php endif; ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-status-form">
                    <?php wp_nonce_field('cmn_assign_contact', 'cmn_assign_contact_nonce'); ?>
                    <input type="hidden" name="action" value="cmn_assign_contact_to_school">
                    <input type="hidden" name="cmn_school_domain" value="<?php echo esc_attr($school_domain); ?>">
                    <input type="hidden" name="cmn_redirect" value="<?php echo esc_url($redirect_url); ?>">
                    <label>Assign Existing Contact
                        <select name="cmn_contact_id">
                            <option value="">Select contact</option>
                            <?php foreach ($this->get_contacts_not_linked_to_school($school_domain) as $contact) : ?>
                                <option value="<?php echo esc_attr($contact->ID); ?>"><?php echo esc_html(get_post_meta($contact->ID, 'cmn_contact_name', true)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="cmn-ghost" type="submit">Assign Contact</button>
                </form>
            </div>
            <div class="cmn-panel-card cmn-panel-card-wide">
                <h3>Address</h3>
                <div class="cmn-meta-grid">
                    <div><strong>House / Number:</strong> <?php echo esc_html($meta('cmn_house_number')); ?></div>
                    <div><strong>Address Line 1:</strong> <?php echo esc_html($meta('cmn_address_line1')); ?></div>
                    <div><strong>Address Line 2:</strong> <?php echo esc_html($meta('cmn_address_line2')); ?></div>
                    <div><strong>Address Line 3:</strong> <?php echo esc_html($meta('cmn_address_line3')); ?></div>
                    <div><strong>Town:</strong> <?php echo esc_html($meta('cmn_town')); ?></div>
                    <div><strong>County:</strong> <?php echo esc_html($meta('cmn_county')); ?></div>
                    <div><strong>Postcode:</strong> <?php echo esc_html($meta('cmn_postcode')); ?></div>
                </div>
            </div>
            <div class="cmn-panel-card">
                <h3>Open Tasks</h3>
                <?php if ($open_tasks) : ?>
                    <div class="cmn-task-list">
                        <?php foreach ($open_tasks as $task) : ?>
                            <?php
                            $task_date = $task['due_date'] ?? '';
                            $task_label = $task_date ? date_i18n('M j, Y', strtotime($task_date)) : 'No date';
                            $is_overdue = $task_date && strtotime($task_date) < strtotime(date('Y-m-d'));
                            ?>
                            <div class="cmn-task-item<?php echo $is_overdue ? ' is-overdue' : ''; ?>">
                                <div>
                                    <strong><?php echo esc_html($task['subject'] ?? 'Task'); ?></strong>
                                    <span><?php echo esc_html($task['notes'] ? wp_trim_words($task['notes'], 8, '…') : 'Task'); ?></span>
                                </div>
                                <div class="cmn-task-meta"><?php echo esc_html($task_label); ?><?php echo $is_overdue ? ' · Overdue' : ''; ?></div>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                    <?php wp_nonce_field('cmn_complete_activity', 'cmn_complete_activity_nonce'); ?>
                                    <input type="hidden" name="action" value="cmn_complete_activity">
                                    <input type="hidden" name="cmn_activity_id" value="<?php echo esc_attr($task['id'] ?? 0); ?>">
                                    <input type="hidden" name="cmn_redirect" value="<?php echo esc_url($redirect_url); ?>">
                                    <button class="cmn-ghost" type="submit">Mark Done</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="cmn-muted">No open tasks.</p>
                <?php endif; ?>
            </div>
            <div class="cmn-panel-card">
                <h3>Add Activity</h3>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-form cmn-activity-form">
                    <?php wp_nonce_field('cmn_add_activity', 'cmn_add_activity_nonce'); ?>
                    <input type="hidden" name="action" value="cmn_add_activity">
                    <input type="hidden" name="cmn_school_domain" value="<?php echo esc_attr($school_domain); ?>">
                    <input type="hidden" name="cmn_redirect" value="<?php echo esc_url($redirect_url); ?>">
                    <label>Type
                        <select name="cmn_activity_type">
                            <option value="note">Note</option>
                            <option value="task">Task</option>
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                        </select>
                    </label>
                    <label>Title
                        <input type="text" name="cmn_activity_title" required>
                    </label>
                    <label>Details
                        <textarea name="cmn_activity_content" rows="3"></textarea>
                    </label>
                    <label>Due / Date
                        <input type="date" name="cmn_activity_date">
                    </label>
                    <label>Duration (mins)
                        <input type="number" name="cmn_activity_duration" min="0">
                    </label>
                    <button class="cmn-ghost" type="submit">Add Activity</button>
                </form>
                <p class="cmn-muted">Tip: set Type = Task and add a date to surface it on the main dashboard.</p>
            </div>
            <div class="cmn-panel-card">
                <h3>Activity Timeline</h3>
                <?php if ($activities) : ?>
                    <ul class="cmn-activity-list">
                        <?php foreach ($activities as $activity) : ?>
                            <?php
                            $type = $activity['activity_type'] ?? '';
                            $date = $activity['due_date'] ?? '';
                            $content = $activity['notes'] ?? '';
                            $duration = $activity['duration_minutes'] ?? '';
                            $is_complete = !empty($activity['completed_at']);
                            ?>
                            <li>
                                <strong><?php echo esc_html(ucfirst($type ?: 'note')); ?>:</strong> <?php echo esc_html($activity['subject'] ?? 'Activity'); ?>
                                <?php if ($date) : ?>
                                    <span class="cmn-muted">(<?php echo esc_html($date); ?>)</span>
                                <?php endif; ?>
                                <?php if ($is_complete) : ?>
                                    <span class="cmn-pill">done</span>
                                <?php endif; ?>
                                <?php if ($duration) : ?>
                                    <span class="cmn-muted">Duration: <?php echo esc_html($duration); ?>m</span>
                                <?php endif; ?>
                                <?php if ($content) : ?>
                                    <div class="cmn-activity-content"><?php echo esc_html($content); ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p class="cmn-muted">No activity yet.</p>
                <?php endif; ?>
            </div>
        </div>
        <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['view' => 'schools'], $portal_url)); ?>">Back to Schools</a>
        <?php
        return ob_get_clean();
    }

    private function render_frontend_candidate_profile($candidate_id) {
        $candidate = get_post($candidate_id);
        if (!$candidate || $candidate->post_type !== 'cmn_candidate') {
            return '<div class="cmn-panel-card"><p>Candidate not found.</p></div>';
        }
        $meta = function ($key) use ($candidate_id) {
            return get_post_meta($candidate_id, $key, true);
        };
        ob_start();
        ?>
        <header class="cmn-school-header">
            <h2><?php echo esc_html($candidate->post_title); ?></h2>
            <p>Candidate profile</p>
        </header>
            <div class="cmn-profile-grid">
                <div class="cmn-panel-card">
                    <h3>Details</h3>
                    <div class="cmn-meta-grid">
                        <div><strong>Status:</strong> <?php echo esc_html($meta('cmn_status')); ?></div>
                        <div><strong>Location:</strong> <?php echo esc_html($meta('cmn_location')); ?></div>
                        <div><strong>Phone:</strong> <?php echo esc_html($meta('cmn_phone')); ?></div>
                        <div><strong>Email:</strong> <?php echo esc_html($meta('cmn_email')); ?></div>
                    </div>
                </div>
                <div class="cmn-panel-card">
                    <h3>Documents</h3>
                    <div class="cmn-meta-grid">
                        <div><strong>CV:</strong> <?php echo $meta('cmn_cv_file') ? 'Uploaded' : 'Not uploaded'; ?></div>
                        <div><strong>DBS:</strong> <?php echo $meta('cmn_dbs_file') ? 'Uploaded' : 'Not uploaded'; ?></div>
                    </div>
                </div>
            </div>
            <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['view' => 'candidates'], home_url('/portal'))); ?>">Back to Candidates</a>
        <?php
        return ob_get_clean();
    }

    private function get_candidate_availability_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_candidate_availability';
    }

    private function get_candidate_calendar_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_candidate_calendar_availability';
    }

    private function get_candidate_requests_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_candidate_requests';
    }

    private function get_booking_threads_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_booking_threads';
    }

    private function get_booking_messages_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_booking_messages';
    }

    private function get_booking_participants_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_booking_thread_participants';
    }

    private function get_booking_feedback_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_booking_feedback';
    }

    private function get_ready_responses_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_school_ready_responses';
    }

    private function get_booking_thread_columns() {
        static $columns = null;
        if ($columns !== null) {
            return $columns;
        }
        global $wpdb;
        $table = $this->get_booking_threads_table();
        $rows = (array) $wpdb->get_results("SHOW COLUMNS FROM {$table}", ARRAY_A);
        $columns = [];
        foreach ($rows as $row) {
            $field = isset($row['Field']) ? sanitize_key((string) $row['Field']) : '';
            if ($field !== '') {
                $columns[] = $field;
            }
        }
        return $columns;
    }

    private function get_booking_message_columns() {
        static $columns = null;
        if ($columns !== null) {
            return $columns;
        }
        global $wpdb;
        $table = $this->get_booking_messages_table();
        $rows = (array) $wpdb->get_results("SHOW COLUMNS FROM {$table}", ARRAY_A);
        $columns = [];
        foreach ($rows as $row) {
            $field = isset($row['Field']) ? sanitize_key((string) $row['Field']) : '';
            if ($field !== '') {
                $columns[] = $field;
            }
        }
        return $columns;
    }

    private function get_fallback_account_manager_user_id() {
        $user = get_user_by('email', 'j.norton@covermenow.co.uk');
        if (!$user) {
            $user = get_user_by('email', 'jay.norton@covermenow.co.uk');
        }
        return $user ? (int) $user->ID : 0;
    }

    private function get_request_account_manager_user_id($school_id, $request = []) {
        $from_request = isset($request['account_manager_user_id']) ? (int) $request['account_manager_user_id'] : 0;
        if ($from_request) {
            return $from_request;
        }
        $from_meta = (int) get_post_meta((int) $school_id, 'cmn_account_manager_user', true);
        if ($from_meta) {
            return $from_meta;
        }
        return $this->get_fallback_account_manager_user_id();
    }

    private function get_request_candidate_pay_rate($candidate_id, $school_id, $request = []) {
        if (!empty($request['candidate_pay_rate'])) {
            return (float) $request['candidate_pay_rate'];
        }
        return (float) $this->get_candidate_rate((int) $candidate_id, (int) $school_id);
    }

    private function get_request_school_charge_rate($candidate_pay_rate, $request = []) {
        if (!empty($request['school_charge_rate'])) {
            return (float) $request['school_charge_rate'];
        }
        return round((float) $candidate_pay_rate + 60, 2);
    }

    private function get_request_expires_at($request) {
        $expires_at = trim((string) ($request['expires_at'] ?? ''));
        if ($expires_at !== '') {
            return $expires_at;
        }
        $sent_at = trim((string) ($request['request_sent_at'] ?? $request['requested_at'] ?? ''));
        if ($sent_at === '') {
            return '';
        }
        return gmdate('Y-m-d H:i:s', strtotime($sent_at . ' +15 minutes'));
    }

    private function is_request_expired($request) {
        $status = strtolower((string) ($request['status'] ?? ''));
        if ($status !== 'requested' && $status !== 'pending') {
            return false;
        }
        $expires_at = $this->get_request_expires_at($request);
        if ($expires_at === '') {
            return false;
        }
        return strtotime($expires_at) <= current_time('timestamp', true);
    }

    private function maybe_mark_request_expired(&$request) {
        if (!$this->is_request_expired($request)) {
            return false;
        }
        global $wpdb;
        $table = $this->get_candidate_requests_table();
        $request_id = (int) ($request['id'] ?? 0);
        if (!$request_id) {
            return false;
        }
        $wpdb->update($table, [
            'status' => 'expired',
            'updated_at' => current_time('mysql'),
        ], [
            'id' => $request_id,
            'status' => $request['status'],
        ], ['%s', '%s'], ['%d', '%s']);
        $request['status'] = 'expired';
        return true;
    }

    private function create_or_get_booking_thread($booking_id, $thread_type = 'booking_details', $context = []) {
        $booking_id = (int) $booking_id;
        if (!$booking_id) {
            return 0;
        }
        global $wpdb;
        $table = $this->get_booking_threads_table();
        $columns = $this->get_booking_thread_columns();
        $thread_type = sanitize_key($thread_type);
        $existing = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE booking_id = %d AND thread_type = %s LIMIT 1",
            $booking_id,
            $thread_type
        ));
        if ($existing) {
            if (!empty($context) && !empty($columns)) {
                $updates = [];
                $formats = [];
                if (in_array('candidate_user_id', $columns, true) && isset($context['candidate_user_id'])) {
                    $updates['candidate_user_id'] = (int) $context['candidate_user_id'];
                    $formats[] = '%d';
                }
                if (in_array('school_user_id', $columns, true) && isset($context['school_user_id'])) {
                    $updates['school_user_id'] = (int) $context['school_user_id'];
                    $formats[] = '%d';
                }
                if (in_array('account_manager_user_id', $columns, true) && isset($context['account_manager_user_id'])) {
                    $updates['account_manager_user_id'] = (int) $context['account_manager_user_id'];
                    $formats[] = '%d';
                }
                if (in_array('status', $columns, true) && isset($context['status'])) {
                    $updates['status'] = sanitize_key((string) $context['status']);
                    $formats[] = '%s';
                }
                if ($updates) {
                    $wpdb->update($table, $updates, ['id' => $existing], $formats, ['%d']);
                }
            }
            return $existing;
        }
        $insert_data = [
            'booking_id' => $booking_id,
            'thread_type' => $thread_type,
            'created_at' => current_time('mysql'),
        ];
        $insert_formats = ['%d', '%s', '%s'];
        if (in_array('candidate_user_id', $columns, true)) {
            $insert_data['candidate_user_id'] = isset($context['candidate_user_id']) ? (int) $context['candidate_user_id'] : null;
            $insert_formats[] = '%d';
        }
        if (in_array('school_user_id', $columns, true)) {
            $insert_data['school_user_id'] = isset($context['school_user_id']) ? (int) $context['school_user_id'] : null;
            $insert_formats[] = '%d';
        }
        if (in_array('account_manager_user_id', $columns, true)) {
            $insert_data['account_manager_user_id'] = isset($context['account_manager_user_id']) ? (int) $context['account_manager_user_id'] : null;
            $insert_formats[] = '%d';
        }
        if (in_array('status', $columns, true)) {
            $insert_data['status'] = isset($context['status']) ? sanitize_key((string) $context['status']) : 'active';
            $insert_formats[] = '%s';
        }
        $wpdb->insert($table, $insert_data, $insert_formats);
        return (int) $wpdb->insert_id;
    }

    private function add_booking_thread_participant($thread_id, $user_id, $role_type) {
        $thread_id = (int) $thread_id;
        $user_id = (int) $user_id;
        if (!$thread_id || !$user_id) {
            return;
        }
        global $wpdb;
        $table = $this->get_booking_participants_table();
        $wpdb->replace($table, [
            'thread_id' => $thread_id,
            'user_id' => $user_id,
            'role_type' => sanitize_key($role_type),
            'created_at' => current_time('mysql'),
        ], ['%d', '%d', '%s', '%s']);
    }

    private function add_booking_thread_message($thread_id, $sender_user_id, $sender_role_type, $message, $attachment_ids = []) {
        $thread_id = (int) $thread_id;
        if (!$thread_id || trim((string) $message) === '') {
            return;
        }
        global $wpdb;
        $table = $this->get_booking_messages_table();
        $columns = $this->get_booking_message_columns();
        $insert_data = [
            'thread_id' => $thread_id,
            'sender_user_id' => (int) $sender_user_id ?: null,
            'sender_role_type' => sanitize_key($sender_role_type),
            'message' => wp_kses_post((string) $message),
            'created_at' => current_time('mysql'),
        ];
        $formats = ['%d', '%d', '%s', '%s', '%s'];
        if (in_array('attachment_ids', $columns, true)) {
            $safe_ids = [];
            if (is_array($attachment_ids)) {
                foreach ($attachment_ids as $attachment_id) {
                    $attachment_id = (int) $attachment_id;
                    if ($attachment_id > 0) {
                        $safe_ids[] = $attachment_id;
                    }
                }
            }
            $insert_data['attachment_ids'] = $safe_ids ? wp_json_encode(array_values(array_unique($safe_ids))) : null;
            $formats[] = '%s';
        }
        $wpdb->insert($table, $insert_data, $formats);
    }

    private function user_can_access_booking_thread($thread_id, $user_id = 0) {
        $thread_id = (int) $thread_id;
        $user_id = $user_id ? (int) $user_id : get_current_user_id();
        if (!$thread_id || !$user_id) {
            return false;
        }
        if ($this->is_staff_user()) {
            return true;
        }
        global $wpdb;
        $table = $this->get_booking_participants_table();
        $has = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE thread_id = %d AND user_id = %d LIMIT 1",
            $thread_id,
            $user_id
        ));
        return $has > 0;
    }

    private function get_booking_thread_by_booking($booking_id, $thread_type = 'booking_details') {
        global $wpdb;
        $table = $this->get_booking_threads_table();
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE booking_id = %d AND thread_type = %s LIMIT 1",
            (int) $booking_id,
            sanitize_key($thread_type)
        ), ARRAY_A);
    }

    private function get_booking_thread_messages($thread_id) {
        global $wpdb;
        $table = $this->get_booking_messages_table();
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE thread_id = %d ORDER BY created_at ASC, id ASC",
            (int) $thread_id
        ), ARRAY_A);
    }

    private function get_booking_message_attachments($message_row) {
        $ids = [];
        if (!empty($message_row['attachment_ids'])) {
            $decoded = json_decode((string) $message_row['attachment_ids'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $id) {
                    $id = (int) $id;
                    if ($id > 0) {
                        $ids[] = $id;
                    }
                }
            }
        }
        $attachments = [];
        foreach ($ids as $id) {
            $url = wp_get_attachment_url($id);
            if (!$url) {
                continue;
            }
            $path = get_attached_file($id);
            $attachments[] = [
                'id' => $id,
                'filename' => $path ? basename($path) : ('Attachment #' . $id),
                'url' => esc_url_raw($url),
            ];
        }
        return $attachments;
    }

    private function get_booking_service_date($booking_id) {
        $booking_id = (int) $booking_id;
        if (!$booking_id) {
            return '';
        }
        $date = trim((string) get_post_meta($booking_id, 'cmn_date', true));
        if ($date === '') {
            $date = trim((string) get_post_meta($booking_id, 'cmn_start_date', true));
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return '';
        }
        return $date;
    }

    private function get_booking_candidate_user_id($booking_id) {
        $booking_id = (int) $booking_id;
        if (!$booking_id) {
            return 0;
        }
        $candidate_id = (int) get_post_meta($booking_id, 'cmn_candidate_id', true);
        if (!$candidate_id) {
            return 0;
        }
        return (int) $this->get_candidate_user_id($candidate_id);
    }

    private function get_booking_school_user_id($booking_id) {
        $booking_id = (int) $booking_id;
        if (!$booking_id) {
            return 0;
        }
        $school_id = (int) get_post_meta($booking_id, 'cmn_school_id', true);
        $request_id = (int) get_post_meta($booking_id, 'cmn_request_id', true);
        if ($request_id) {
            $request = $this->get_candidate_request_by_id($request_id);
            if ($request) {
                return (int) $this->get_school_user_id_for_request($request, $school_id);
            }
        }
        if (!$school_id) {
            return 0;
        }
        $contact_email = $this->get_school_primary_contact_email($school_id);
        if (!$contact_email) {
            return 0;
        }
        $user = get_user_by('email', $contact_email);
        return $user ? (int) $user->ID : 0;
    }

    private function get_booking_feedback_role_for_user($user_id = 0) {
        $user_id = $user_id ? (int) $user_id : get_current_user_id();
        if (!$user_id) {
            return '';
        }
        if ($this->is_admin_user($user_id) || $this->is_account_manager_user($user_id) || $this->is_staff_role($user_id)) {
            return 'staff';
        }
        if ($this->is_candidate_user($user_id)) {
            return 'candidate';
        }
        if ($this->is_school_user($user_id)) {
            return 'school';
        }
        return '';
    }

    private function user_can_access_booking_feedback($booking_id, $user_id, $viewer_role = '') {
        $booking_id = (int) $booking_id;
        $user_id = (int) $user_id;
        if (!$booking_id || !$user_id) {
            return false;
        }
        $viewer_role = $viewer_role !== '' ? $viewer_role : $this->get_booking_feedback_role_for_user($user_id);
        if ($viewer_role === 'staff') {
            return true;
        }
        $details_thread = $this->get_booking_thread_by_booking($booking_id, 'booking_details');
        if ($details_thread && $this->user_can_access_booking_thread((int) ($details_thread['id'] ?? 0), $user_id)) {
            return true;
        }
        if ($viewer_role === 'candidate') {
            return (int) $this->get_booking_candidate_user_id($booking_id) === $user_id;
        }
        if ($viewer_role === 'school') {
            return (int) $this->get_booking_school_user_id($booking_id) === $user_id;
        }
        return false;
    }

    private function is_booking_feedback_eligible($booking_id, $status = '') {
        $booking_id = (int) $booking_id;
        if (!$booking_id) {
            return false;
        }
        $status = $status !== '' ? sanitize_key($status) : sanitize_key((string) get_post_meta($booking_id, 'cmn_status', true));
        if ($status === 'completed') {
            return true;
        }
        if (in_array($status, ['declined', 'cancelled', 'expired', 'candidate_declined'], true)) {
            return false;
        }
        $booking_date = $this->get_booking_service_date($booking_id);
        if ($booking_date === '') {
            return false;
        }
        $today = current_time('Y-m-d');
        return $booking_date < $today;
    }

    private function get_booking_chat_link_for_role($booking_id, $role = 'candidate') {
        $booking_id = (int) $booking_id;
        if (!$booking_id) {
            return $this->get_portal_base_url();
        }
        $params = [
            'cmn_booking_chat' => $booking_id,
            'cmn_thread_type' => 'booking_details',
            'cmn_feedback_prompt' => '1',
        ];
        if ($role === 'school') {
            $params['school'] = 'requests';
        } elseif ($role === 'candidate') {
            $params['candidate'] = 'bookings';
        } else {
            $params['view'] = 'requests';
        }
        return add_query_arg($params, $this->get_portal_base_url());
    }

    private function maybe_request_booking_feedback_notifications($booking_id, $force = false) {
        $booking_id = (int) $booking_id;
        if (!$booking_id) {
            return;
        }
        if (!$force && !$this->is_booking_feedback_eligible($booking_id)) {
            return;
        }
        if (get_post_meta($booking_id, 'cmn_feedback_requested_at', true)) {
            return;
        }
        $candidate_id = (int) get_post_meta($booking_id, 'cmn_candidate_id', true);
        $school_id = (int) get_post_meta($booking_id, 'cmn_school_id', true);
        $candidate_name = $candidate_id ? (string) get_the_title($candidate_id) : 'Candidate';
        $school_name = $school_id ? (string) get_the_title($school_id) : 'School';
        $candidate_user_id = (int) $this->get_booking_candidate_user_id($booking_id);
        $school_user_id = (int) $this->get_booking_school_user_id($booking_id);
        if ($school_user_id) {
            $this->add_notification(
                $school_user_id,
                'feedback_request_school',
                'Feedback requested: Candidate ' . $candidate_name,
                'Please rate ' . $candidate_name . ' for this completed booking.',
                $this->get_booking_chat_link_for_role($booking_id, 'school')
            );
        }
        if ($candidate_user_id) {
            $this->add_notification(
                $candidate_user_id,
                'feedback_request_candidate',
                'Feedback requested: Booking with ' . $school_name,
                'Please rate your completed booking with ' . $school_name . '.',
                $this->get_booking_chat_link_for_role($booking_id, 'candidate')
            );
        }
        update_post_meta($booking_id, 'cmn_feedback_requested_at', current_time('mysql'));
    }

    private function maybe_request_feedback_for_past_bookings($limit = 150) {
        $booking_ids = get_posts([
            'post_type' => 'cmn_booking',
            'posts_per_page' => max(10, (int) $limit),
            'fields' => 'ids',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        foreach ((array) $booking_ids as $booking_id) {
            if (get_post_meta((int) $booking_id, 'cmn_feedback_requested_at', true)) {
                continue;
            }
            if (!$this->is_booking_feedback_eligible((int) $booking_id)) {
                continue;
            }
            $this->maybe_request_booking_feedback_notifications((int) $booking_id);
        }
    }

    private function get_booking_feedback_tag_options($viewer_role) {
        if ($viewer_role === 'candidate') {
            return [
                'Friendly staff',
                'Clear timetable',
                'Behaviour well managed',
                'Good handover',
                'Poor instructions',
                'Behaviour issues',
                'No support on arrival',
            ];
        }
        return [
            'Great communicator',
            'Calm under pressure',
            'Followed instructions',
            'Would rebook',
            'Late arrival',
            'Poor communication',
            'Unprepared',
        ];
    }

    private function sanitize_booking_feedback_tags($raw_tags, $viewer_role) {
        $allowed = $this->get_booking_feedback_tag_options($viewer_role);
        $allowed_map = array_fill_keys($allowed, true);
        $incoming = [];
        if (is_string($raw_tags)) {
            $decoded = json_decode($raw_tags, true);
            if (is_array($decoded)) {
                $incoming = $decoded;
            } else {
                $incoming = array_filter(array_map('trim', explode(',', $raw_tags)));
            }
        } elseif (is_array($raw_tags)) {
            $incoming = $raw_tags;
        }
        $clean = [];
        foreach ($incoming as $tag) {
            $tag = sanitize_text_field((string) $tag);
            if ($tag === '' || !isset($allowed_map[$tag])) {
                continue;
            }
            $clean[$tag] = $tag;
        }
        return array_values($clean);
    }

    private function get_booking_feedback_rows($booking_id) {
        global $wpdb;
        $table = $this->get_booking_feedback_table();
        return (array) $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE booking_id = %d ORDER BY created_at DESC, id DESC",
            (int) $booking_id
        ), ARRAY_A);
    }

    private function format_booking_feedback_row($row, $viewer_user_id, $viewer_role, $is_owner_feedback) {
        $viewer_user_id = (int) $viewer_user_id;
        $is_staff_view = $viewer_role === 'staff';
        $raw_tags = [];
        if (!empty($row['tags'])) {
            $decoded_tags = json_decode((string) $row['tags'], true);
            if (is_array($decoded_tags)) {
                foreach ($decoded_tags as $tag) {
                    $tag = sanitize_text_field((string) $tag);
                    if ($tag !== '') {
                        $raw_tags[] = $tag;
                    }
                }
            }
        }
        $show_comment = $is_staff_view || $is_owner_feedback;
        return [
            'id' => (int) ($row['id'] ?? 0),
            'booking_id' => (int) ($row['booking_id'] ?? 0),
            'rater_user_id' => (int) ($row['rater_user_id'] ?? 0),
            'rated_entity_type' => (string) ($row['rated_entity_type'] ?? ''),
            'rated_entity_id' => (int) ($row['rated_entity_id'] ?? 0),
            'stars_overall' => (int) ($row['stars_overall'] ?? 0),
            'stars_1' => $is_staff_view || $is_owner_feedback ? (int) ($row['stars_1'] ?? 0) : 0,
            'stars_2' => $is_staff_view || $is_owner_feedback ? (int) ($row['stars_2'] ?? 0) : 0,
            'stars_3' => $is_staff_view || $is_owner_feedback ? (int) ($row['stars_3'] ?? 0) : 0,
            'tags' => $raw_tags,
            'would_rebook' => $is_staff_view || $is_owner_feedback ? (isset($row['would_rebook']) && $row['would_rebook'] !== null ? (int) $row['would_rebook'] : null) : null,
            'comment' => $show_comment ? (string) ($row['comment'] ?? '') : '',
            'comment_hidden' => !$show_comment && !empty($row['comment']),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'is_low_rating' => (int) ($row['stars_overall'] ?? 0) <= 2,
        ];
    }

    private function get_school_feedback_third_metric_label($booking_id) {
        $role = strtolower((string) get_post_meta((int) $booking_id, 'cmn_role', true));
        if ($role === '') {
            return 'Classroom management';
        }
        if (strpos($role, 'teaching assistant') !== false || strpos($role, 'ta') !== false) {
            return 'Engagement';
        }
        if (strpos($role, 'teacher') !== false || strpos($role, 'class') !== false) {
            return 'Classroom management';
        }
        return 'Classroom management / engagement';
    }

    private function get_booking_feedback_form_config($booking_id, $viewer_role) {
        if ($viewer_role === 'candidate') {
            return [
                'star_labels' => [
                    'stars_1' => 'Check-in/Reception',
                    'stars_2' => 'Clarity of Instructions',
                    'stars_3' => 'Support On-site',
                    'stars_overall' => 'Overall',
                ],
                'show_would_rebook' => true,
                'tags' => $this->get_booking_feedback_tag_options('candidate'),
                'submit_label' => 'Submit feedback',
            ];
        }
        return [
            'star_labels' => [
                'stars_1' => 'Punctuality',
                'stars_2' => 'Professionalism',
                'stars_3' => $this->get_school_feedback_third_metric_label($booking_id),
                'stars_overall' => 'Overall',
            ],
            'show_would_rebook' => false,
            'tags' => $this->get_booking_feedback_tag_options('school'),
            'submit_label' => 'Submit feedback',
        ];
    }

    private function get_booking_feedback_payload($booking_id, $viewer_user_id, $viewer_role = '') {
        $booking_id = (int) $booking_id;
        $viewer_user_id = (int) $viewer_user_id;
        $viewer_role = $viewer_role !== '' ? $viewer_role : $this->get_booking_feedback_role_for_user($viewer_user_id);
        $rows = $this->get_booking_feedback_rows($booking_id);
        $viewer_feedback = null;
        $counterparty_feedback = null;
        $staff_rows = [];
        foreach ($rows as $row) {
            $is_owner = (int) ($row['rater_user_id'] ?? 0) === $viewer_user_id;
            $formatted = $this->format_booking_feedback_row($row, $viewer_user_id, $viewer_role, $is_owner);
            if ($viewer_role === 'staff') {
                $staff_rows[] = $formatted;
                continue;
            }
            if ($is_owner) {
                $viewer_feedback = $formatted;
            } elseif ($counterparty_feedback === null) {
                $counterparty_feedback = $formatted;
            }
        }
        $eligible = $this->is_booking_feedback_eligible($booking_id);
        $requires_feedback = in_array($viewer_role, ['candidate', 'school'], true) && $eligible && !$viewer_feedback;
        $candidate_id = (int) get_post_meta($booking_id, 'cmn_candidate_id', true);
        $school_id = (int) get_post_meta($booking_id, 'cmn_school_id', true);
        return [
            'booking_id' => $booking_id,
            'booking_status' => (string) get_post_meta($booking_id, 'cmn_status', true),
            'booking_date' => $this->get_booking_service_date($booking_id),
            'eligible' => $eligible,
            'viewer_role' => $viewer_role,
            'requires_feedback' => $requires_feedback,
            'viewer_feedback' => $viewer_feedback,
            'counterparty_feedback' => $counterparty_feedback,
            'feedback_rows' => $viewer_role === 'staff' ? $staff_rows : [],
            'form' => in_array($viewer_role, ['candidate', 'school'], true) ? $this->get_booking_feedback_form_config($booking_id, $viewer_role) : null,
            'candidate_name' => $candidate_id ? (string) get_the_title($candidate_id) : 'Candidate',
            'school_name' => $school_id ? (string) get_the_title($school_id) : 'School',
        ];
    }

    private function get_school_ready_responses($school_user_id) {
        $school_user_id = (int) $school_user_id;
        if (!$school_user_id) {
            return [];
        }
        global $wpdb;
        $table = $this->get_ready_responses_table();
        return (array) $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE school_user_id = %d ORDER BY is_default DESC, updated_at DESC, id DESC",
            $school_user_id
        ), ARRAY_A);
    }

    private function get_school_ready_response_by_id($response_id, $school_user_id = 0) {
        $response_id = (int) $response_id;
        if (!$response_id) {
            return null;
        }
        global $wpdb;
        $table = $this->get_ready_responses_table();
        if ($school_user_id > 0) {
            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table} WHERE id = %d AND school_user_id = %d LIMIT 1",
                $response_id,
                (int) $school_user_id
            ), ARRAY_A);
        }
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d LIMIT 1",
            $response_id
        ), ARRAY_A);
    }

    private function get_default_school_ready_response($school_user_id) {
        $school_user_id = (int) $school_user_id;
        if (!$school_user_id) {
            return null;
        }
        global $wpdb;
        $table = $this->get_ready_responses_table();
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE school_user_id = %d AND is_default = 1 ORDER BY updated_at DESC LIMIT 1",
            $school_user_id
        ), ARRAY_A);
    }

    private function normalize_ready_response_selection_for_request($raw_value, $school_user_id) {
        $raw = sanitize_text_field((string) $raw_value);
        if ($raw === '' || $raw === 'default') {
            return null;
        }
        if ($raw === 'none') {
            return 0;
        }
        $response_id = (int) $raw;
        if ($response_id < 1) {
            return null;
        }
        $response = $this->get_school_ready_response_by_id($response_id, (int) $school_user_id);
        if (!$response) {
            return null;
        }
        return (int) ($response['id'] ?? 0);
    }

    private function resolve_request_ready_response_template($request, $school_user_id) {
        $selected_id = isset($request['ready_response_id']) ? (int) $request['ready_response_id'] : null;
        if ($selected_id === 0) {
            return null;
        }
        if ($selected_id > 0) {
            $selected_template = $this->get_school_ready_response_by_id($selected_id, (int) $school_user_id);
            if ($selected_template) {
                return $selected_template;
            }
        }
        return $this->get_default_school_ready_response((int) $school_user_id);
    }

    private function format_school_address($school_id) {
        $school_id = (int) $school_id;
        if (!$school_id) {
            return '';
        }
        $parts = [];
        foreach (['cmn_address_line1', 'cmn_address_line2', 'cmn_town', 'cmn_county', 'cmn_postcode'] as $field) {
            $value = trim((string) get_post_meta($school_id, $field, true));
            if ($value !== '') {
                $parts[] = $value;
            }
        }
        return implode(', ', $parts);
    }

    private function build_ready_response_tokens($booking_id, $request, $school_id, $candidate_id) {
        $booking_id = (int) $booking_id;
        $school_id = (int) $school_id;
        $candidate_id = (int) $candidate_id;
        $booking_date_raw = $this->get_booking_service_date($booking_id);
        $booking_date = $booking_date_raw ? date_i18n('M j, Y', strtotime($booking_date_raw)) : '';
        $contact_name = trim((string) get_post_meta($school_id, 'cmn_primary_contact_name', true));
        if ($contact_name === '') {
            $contact_name = trim((string) get_post_meta($school_id, 'cmn_contact1', true));
        }
        $contact_phone = trim((string) get_post_meta($school_id, 'cmn_primary_contact_phone', true));
        if ($contact_phone === '') {
            $contact_phone = trim((string) get_post_meta($school_id, 'cmn_phone', true));
        }
        return [
            'candidate_name' => $candidate_id ? (string) get_the_title($candidate_id) : '',
            'school_name' => $school_id ? (string) get_the_title($school_id) : '',
            'booking_date' => $booking_date,
            'start_time' => trim((string) get_post_meta($booking_id, 'cmn_start_time', true)),
            'end_time' => trim((string) get_post_meta($booking_id, 'cmn_end_time', true)),
            'location_name' => trim((string) get_post_meta($booking_id, 'cmn_location', true)) ?: trim((string) get_post_meta($school_id, 'cmn_location', true)),
            'location_address' => $this->format_school_address($school_id),
            'reception_instructions' => trim((string) get_post_meta($school_id, 'cmn_reception_instructions', true)),
            'parking_info' => trim((string) get_post_meta($school_id, 'cmn_parking_info', true)),
            'teacher_name' => trim((string) get_post_meta($booking_id, 'cmn_teacher_name', true)),
            'contact_name' => $contact_name,
            'contact_phone' => $contact_phone,
            'notes' => trim((string) get_post_meta($booking_id, 'cmn_notes', true)) ?: trim((string) ($request['internal_note'] ?? '')),
        ];
    }

    private function render_ready_response_template_message($template, $tokens) {
        $template = (string) $template;
        if ($template === '') {
            return '';
        }
        $replacements = [];
        foreach ((array) $tokens as $key => $value) {
            $replacements['{' . sanitize_key($key) . '}'] = sanitize_text_field((string) $value);
        }
        $rendered = strtr($template, $replacements);
        $lines = preg_split('/\r\n|\r|\n/', $rendered);
        $clean_lines = [];
        foreach ((array) $lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^[A-Za-z0-9\/\-\s]+:\s*$/', $line)) {
                continue;
            }
            $clean_lines[] = $line;
        }
        return implode("\n", $clean_lines);
    }

    private function post_booking_acceptance_auto_messages($thread_id, $booking_id, $request, $school_id, $candidate_id, $school_user_id, $am_user_id) {
        $thread_id = (int) $thread_id;
        $booking_id = (int) $booking_id;
        $school_id = (int) $school_id;
        $candidate_id = (int) $candidate_id;
        $school_user_id = (int) $school_user_id;
        $am_user_id = (int) $am_user_id;
        if (!$thread_id || !$booking_id) {
            return;
        }
        if (get_post_meta($booking_id, 'cmn_accept_auto_messages_posted', true) === '1') {
            return;
        }

        $candidate_name = $candidate_id ? (string) get_the_title($candidate_id) : 'Candidate';
        $accept_msg = 'Candidate ' . $candidate_name . ' has accepted this booking.';
        $accept_sender_user_id = $am_user_id ?: $school_user_id;
        $accept_sender_role = $am_user_id ? 'account_manager' : ($school_user_id ? 'school' : 'system');
        $this->add_booking_thread_message($thread_id, $accept_sender_user_id, $accept_sender_role, $accept_msg);

        $template_row = $this->resolve_request_ready_response_template($request, $school_user_id);
        if ($template_row && !empty($template_row['message_template'])) {
            $tokens = $this->build_ready_response_tokens($booking_id, $request, $school_id, $candidate_id);
            $template_message = $this->render_ready_response_template_message((string) $template_row['message_template'], $tokens);
            if ($template_message !== '') {
                $template_sender_user_id = $school_user_id ?: $am_user_id;
                $template_sender_role = $school_user_id ? 'school' : ($am_user_id ? 'account_manager' : 'system');
                $this->add_booking_thread_message($thread_id, $template_sender_user_id, $template_sender_role, $template_message);
            }
        }
        update_post_meta($booking_id, 'cmn_accept_auto_messages_posted', '1');
    }

    private function maybe_notify_low_booking_feedback($booking_id, $viewer_role, $stars_overall, $rater_user_id) {
        $booking_id = (int) $booking_id;
        $rater_user_id = (int) $rater_user_id;
        $stars_overall = (int) $stars_overall;
        if ($booking_id < 1 || $rater_user_id < 1) {
            return;
        }
        $meta_key = 'cmn_feedback_low_alert_' . $booking_id . '_' . $rater_user_id;
        if ($stars_overall > 2) {
            delete_post_meta($booking_id, $meta_key);
            return;
        }
        if (get_post_meta($booking_id, $meta_key, true) === '1') {
            return;
        }
        $candidate_id = (int) get_post_meta($booking_id, 'cmn_candidate_id', true);
        $school_id = (int) get_post_meta($booking_id, 'cmn_school_id', true);
        $candidate_name = $candidate_id ? (string) get_the_title($candidate_id) : 'Candidate';
        $school_name = $school_id ? (string) get_the_title($school_id) : 'School';
        $staff_link = add_query_arg([
            'view' => 'requests',
            'cmn_booking_chat' => $booking_id,
            'cmn_thread_type' => 'booking_details',
        ], $this->get_portal_base_url());
        $message = 'Low feedback alert (' . $stars_overall . '/5) for ' . $school_name . ' · ' . $candidate_name . '.';
        $notify_user_ids = [];
        foreach ($this->get_admin_users_for_support() as $admin_id) {
            $notify_user_ids[(int) $admin_id] = (int) $admin_id;
        }
        $request_id = (int) get_post_meta($booking_id, 'cmn_request_id', true);
        if ($request_id > 0) {
            $request = $this->get_candidate_request_by_id($request_id);
            if ($request && !empty($request['account_manager_user_id'])) {
                $notify_user_ids[(int) $request['account_manager_user_id']] = (int) $request['account_manager_user_id'];
            }
        }
        foreach ($notify_user_ids as $notify_user_id) {
            $this->add_notification((int) $notify_user_id, 'booking_feedback_low', 'Low booking feedback', $message, $staff_link);
        }
        update_post_meta($booking_id, $meta_key, '1');
    }

    private function get_tomorrow_date() {
        $tz = wp_timezone();
        $now = new DateTime('now', $tz);
        return $now->modify('+1 day')->format('Y-m-d');
    }

    private function get_candidate_availability_window(DateTime $now = null) {
        $tz = wp_timezone();
        if (!$now) {
            $now = new DateTime('now', $tz);
        }
        $hour = (int) $now->format('G');
        $is_open = ($hour >= 19 || $hour < 8);
        $target_date = ($hour >= 19)
            ? (clone $now)->modify('+1 day')->format('Y-m-d')
            : $now->format('Y-m-d');
        return [
            'is_open' => $is_open,
            'target_date' => $target_date,
            'closed_message' => 'You can confirm availability from 7pm until 8am.',
        ];
    }

    private function has_candidate_availability($candidate_id, $date) {
        if (!$candidate_id || !$date) {
            return false;
        }
        global $wpdb;
        $table = $this->get_candidate_availability_table();
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE candidate_id = %d AND available_date = %s",
            $candidate_id,
            $date
        ));
        return !empty($exists);
    }

    private function is_candidate_unavailable($candidate_id, $date) {
        if (!$candidate_id || !$date) {
            return false;
        }
        global $wpdb;
        $table = $this->get_candidate_calendar_table();
        if (!$table) {
            return false;
        }
        $status = $wpdb->get_var($wpdb->prepare(
            "SELECT status FROM {$table} WHERE candidate_id = %d AND date = %s",
            $candidate_id,
            $date
        ));
        return $status === 'unavailable';
    }

    private function get_candidate_calendar_map($candidate_id, $start_date, $end_date) {
        if (!$candidate_id || !$start_date || !$end_date) {
            return [];
        }
        global $wpdb;
        $table = $this->get_candidate_calendar_table();
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT date, status FROM {$table} WHERE candidate_id = %d AND date BETWEEN %s AND %s",
            $candidate_id,
            $start_date,
            $end_date
        ), ARRAY_A);
        $map = [];
        if ($rows) {
            foreach ($rows as $row) {
                if (!empty($row['date']) && !empty($row['status'])) {
                    $map[$row['date']] = $row['status'];
                }
            }
        }
        return $map;
    }

    private function is_weekday_date($date) {
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        $dt = DateTime::createFromFormat('Y-m-d', $date, wp_timezone());
        if (!$dt) {
            return false;
        }
        $weekday = (int) $dt->format('N');
        return $weekday >= 1 && $weekday <= 5;
    }

    private function get_calendar_limit_dates() {
        $today = current_time('Y-m-d');
        $limit = (new DateTime($today, wp_timezone()))->modify('+30 days')->format('Y-m-d');
        return [$today, $limit];
    }

    private function get_candidate_calendar_summary($candidate_id) {
        if (!$candidate_id) {
            return [
                'available_count' => 0,
                'unavailable_count' => 0,
                'next_available' => '',
                'next_available_label' => 'Not set',
            ];
        }
        [$today, $limit] = $this->get_calendar_limit_dates();
        $map = $this->get_candidate_calendar_map($candidate_id, $today, $limit);
        $available = 0;
        $unavailable = 0;
        $next = '';
        foreach ($map as $date => $status) {
            if ($status === 'available') {
                $available++;
                if ($next === '' || $date < $next) {
                    $next = $date;
                }
            } elseif ($status === 'unavailable') {
                $unavailable++;
            }
        }
        $next_label = $next ? date_i18n('l, F jS', strtotime($next)) : 'Not set';
        return [
            'available_count' => $available,
            'unavailable_count' => $unavailable,
            'next_available' => $next,
            'next_available_label' => $next_label,
        ];
    }

    private function get_candidate_requests($status = 'requested', $limit = 10) {
        global $wpdb;
        $table = $this->get_candidate_requests_table();
        $status = sanitize_text_field($status);
        if ($status === 'pending') {
            $status = 'requested';
        }
        $limit = (int) $limit;
        if ($limit < 1) {
            $limit = 10;
        }
        $sql = $wpdb->prepare(
            "SELECT * FROM {$table} WHERE status = %s ORDER BY requested_at DESC LIMIT %d",
            $status,
            $limit
        );
        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ($rows as &$row) {
            $this->maybe_mark_request_expired($row);
        }
        return $rows;
    }

    private function get_candidate_requests_for_status($status, $limit = 50, $school_domains = []) {
        global $wpdb;
        $table = $this->get_candidate_requests_table();
        $limit = (int) $limit;
        if ($limit < 1) {
            $limit = 50;
        }
        $where = [];
        $params = [];
        if ($status && $status !== 'all') {
            if ($status === 'pending') {
                $status = 'requested';
            }
            $where[] = 'status = %s';
            $params[] = sanitize_text_field($status);
        }
        if ($school_domains) {
            $school_domains = array_values(array_unique(array_filter(array_map('strtolower', $school_domains))));
            $placeholders = implode(',', array_fill(0, count($school_domains), '%s'));
            $where[] = "school_email_domain IN ({$placeholders})";
            $params = array_merge($params, $school_domains);
        }
        $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $sql = "SELECT * FROM {$table} {$where_sql} ORDER BY requested_at DESC LIMIT {$limit}";
        if ($params) {
            $sql = $wpdb->prepare($sql, $params);
        }
        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ($rows as &$row) {
            $this->maybe_mark_request_expired($row);
        }
        return $rows;
    }

    private function get_school_candidate_requests($school_domain, $limit = 20) {
        global $wpdb;
        $table = $this->get_candidate_requests_table();
        $school_domain = strtolower(trim((string) $school_domain));
        if ($school_domain === '') {
            return [];
        }
        $limit = (int) $limit;
        if ($limit < 1) {
            $limit = 20;
        }
        $sql = $wpdb->prepare(
            "SELECT * FROM {$table} WHERE school_email_domain = %s ORDER BY requested_at DESC LIMIT %d",
            $school_domain,
            $limit
        );
        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ($rows as &$row) {
            $this->maybe_mark_request_expired($row);
        }
        return $rows;
    }

    private function get_candidate_request_by_id($request_id) {
        global $wpdb;
        $table = $this->get_candidate_requests_table();
        $request_id = (int) $request_id;
        if (!$request_id) {
            return null;
        }
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d LIMIT 1",
            $request_id
        ), ARRAY_A);
        if ($row) {
            $this->maybe_mark_request_expired($row);
        }
        return $row ?: null;
    }

    private function get_candidate_requests_for_candidate($candidate_id, $limit = 20) {
        global $wpdb;
        $table = $this->get_candidate_requests_table();
        $candidate_id = (int) $candidate_id;
        if (!$candidate_id) {
            return [];
        }
        $limit = max(1, (int) $limit);
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE candidate_id = %d ORDER BY requested_at DESC LIMIT %d",
            $candidate_id,
            $limit
        ), ARRAY_A);
        foreach ($rows as &$row) {
            $this->maybe_mark_request_expired($row);
        }
        return $rows;
    }

    private function get_candidate_completed_booking_count($candidate_id) {
        $candidate_id = (int) $candidate_id;
        if (!$candidate_id) {
            return 0;
        }
        $ids = get_posts([
            'post_type' => 'cmn_booking',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'cmn_candidate_id',
                    'value' => $candidate_id,
                ],
                [
                    'key' => 'cmn_status',
                    'value' => ['approved', 'completed', 'confirmed'],
                    'compare' => 'IN',
                ],
            ],
        ]);
        return is_array($ids) ? count($ids) : 0;
    }

    private function get_candidate_strength_metrics($candidate_id, $candidate_user_id = 0) {
        $candidate_id = (int) $candidate_id;
        $candidate_user_id = (int) $candidate_user_id;
        if (!$candidate_id) {
            return [
                'score' => 0,
                'profile_completion' => 0,
                'docs_approved' => 0,
                'docs_total' => 3,
                'bookings_completed' => 0,
                'avg_rating' => 0.0,
                'years_experience' => 0,
            ];
        }

        $profile_completion = $candidate_user_id ? (int) get_user_meta($candidate_user_id, 'cmn_profile_completion_pct', true) : 0;
        if ($profile_completion < 1) {
            $profile_completion = (int) get_post_meta($candidate_id, 'cmn_profile_completion_pct', true);
        }
        $profile_completion = max(0, min(100, $profile_completion));

        $doc_states = [];
        foreach (['dbs', 'id', 'cv'] as $doc_type) {
            $doc = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, $doc_type);
            $doc_states[$doc_type] = [
                'uploaded' => !empty($doc['uploaded']),
                'status' => sanitize_key((string) ($doc['doc_status'] ?? 'not_uploaded')),
            ];
        }
        $docs_approved = 0;
        $doc_points = 0;
        foreach ($doc_states as $state) {
            if ($state['status'] === 'approved') {
                $docs_approved++;
                $doc_points += 15;
            } elseif ($state['status'] === 'pending' || $state['uploaded']) {
                $doc_points += 8;
            } elseif ($state['status'] === 'rejected') {
                $doc_points += 2;
            }
        }

        $bookings_completed = $this->get_candidate_completed_booking_count($candidate_id);
        $bookings_points = min(15, (int) round($bookings_completed * 1.5));

        $avg_rating = 0.0;
        if ($candidate_user_id) {
            $avg_rating = (float) get_user_meta($candidate_user_id, 'cmn_candidate_avg_rating', true);
        }
        if ($avg_rating <= 0) {
            $avg_rating = (float) get_post_meta($candidate_id, 'cmn_candidate_avg_rating', true);
        }
        if ($avg_rating < 0) {
            $avg_rating = 0.0;
        }
        if ($avg_rating > 5) {
            $avg_rating = 5.0;
        }
        $rating_points = (int) round(($avg_rating / 5) * 10);

        $years_experience = 0;
        foreach (['cmn_years_experience', 'years_experience', 'cmn_experience_years'] as $key) {
            $value = (float) get_post_meta($candidate_id, $key, true);
            if ($value <= 0 && $candidate_user_id) {
                $value = (float) get_user_meta($candidate_user_id, $key, true);
            }
            if ($value > 0) {
                $years_experience = (int) round($value);
                break;
            }
        }
        $experience_points = min(5, max(0, (int) round(($years_experience / 10) * 5)));

        $profile_points = (int) round(($profile_completion / 100) * 25);
        $score = max(0, min(100, $profile_points + $doc_points + $bookings_points + $rating_points + $experience_points));

        return [
            'score' => $score,
            'profile_completion' => $profile_completion,
            'docs_approved' => $docs_approved,
            'docs_total' => 3,
            'bookings_completed' => $bookings_completed,
            'avg_rating' => round($avg_rating, 1),
            'years_experience' => $years_experience,
        ];
    }

    private function get_booking_id_for_request($request_id) {
        $request_id = (int) $request_id;
        if (!$request_id) {
            return 0;
        }
        $bookings = get_posts([
            'post_type' => 'cmn_booking',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'cmn_request_id',
                    'value' => $request_id,
                ],
            ],
        ]);
        return $bookings ? (int) $bookings[0] : 0;
    }

    private function get_candidate_doc_meta_keys($doc_type) {
        $doc_type = sanitize_key((string) $doc_type);
        $map = [
            'dbs' => [
                'attachment' => 'cmn_doc_dbs_attachment_id',
                'legacy_attachment' => 'cmn_candidate_dbs_doc',
                'uploaded_at' => 'cmn_doc_dbs_uploaded_at',
                'legacy_uploaded_at' => 'cmn_candidate_dbs_doc_uploaded_at',
                'filename' => 'cmn_doc_dbs_filename',
                'filesize' => 'cmn_doc_dbs_filesize',
                'mime' => 'cmn_doc_dbs_mime',
                'review_status' => 'cmn_doc_dbs_review_status',
                'review_reason' => 'cmn_doc_dbs_review_reason',
                'reviewed_at' => 'cmn_doc_dbs_reviewed_at',
                'reviewed_by' => 'cmn_doc_dbs_reviewed_by',
                'post_meta' => 'cmn_dbs_file',
            ],
            'id' => [
                'attachment' => 'cmn_doc_id_attachment_id',
                'legacy_attachment' => 'cmn_candidate_id_doc',
                'uploaded_at' => 'cmn_doc_id_uploaded_at',
                'legacy_uploaded_at' => 'cmn_candidate_id_doc_uploaded_at',
                'filename' => 'cmn_doc_id_filename',
                'filesize' => 'cmn_doc_id_filesize',
                'mime' => 'cmn_doc_id_mime',
                'review_status' => 'cmn_doc_id_review_status',
                'review_reason' => 'cmn_doc_id_review_reason',
                'reviewed_at' => 'cmn_doc_id_reviewed_at',
                'reviewed_by' => 'cmn_doc_id_reviewed_by',
                'post_meta' => 'cmn_id_file',
            ],
            'cv' => [
                'attachment' => 'cmn_doc_cv_attachment_id',
                'legacy_attachment' => 'cmn_candidate_cv_doc',
                'uploaded_at' => 'cmn_doc_cv_uploaded_at',
                'legacy_uploaded_at' => 'cmn_candidate_cv_doc_uploaded_at',
                'filename' => 'cmn_doc_cv_filename',
                'filesize' => 'cmn_doc_cv_filesize',
                'mime' => 'cmn_doc_cv_mime',
                'review_status' => 'cmn_doc_cv_review_status',
                'review_reason' => 'cmn_doc_cv_review_reason',
                'reviewed_at' => 'cmn_doc_cv_reviewed_at',
                'reviewed_by' => 'cmn_doc_cv_reviewed_by',
                'post_meta' => 'cmn_cv_file',
            ],
        ];
        return $map[$doc_type] ?? [];
    }

    private function get_candidate_doc_types() {
        return ['dbs', 'id', 'cv'];
    }

    private function normalize_candidate_doc_review_status($status, $has_upload) {
        if (!$has_upload) {
            return 'not_uploaded';
        }
        $status = sanitize_key((string) $status);
        if (in_array($status, ['approved', 'rejected', 'pending'], true)) {
            return $status;
        }
        return 'pending';
    }

    private function get_candidate_doc_summary($docs) {
        $doc_types = $this->get_candidate_doc_types();
        $states = [];
        foreach ($doc_types as $doc_type) {
            $states[] = (string) ($docs[$doc_type]['doc_status'] ?? 'not_uploaded');
        }
        $has_rejected = in_array('rejected', $states, true);
        $has_missing = in_array('not_uploaded', $states, true);
        $all_approved = !in_array('pending', $states, true) && !in_array('rejected', $states, true) && !in_array('not_uploaded', $states, true);

        if ($has_rejected) {
            return [
                'key' => 'action_required',
                'badge_class' => 'is-declined',
                'badge_label' => 'Action Required',
                'copy' => 'Action Required - Document Rejected',
            ];
        }
        if ($has_missing) {
            return [
                'key' => 'incomplete',
                'badge_class' => 'is-declined',
                'badge_label' => 'Incomplete',
                'copy' => 'Incomplete - Documents Required',
            ];
        }
        if ($all_approved) {
            return [
                'key' => 'verified',
                'badge_class' => 'is-verified',
                'badge_label' => 'Verified',
                'copy' => 'Verified',
            ];
        }
        return [
            'key' => 'awaiting_review',
            'badge_class' => 'is-pending',
            'badge_label' => 'Awaiting Review',
            'copy' => 'Documents Awaiting Review',
        ];
    }

    public function cmn_get_candidate_compliance_score($candidate_user_id) {
        $candidate_user_id = (int) $candidate_user_id;
        if ($candidate_user_id < 1) {
            return 0;
        }
        $candidate_id = (int) $this->get_candidate_id_for_user($candidate_user_id);
        if ($candidate_id < 1) {
            return 0;
        }
        $weights = [
            'dbs' => 40,
            'id' => 30,
            'cv' => 30,
        ];
        $score = 0;
        foreach ($weights as $doc_type => $points) {
            $status = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, $doc_type);
            if (($status['doc_status'] ?? 'not_uploaded') === 'approved') {
                $score += (int) $points;
            }
        }
        return max(0, min(100, (int) $score));
    }

    private function get_candidate_compliance_payload($candidate_id, $candidate_user_id) {
        $candidate_id = (int) $candidate_id;
        $candidate_user_id = (int) $candidate_user_id;
        $docs = [];
        foreach ($this->get_candidate_doc_types() as $doc_type) {
            $status = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, $doc_type);
            $docs[$doc_type] = [
                'status' => sanitize_key((string) ($status['doc_status'] ?? 'not_uploaded')),
                'label' => sanitize_text_field((string) ($status['status_label'] ?? 'Not Uploaded')),
                'review_reason' => sanitize_text_field((string) ($status['review_reason'] ?? '')),
                'uploaded_at' => sanitize_text_field((string) ($status['uploaded_at'] ?? '')),
                'uploaded_at_label' => sanitize_text_field((string) ($status['uploaded_at_label'] ?? '')),
            ];
        }
        return [
            'score' => $this->cmn_get_candidate_compliance_score($candidate_user_id),
            'docs' => $docs,
            'summary' => $this->get_candidate_doc_summary([
                'dbs' => ['doc_status' => $docs['dbs']['status']],
                'id' => ['doc_status' => $docs['id']['status']],
                'cv' => ['doc_status' => $docs['cv']['status']],
            ]),
        ];
    }

    private function sync_candidate_admin_verification_status($candidate_id, $candidate_user_id, $docs = null) {
        $candidate_id = (int) $candidate_id;
        $candidate_user_id = (int) $candidate_user_id;
        if (!$candidate_id || !$candidate_user_id) {
            return '';
        }
        if (!is_array($docs)) {
            $docs = [];
            foreach ($this->get_candidate_doc_types() as $doc_type) {
                $docs[$doc_type] = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, $doc_type);
            }
        }
        $summary = $this->get_candidate_doc_summary($docs);
        $status_map = [
            'verified' => 'approved',
            'action_required' => 'rejected',
            'incomplete' => 'incomplete',
            'awaiting_review' => 'awaiting_review',
        ];
        $verification_status = $status_map[$summary['key']] ?? 'awaiting_review';
        update_user_meta($candidate_user_id, 'cmn_admin_verification_status', $verification_status);
        update_post_meta($candidate_id, 'cmn_admin_verification_status', $verification_status);
        return $verification_status;
    }

    private function get_candidate_doc_attachment_id($candidate_user_id, $doc_type) {
        $keys = $this->get_candidate_doc_meta_keys($doc_type);
        if (!$keys || !$candidate_user_id) {
            return 0;
        }
        $attachment_id = (int) get_user_meta((int) $candidate_user_id, $keys['attachment'], true);
        if (!$attachment_id && !empty($keys['legacy_attachment'])) {
            $attachment_id = (int) get_user_meta((int) $candidate_user_id, $keys['legacy_attachment'], true);
        }
        return $attachment_id;
    }

    private function cmn_school_can_access_candidate_docs($school_user_id, $candidate_user_id) {
        global $wpdb;
        $school_user_id = (int) $school_user_id;
        $candidate_user_id = (int) $candidate_user_id;
        if (!$school_user_id || !$candidate_user_id || !$this->is_school_user($school_user_id)) {
            return false;
        }
        $school_id = (int) $this->resolve_school_id_for_user($school_user_id);
        $candidate_id = (int) $this->get_candidate_id_for_user($candidate_user_id);
        if (!$school_id || !$candidate_id) {
            return false;
        }

        $requests_table = $this->get_candidate_requests_table();
        $request_match = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$requests_table} WHERE school_id = %d AND candidate_id = %d AND status IN ('accepted','confirmed') ORDER BY id DESC LIMIT 1",
            $school_id,
            $candidate_id
        ));
        if ($request_match > 0) {
            return true;
        }

        $booking_ids = get_posts([
            'post_type' => 'cmn_booking',
            'fields' => 'ids',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'cmn_school_id',
                    'value' => $school_id,
                ],
                [
                    'key' => 'cmn_candidate_id',
                    'value' => $candidate_id,
                ],
                [
                    'key' => 'cmn_status',
                    'value' => ['approved', 'confirmed', 'accepted'],
                    'compare' => 'IN',
                ],
            ],
        ]);
        return !empty($booking_ids);
    }

    private function cmn_candidate_can_access_doc($viewer_user_id, $candidate_user_id, $attachment_id = 0) {
        $viewer_user_id = (int) $viewer_user_id;
        $candidate_user_id = (int) $candidate_user_id;
        if (!$viewer_user_id || !$candidate_user_id) {
            return false;
        }
        if ($viewer_user_id === $candidate_user_id) {
            return true;
        }
        if ($this->is_admin_user($viewer_user_id) || $this->is_staff_role($viewer_user_id) || $this->is_account_manager_user($viewer_user_id)) {
            return true;
        }
        if ($this->is_school_user($viewer_user_id) && $this->cmn_school_can_access_candidate_docs($viewer_user_id, $candidate_user_id)) {
            $attachment_id = (int) $attachment_id;
            if (!$attachment_id) {
                return false;
            }
            $doc_type = sanitize_key((string) get_post_meta($attachment_id, 'cmn_doc_type', true));
            if ($doc_type !== 'cv_formatted') {
                return false;
            }
            $candidate_id = (int) $this->get_candidate_id_for_user($candidate_user_id);
            if (!$candidate_id) {
                return false;
            }
            $formatted_attachment_id = $this->get_candidate_cv_formatted_attachment_id($candidate_id);
            return $formatted_attachment_id > 0 && $formatted_attachment_id === $attachment_id;
        }
        return false;
    }

    public function protect_candidate_doc_attachment_access() {
        if (is_admin() || !is_attachment()) {
            return;
        }
        $attachment_id = (int) get_queried_object_id();
        if (!$attachment_id) {
            return;
        }
        $owner_user_id = (int) get_post_meta($attachment_id, 'cmn_owner_user_id', true);
        if (!$owner_user_id) {
            $candidate_id = (int) get_post_meta($attachment_id, 'cmn_candidate_id', true);
            if ($candidate_id) {
                $owner_user_id = (int) $this->get_candidate_user_id($candidate_id);
            }
        }
        if (!$owner_user_id) {
            return;
        }
        if (!is_user_logged_in()) {
            wp_safe_redirect(wp_login_url($this->get_portal_base_url()));
            exit;
        }
        $viewer_user_id = get_current_user_id();
        if (!$this->cmn_candidate_can_access_doc($viewer_user_id, $owner_user_id, $attachment_id)) {
            status_header(403);
            wp_die('Access restricted.');
        }
    }

    private function get_candidate_doc_status($candidate_id, $user_id, $doc_type) {
        $keys = $this->get_candidate_doc_meta_keys($doc_type);
        if (!$keys) {
            return [
                'doc_status' => 'not_uploaded',
                'status_label' => 'Not Uploaded',
                'status_copy' => 'Not Uploaded',
                'badge_class' => 'is-declined',
                'uploaded' => false,
                'attachment_id' => 0,
                'filename' => '',
                'url' => '',
                'uploaded_at' => '',
                'uploaded_label' => 'Not uploaded',
                'uploaded_at_label' => '',
                'filesize' => 0,
                'filesize_label' => '',
                'mime' => '',
                'review_status' => 'not_uploaded',
                'review_reason' => '',
                'reviewed_at' => '',
                'reviewed_at_label' => '',
                'reviewed_by' => 0,
            ];
        }
        $attachment_id = $this->get_candidate_doc_attachment_id((int) $user_id, $doc_type);
        $legacy_url = $keys && !empty($keys['post_meta']) ? (string) get_post_meta((int) $candidate_id, (string) $keys['post_meta'], true) : '';
        if (!$attachment_id && $legacy_url !== '') {
            $resolved_attachment_id = (int) attachment_url_to_postid($legacy_url);
            if ($resolved_attachment_id > 0) {
                $attachment_id = $resolved_attachment_id;
                update_user_meta((int) $user_id, $keys['attachment'], $attachment_id);
                if (!empty($keys['legacy_attachment'])) {
                    update_user_meta((int) $user_id, $keys['legacy_attachment'], $attachment_id);
                }
            }
        }
        $uploaded_at = (string) get_user_meta((int) $user_id, $keys['uploaded_at'], true);
        if ($uploaded_at === '' && !empty($keys['legacy_uploaded_at'])) {
            $uploaded_at = (string) get_user_meta((int) $user_id, $keys['legacy_uploaded_at'], true);
        }
        $url = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
        if ($url === '' && $legacy_url !== '') {
            $url = esc_url_raw($legacy_url);
        }
        if ($attachment_id) {
            $existing_owner = (int) get_post_meta($attachment_id, 'cmn_owner_user_id', true);
            if (!$existing_owner && $user_id) {
                update_post_meta($attachment_id, 'cmn_owner_user_id', (int) $user_id);
            }
            $existing_doc_type = (string) get_post_meta($attachment_id, 'cmn_doc_type', true);
            if ($existing_doc_type === '') {
                update_post_meta($attachment_id, 'cmn_doc_type', $doc_type);
            }
        }
        $filename = $attachment_id ? (string) get_user_meta((int) $user_id, $keys['filename'], true) : '';
        if ($filename === '' && $attachment_id) {
            $filename = basename((string) get_attached_file($attachment_id));
        }
        if (!$filename && $url) {
            $filename = basename((string) parse_url($url, PHP_URL_PATH));
        }
        $filesize = $attachment_id ? (int) get_user_meta((int) $user_id, $keys['filesize'], true) : 0;
        if (!$filesize && $attachment_id) {
            $path = get_attached_file($attachment_id);
            if ($path && file_exists($path)) {
                $filesize = (int) filesize($path);
            }
        }
        $mime = $attachment_id ? (string) get_user_meta((int) $user_id, $keys['mime'], true) : '';
        if ($mime === '' && $attachment_id) {
            $mime = (string) get_post_mime_type($attachment_id);
        }
        $review_status_raw = !empty($keys['review_status']) ? (string) get_user_meta((int) $user_id, $keys['review_status'], true) : '';
        $review_reason = !empty($keys['review_reason']) ? sanitize_text_field((string) get_user_meta((int) $user_id, $keys['review_reason'], true)) : '';
        $reviewed_at = !empty($keys['reviewed_at']) ? (string) get_user_meta((int) $user_id, $keys['reviewed_at'], true) : '';
        $reviewed_by = !empty($keys['reviewed_by']) ? (int) get_user_meta((int) $user_id, $keys['reviewed_by'], true) : 0;
        $doc_status = $this->normalize_candidate_doc_review_status($review_status_raw, (bool) ($url !== ''));
        $status_label_map = [
            'not_uploaded' => 'Not Uploaded',
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
        $badge_class_map = [
            'not_uploaded' => 'is-declined',
            'pending' => 'is-pending',
            'approved' => 'is-verified',
            'rejected' => 'is-declined',
        ];
        $status_label = $status_label_map[$doc_status] ?? 'Not Uploaded';
        $badge_class = $badge_class_map[$doc_status] ?? 'is-declined';
        $status_copy = $status_label;
        if ($doc_status === 'rejected' && $review_reason !== '') {
            $status_copy .= ' - ' . $review_reason;
        }
        $reviewed_at_label = $reviewed_at ? date_i18n('M j, Y g:ia', strtotime($reviewed_at)) : '';
        $uploaded_label = 'Not uploaded';
        $uploaded_at_label = '';
        if ($url && $filename) {
            $uploaded_label = 'Uploaded: ' . $filename;
            if ($uploaded_at) {
                $uploaded_at_label = date_i18n('M j, Y g:ia', strtotime($uploaded_at));
                $uploaded_label .= ' on ' . $uploaded_at_label;
            }
        }
        return [
            'doc_status' => $doc_status,
            'status_label' => $status_label,
            'status_copy' => $status_copy,
            'badge_class' => $badge_class,
            'uploaded' => (bool) ($url !== ''),
            'attachment_id' => $attachment_id,
            'filename' => $filename,
            'url' => $url,
            'uploaded_at' => $uploaded_at,
            'uploaded_at_label' => $uploaded_at_label,
            'uploaded_label' => $uploaded_label,
            'filesize' => $filesize,
            'filesize_label' => $filesize > 0 ? size_format($filesize) : '',
            'mime' => $mime,
            'review_status' => $doc_status,
            'review_reason' => $doc_status === 'rejected' ? $review_reason : '',
            'reviewed_at' => $reviewed_at,
            'reviewed_at_label' => $reviewed_at_label,
            'reviewed_by' => $reviewed_by,
        ];
    }

    private function is_candidate_doc_attachment_still_linked($attachment_id) {
        global $wpdb;
        $attachment_id = (int) $attachment_id;
        if (!$attachment_id) {
            return false;
        }
        $keys = [];
        foreach ($this->get_candidate_doc_types() as $doc_type) {
            $map = $this->get_candidate_doc_meta_keys($doc_type);
            if (!empty($map['attachment'])) {
                $keys[] = $map['attachment'];
            }
            if (!empty($map['legacy_attachment'])) {
                $keys[] = $map['legacy_attachment'];
            }
        }
        $keys = array_values(array_unique(array_filter($keys)));
        if (!$keys) {
            return false;
        }
        $placeholders = implode(',', array_fill(0, count($keys), '%s'));
        $params = array_merge($keys, [(string) $attachment_id]);
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key IN ({$placeholders}) AND meta_value = %s",
            $params
        );
        $count = (int) $wpdb->get_var($sql);
        return $count > 0;
    }

    private function update_candidate_profile_completion($candidate_id, $user_id) {
        $candidate_id = (int) $candidate_id;
        $user_id = (int) $user_id;
        if (!$candidate_id || !$user_id) {
            return 0;
        }
        $candidate_post = get_post($candidate_id);
        $profile_email = (string) get_post_meta($candidate_id, 'cmn_email', true);
        $first_name = (string) get_user_meta($user_id, 'first_name', true);
        $last_name = (string) get_user_meta($user_id, 'last_name', true);
        $profile_phone = (string) get_user_meta($user_id, 'phone', true);
        if ($profile_phone === '') {
            $profile_phone = (string) get_post_meta($candidate_id, 'cmn_phone', true);
        }
        $travel_distance = (string) get_user_meta($user_id, 'travel_radius', true);
        if ($travel_distance === '') {
            $travel_distance = (string) get_post_meta($candidate_id, 'cmn_travel_distance', true);
        }
        $role_label = (string) get_user_meta($user_id, 'role_type', true);
        if ($role_label === '') {
            $roles = (array) get_post_meta($candidate_id, 'cmn_roles', true);
            $role_label = $roles ? (string) $roles[0] : '';
        }
        if ($first_name === '' && $candidate_post) {
            $parts = preg_split('/\s+/', trim((string) $candidate_post->post_title));
            $first_name = (string) ($parts[0] ?? '');
            $last_name = count($parts) > 1 ? (string) end($parts) : $last_name;
        }
        $doc_dbs = $this->get_candidate_doc_status($candidate_id, $user_id, 'dbs');
        $doc_id = $this->get_candidate_doc_status($candidate_id, $user_id, 'id');
        $doc_cv = $this->get_candidate_doc_status($candidate_id, $user_id, 'cv');

        $fields = [
            $first_name,
            $last_name,
            $profile_email,
            $profile_phone,
            $role_label,
            $travel_distance,
            $doc_cv['uploaded'] ? '1' : '',
            $doc_dbs['uploaded'] ? '1' : '',
            $doc_id['uploaded'] ? '1' : '',
        ];
        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($field)) {
                $completed++;
            }
        }
        $pct = (int) round(($completed / max(1, count($fields))) * 100);
        update_user_meta($user_id, 'cmn_profile_completion_pct', (string) $pct);
        update_post_meta($candidate_id, 'cmn_profile_completion_pct', (string) $pct);
        return $pct;
    }

    private function get_candidate_users_for_compliance_reminders() {
        return get_users([
            'role__in' => ['cmn_candidate', 'cmn_candidate_pending', 'candidate'],
            'fields' => ['ID', 'display_name', 'user_email'],
            'number' => 500,
        ]);
    }

    private function get_candidate_dbs_expiry_timestamp($candidate_id, $user_id) {
        $keys = [
            'cmn_doc_dbs_expiry_date',
            'cmn_dbs_expiry_date',
            'cmn_dbs_expiry',
            'dbs_expiry_date',
            'dbs_expiry',
        ];
        foreach ($keys as $key) {
            $value = (string) get_user_meta((int) $user_id, $key, true);
            if ($value === '') {
                $value = (string) get_post_meta((int) $candidate_id, $key, true);
            }
            if ($value === '') {
                continue;
            }
            if (ctype_digit($value)) {
                $numeric = (int) $value;
                if ($numeric > 1000000) {
                    return $numeric;
                }
            }
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return $timestamp;
            }
        }
        return 0;
    }

    private function build_candidate_compliance_issues($candidate_id, $user_id) {
        $issues = [];
        $label_map = [
            'dbs' => 'DBS',
            'id' => 'Photo ID',
            'cv' => 'CV',
        ];
        foreach ($this->get_candidate_doc_types() as $doc_type) {
            $doc = $this->get_candidate_doc_status($candidate_id, $user_id, $doc_type);
            $doc_label = $label_map[$doc_type] ?? strtoupper((string) $doc_type);
            $status = (string) ($doc['doc_status'] ?? 'not_uploaded');
            if ($status === 'not_uploaded') {
                $issues[] = [
                    'key' => 'missing_' . $doc_type,
                    'message' => $doc_label . ' is missing.',
                ];
            } elseif ($status === 'rejected') {
                $reason = trim((string) ($doc['review_reason'] ?? ''));
                $issues[] = [
                    'key' => 'rejected_' . $doc_type,
                    'message' => $doc_label . ' was rejected' . ($reason !== '' ? (': ' . $reason) : '.'),
                ];
            }
        }

        $profile_completion = (int) get_user_meta((int) $user_id, 'cmn_profile_completion_pct', true);
        if ($profile_completion < 1) {
            $profile_completion = (int) get_post_meta((int) $candidate_id, 'cmn_profile_completion_pct', true);
        }
        if ($profile_completion < 1) {
            $profile_completion = (int) $this->update_candidate_profile_completion($candidate_id, $user_id);
        }
        if ($profile_completion < 100) {
            $issues[] = [
                'key' => 'profile_incomplete',
                'message' => 'Profile is ' . $profile_completion . '% complete.',
            ];
        }

        $expiry_ts = $this->get_candidate_dbs_expiry_timestamp($candidate_id, $user_id);
        if ($expiry_ts > 0) {
            $today_ts = current_time('timestamp');
            $soon_ts = strtotime('+30 days', $today_ts);
            if ($expiry_ts <= $today_ts) {
                $issues[] = [
                    'key' => 'dbs_expired',
                    'message' => 'DBS has expired. Please upload an updated DBS document.',
                ];
            } elseif ($expiry_ts <= $soon_ts) {
                $issues[] = [
                    'key' => 'dbs_expiring_soon',
                    'message' => 'DBS expires on ' . date_i18n('M j, Y', $expiry_ts) . '.',
                ];
            }
        }

        return $issues;
    }

    private function should_send_compliance_reminder_issue($user_id, $issue_key, $now_ts) {
        $meta_key = 'cmn_compliance_reminder_sent_' . sanitize_key((string) $issue_key);
        $last_sent = (string) get_user_meta((int) $user_id, $meta_key, true);
        if ($last_sent === '') {
            return true;
        }
        $last_ts = strtotime($last_sent);
        if ($last_ts === false) {
            return true;
        }
        return ($now_ts - $last_ts) >= DAY_IN_SECONDS;
    }

    private function mark_compliance_reminder_issue_sent($user_id, $issue_key) {
        $meta_key = 'cmn_compliance_reminder_sent_' . sanitize_key((string) $issue_key);
        update_user_meta((int) $user_id, $meta_key, current_time('mysql'));
    }

    public function run_compliance_reminders() {
        if (get_transient('cmn_compliance_reminder_lock')) {
            return;
        }
        set_transient('cmn_compliance_reminder_lock', 1, 300);
        $now_ts = time();
        update_option('cmn_compliance_reminder_last_run_ts', $now_ts, false);

        try {
            $candidates = $this->get_candidate_users_for_compliance_reminders();
            if (!$candidates) {
                return;
            }
            foreach ($candidates as $candidate_user) {
                $user_id = isset($candidate_user->ID) ? (int) $candidate_user->ID : 0;
                $email = isset($candidate_user->user_email) ? sanitize_email((string) $candidate_user->user_email) : '';
                if ($user_id < 1 || $email === '' || !$this->is_candidate_user($user_id)) {
                    continue;
                }
                $candidate_id = (int) $this->get_candidate_id_for_user($user_id);
                if ($candidate_id < 1) {
                    continue;
                }

                $issues = $this->build_candidate_compliance_issues($candidate_id, $user_id);
                if (!$issues) {
                    continue;
                }

                $sendable_issues = [];
                foreach ($issues as $issue) {
                    $issue_key = (string) ($issue['key'] ?? '');
                    if ($issue_key === '') {
                        continue;
                    }
                    if ($this->should_send_compliance_reminder_issue($user_id, $issue_key, $now_ts)) {
                        $sendable_issues[] = $issue;
                    }
                }
                if (!$sendable_issues) {
                    continue;
                }

                $portal_profile_link = add_query_arg(['candidate' => 'profile'], $this->get_portal_base_url());
                $issue_lines = [];
                foreach ($sendable_issues as $issue) {
                    $issue_lines[] = '- ' . trim((string) ($issue['message'] ?? 'Compliance item requires attention.'));
                }
                $display_name = trim((string) ($candidate_user->display_name ?? ''));
                if ($display_name === '') {
                    $display_name = 'there';
                }
                $subject = 'Compliance reminder: action needed';
                $message = "Hi {$display_name},\n\nPlease review the following compliance item(s):\n" . implode("\n", $issue_lines) . "\n\nUpdate your profile here:\n{$portal_profile_link}\n\nCoverMeNow ONE";
                $this->send_candidate_email($email, $subject, $message, [
                    'type' => 'candidate_status_update',
                    'user_id' => $user_id,
                    'related_candidate_id' => $candidate_id,
                ]);

                $count = count($sendable_issues);
                $notification_message = $count === 1
                    ? (string) ($sendable_issues[0]['message'] ?? 'Compliance action required.')
                    : sprintf('%d compliance items need your attention.', $count);
                $this->add_notification(
                    $user_id,
                    'candidate_compliance_reminder',
                    'Compliance reminder',
                    $notification_message,
                    $portal_profile_link
                );

                foreach ($sendable_issues as $issue) {
                    $this->mark_compliance_reminder_issue_sent($user_id, (string) ($issue['key'] ?? ''));
                }
            }
        } finally {
            delete_transient('cmn_compliance_reminder_lock');
        }
    }

    private function has_booking_for_candidate_date($candidate_id, $date) {
        if (!$candidate_id || !$date) {
            return false;
        }
        $existing = get_posts([
            'post_type' => 'cmn_booking',
            'posts_per_page' => 10,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'cmn_candidate_id',
                    'value' => (int) $candidate_id,
                ],
                [
                    'key' => 'cmn_date',
                    'value' => $date,
                ],
            ],
        ]);
        if (!$existing) {
            return false;
        }
        foreach ($existing as $booking_id) {
            $status = get_post_meta($booking_id, 'cmn_status', true);
            if (!in_array($status, ['declined', 'expired'], true)) {
                return true;
            }
        }
        return false;
    }

    private function create_booking_from_request($request, $school_id, $created_by) {
        $candidate_id = (int) ($request['candidate_id'] ?? 0);
        $requested_date = sanitize_text_field($request['requested_date'] ?? '');
        if (!$candidate_id || !$requested_date || !$school_id) {
            return 0;
        }
        $candidate = get_post($candidate_id);
        $role_meta = (array) get_post_meta($candidate_id, 'cmn_roles', true);
        $role = $role_meta ? $role_meta[0] : 'Candidate';
        $location = get_post_meta($school_id, 'cmn_location', true);
        $title = $candidate ? $candidate->post_title : 'Candidate';
        $post_id = wp_insert_post([
            'post_type' => 'cmn_booking',
            'post_title' => $title . ' · ' . $requested_date,
            'post_status' => 'publish',
        ]);
        if (is_wp_error($post_id) || !$post_id) {
            return 0;
        }
        update_post_meta($post_id, 'cmn_date', $requested_date);
        update_post_meta($post_id, 'cmn_start_date', $requested_date);
        update_post_meta($post_id, 'cmn_status', 'approved');
        update_post_meta($post_id, 'cmn_role', $role);
        update_post_meta($post_id, 'cmn_location', $location);
        update_post_meta($post_id, 'cmn_school_id', $school_id);
        update_post_meta($post_id, 'cmn_candidate_id', $candidate_id);
        update_post_meta($post_id, 'cmn_booking_type', 'morning');
        update_post_meta($post_id, 'cmn_source', 'availability_request');
        update_post_meta($post_id, 'cmn_created_by', (int) $created_by);
        update_post_meta($post_id, 'cmn_created_at', time());
        return (int) $post_id;
    }

    private function get_school_primary_contact_email($school_id) {
        if (!$school_id) {
            return '';
        }
        $email = get_post_meta($school_id, 'cmn_primary_contact_email', true);
        if (!$email) {
            $email = get_post_meta($school_id, 'cmn_contact1_email', true);
        }
        if (!$email) {
            $email = get_post_meta($school_id, 'cmn_email', true);
        }
        return sanitize_email($email);
    }

    private function get_school_user_id_for_request($request, $school_id) {
        $user_id = isset($request['school_user_id']) ? (int) $request['school_user_id'] : 0;
        if ($user_id) {
            return $user_id;
        }
        $email = $this->get_school_primary_contact_email($school_id);
        if ($email) {
            $user = get_user_by('email', $email);
            if ($user) {
                return (int) $user->ID;
            }
        }
        return 0;
    }

    private function count_available_candidates() {
        global $wpdb;
        $table = $this->get_candidate_availability_table();
        $tomorrow = $this->get_tomorrow_date();
        if (!$table || !$tomorrow) {
            return 0;
        }
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE available_date = %s AND available_type = %s",
            $tomorrow,
            'morning'
        ));
        return (int) $count;
    }

    private function render_pending_tables() {
        $schools = $this->get_pending_items('cmn_school');
        $candidates = $this->get_pending_items('cmn_candidate');

        $out = '<div class="cmn-approval-block">';
        $out .= '<h4>Schools</h4>';
        $out .= $this->render_approval_table($schools, 'school');
        $out .= '<h4>Candidates</h4>';
        $out .= $this->render_approval_table($candidates, 'candidate');
        $out .= '</div>';
        return $out;
    }

    private function get_pending_items($post_type) {
        return get_posts([
            'post_type' => $post_type,
            'posts_per_page' => 5,
            'meta_query' => [
                [
                    'key' => 'cmn_status',
                    'value' => 'pending',
                ],
            ],
        ]);
    }

    private function render_approval_table($items, $entity_type) {
        if (!$items) {
            return '<div class="cmn-empty">No pending items.</div>';
        }
        $out = '<table class="cmn-approval-table"><thead><tr><th>Name</th><th>Action</th></tr></thead><tbody>';
        foreach ($items as $item) {
            $out .= '<tr><td>' . esc_html($item->post_title) . '</td><td>';
            $out .= $this->render_status_form($entity_type, $item->ID, 'approved', 'Approve');
            $out .= $this->render_status_form($entity_type, $item->ID, 'rejected', 'Reject');
            $out .= '</td></tr>';
        }
        $out .= '</tbody></table>';
        return $out;
    }

    private function render_status_form($entity_type, $entity_id, $status, $label) {
        $out = '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="cmn-inline">';
        $out .= wp_nonce_field('cmn_update_status', 'cmn_update_status_nonce', true, false);
        $out .= '<input type="hidden" name="action" value="cmn_update_status">';
        $out .= '<input type="hidden" name="cmn_entity_type" value="' . esc_attr($entity_type) . '">';
        $out .= '<input type="hidden" name="cmn_entity_id" value="' . esc_attr($entity_id) . '">';
        $out .= '<input type="hidden" name="cmn_status" value="' . esc_attr($status) . '">';
        $out .= '<button class="cmn-ghost" type="submit">' . esc_html($label) . '</button>';
        $out .= '</form>';
        return $out;
    }

    private function render_recent_list($post_type) {
        $items = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => 4,
        ]);
        if (!$items) {
            return '<div class="cmn-list-item"><strong>No records yet</strong><span>Add new records to see them here.</span></div>';
        }
        $out = '';
        foreach ($items as $item) {
            $status = get_post_meta($item->ID, 'cmn_status', true);
            $out .= '<div class="cmn-list-item"><strong>' . esc_html($item->post_title) . '</strong><span>Status: ' . esc_html($status ?: 'n/a') . '</span></div>';
        }
        return $out;
    }

    private function render_latest_bookings() {
        $items = get_posts([
            'post_type' => 'cmn_booking',
            'posts_per_page' => 4,
        ]);
        if (!$items) {
            return '<div class="cmn-list-item"><strong>No bookings yet</strong><span>Create or approve bookings.</span></div>';
        }
        $out = '';
        foreach ($items as $item) {
            $date = get_post_meta($item->ID, 'cmn_date', true);
            $role = get_post_meta($item->ID, 'cmn_role', true);
            $out .= '<div class="cmn-list-item"><strong>' . esc_html($role ?: $item->post_title) . '</strong><span>' . esc_html($date) . '</span></div>';
        }
        return $out;
    }

    private function count_by_status($post_type, $status) {
        $q = new WP_Query([
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'cmn_status',
                    'value' => $status,
                ],
            ],
            'fields' => 'ids',
        ]);
        return $q->found_posts;
    }

    private function count_bookings_today() {
        $today = date('Y-m-d');
        $q = new WP_Query([
            'post_type' => 'cmn_booking',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'cmn_date',
                    'value' => $today,
                ],
            ],
            'fields' => 'ids',
        ]);
        return $q->found_posts;
    }

    private function count_bookings_by_status($status) {
        $status = sanitize_text_field($status);
        if ($status === '') {
            return 0;
        }
        $q = new WP_Query([
            'post_type' => 'cmn_booking',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'cmn_status',
                    'value' => $status,
                ],
            ],
            'fields' => 'ids',
        ]);
        return $q->found_posts;
    }

    private function count_total_posts($post_type) {
        $post_type = sanitize_text_field($post_type);
        if ($post_type === '') {
            return 0;
        }
        $q = new WP_Query([
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'fields' => 'ids',
        ]);
        return $q->found_posts;
    }

    private function get_booking_feedback_analytics($filter = 'all', $limit = 50) {
        global $wpdb;
        $table = $this->get_booking_feedback_table();
        $limit = max(10, (int) $limit);
        $filter = sanitize_key((string) $filter);
        if (!in_array($filter, ['all', 'low'], true)) {
            $filter = 'all';
        }
        $where = $filter === 'low' ? 'WHERE stars_overall <= 2' : '';
        $rows = (array) $wpdb->get_results(
            "SELECT * FROM {$table} {$where} ORDER BY created_at DESC, id DESC LIMIT {$limit}",
            ARRAY_A
        );
        $all_rows = (array) $wpdb->get_results(
            "SELECT * FROM {$table} ORDER BY created_at DESC, id DESC LIMIT 500",
            ARRAY_A
        );

        $school_stats = [];
        $candidate_stats = [];
        $avg_total = 0.0;
        $avg_count = 0;
        $low_count = 0;
        foreach ($all_rows as $row) {
            $overall = (int) ($row['stars_overall'] ?? 0);
            if ($overall > 0) {
                $avg_total += $overall;
                $avg_count++;
            }
            if ($overall <= 2) {
                $low_count++;
            }
            $booking_id = (int) ($row['booking_id'] ?? 0);
            if (!$booking_id) {
                continue;
            }
            $school_id = (int) get_post_meta($booking_id, 'cmn_school_id', true);
            $candidate_id = (int) get_post_meta($booking_id, 'cmn_candidate_id', true);
            if ($school_id) {
                if (!isset($school_stats[$school_id])) {
                    $school_stats[$school_id] = [
                        'id' => $school_id,
                        'label' => (string) get_the_title($school_id),
                        'count' => 0,
                        'total' => 0.0,
                    ];
                }
                $school_stats[$school_id]['count']++;
                $school_stats[$school_id]['total'] += $overall;
            }
            if ($candidate_id) {
                if (!isset($candidate_stats[$candidate_id])) {
                    $candidate_stats[$candidate_id] = [
                        'id' => $candidate_id,
                        'label' => (string) get_the_title($candidate_id),
                        'count' => 0,
                        'total' => 0.0,
                    ];
                }
                $candidate_stats[$candidate_id]['count']++;
                $candidate_stats[$candidate_id]['total'] += $overall;
            }
        }

        $normalize_trends = function ($items) {
            $rows = [];
            foreach ($items as $item) {
                $count = (int) ($item['count'] ?? 0);
                if ($count < 1) {
                    continue;
                }
                $rows[] = [
                    'id' => (int) ($item['id'] ?? 0),
                    'label' => (string) ($item['label'] ?? ''),
                    'count' => $count,
                    'avg' => round(((float) ($item['total'] ?? 0.0)) / $count, 2),
                ];
            }
            usort($rows, function ($a, $b) {
                if ($a['avg'] === $b['avg']) {
                    return $b['count'] <=> $a['count'];
                }
                return $a['avg'] <=> $b['avg'];
            });
            return array_slice($rows, 0, 8);
        };

        $recent = [];
        foreach ($rows as $row) {
            $booking_id = (int) ($row['booking_id'] ?? 0);
            $school_id = $booking_id ? (int) get_post_meta($booking_id, 'cmn_school_id', true) : 0;
            $candidate_id = $booking_id ? (int) get_post_meta($booking_id, 'cmn_candidate_id', true) : 0;
            $tags = [];
            if (!empty($row['tags'])) {
                $decoded = json_decode((string) $row['tags'], true);
                if (is_array($decoded)) {
                    foreach ($decoded as $tag) {
                        $tag = sanitize_text_field((string) $tag);
                        if ($tag !== '') {
                            $tags[] = $tag;
                        }
                    }
                }
            }
            $recent[] = [
                'id' => (int) ($row['id'] ?? 0),
                'booking_id' => $booking_id,
                'school_name' => $school_id ? (string) get_the_title($school_id) : 'School',
                'candidate_name' => $candidate_id ? (string) get_the_title($candidate_id) : 'Candidate',
                'rated_entity_type' => (string) ($row['rated_entity_type'] ?? ''),
                'stars_overall' => (int) ($row['stars_overall'] ?? 0),
                'tags' => $tags,
                'comment' => (string) ($row['comment'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'is_low' => (int) ($row['stars_overall'] ?? 0) <= 2,
            ];
        }

        return [
            'filter' => $filter,
            'totals' => [
                'feedback_count' => count($all_rows),
                'low_count' => $low_count,
                'avg_overall' => $avg_count > 0 ? round($avg_total / $avg_count, 2) : 0.0,
            ],
            'recent' => $recent,
            'school_trends' => $normalize_trends($school_stats),
            'candidate_trends' => $normalize_trends($candidate_stats),
        ];
    }

    private function count_missing_dbs() {
        $q = new WP_Query([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                [
                    'relation' => 'OR',
                    [
                        'key' => 'cmn_dbs_file',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key' => 'cmn_dbs_file',
                        'value' => '',
                        'compare' => '=',
                    ],
                ],
                [
                    'key' => 'cmn_no_dbs',
                    'value' => '1',
                    'compare' => '!=',
                ],
            ],
        ]);
        return $q->found_posts;
    }

    private function get_candidates_awaiting_document_review($limit = 10) {
        $limit = max(1, (int) $limit);
        $current_user_id = get_current_user_id();
        $args = [
            'post_type' => 'cmn_candidate',
            'posts_per_page' => 300,
            'fields' => 'ids',
            'orderby' => 'modified',
            'order' => 'DESC',
        ];
        if ($this->is_account_manager_user($current_user_id) && !$this->is_admin_user($current_user_id) && !$this->is_staff_role($current_user_id)) {
            $assigned_candidates = $this->get_assigned_candidate_ids_for_account_manager($current_user_id);
            if (!$assigned_candidates) {
                return [];
            }
            $args['post__in'] = $assigned_candidates;
        }
        $candidate_ids = get_posts($args);
        if (!$candidate_ids) {
            return [];
        }
        $rows = [];
        foreach ($candidate_ids as $candidate_id) {
            $candidate_id = (int) $candidate_id;
            $candidate_user_id = (int) $this->get_candidate_user_id($candidate_id);
            if ($candidate_user_id < 1) {
                continue;
            }
            $pending_docs = [];
            $latest_uploaded = 0;
            foreach ($this->get_candidate_doc_types() as $doc_type) {
                $status = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, $doc_type);
                if (($status['doc_status'] ?? '') !== 'pending') {
                    continue;
                }
                if ($doc_type === 'dbs') {
                    $pending_docs[] = 'DBS';
                } elseif ($doc_type === 'id') {
                    $pending_docs[] = 'Photo ID';
                } else {
                    $pending_docs[] = strtoupper((string) $doc_type);
                }
                $uploaded_at = (string) ($status['uploaded_at'] ?? '');
                $uploaded_ts = $uploaded_at !== '' ? strtotime($uploaded_at) : false;
                if ($uploaded_ts !== false && $uploaded_ts > $latest_uploaded) {
                    $latest_uploaded = $uploaded_ts;
                }
            }
            if (!$pending_docs) {
                continue;
            }
            $rows[] = [
                'candidate_id' => $candidate_id,
                'candidate_name' => (string) get_the_title($candidate_id),
                'candidate_email' => (string) get_post_meta($candidate_id, 'cmn_email', true),
                'pending_docs' => $pending_docs,
                'uploaded_at_ts' => $latest_uploaded,
                'uploaded_at_label' => $latest_uploaded > 0 ? date_i18n('M j, Y g:ia', $latest_uploaded) : '—',
            ];
        }
        usort($rows, function ($a, $b) {
            return (int) ($b['uploaded_at_ts'] ?? 0) <=> (int) ($a['uploaded_at_ts'] ?? 0);
        });
        return array_slice($rows, 0, $limit);
    }

    private function count_missing_id() {
        $q = new WP_Query([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'cmn_id_file',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => 'cmn_id_file',
                    'value' => '',
                    'compare' => '=',
                ],
            ],
        ]);
        return $q->found_posts;
    }

    private function count_candidates_by_role($role) {
        $role = sanitize_text_field($role);
        if ($role === '') {
            return 0;
        }
        $q = new WP_Query([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'cmn_roles',
                    'value' => $role,
                    'compare' => 'LIKE',
                ],
            ],
        ]);
        return $q->found_posts;
    }

    private function count_candidates_by_location($location) {
        $location = sanitize_text_field($location);
        if ($location === '') {
            return 0;
        }
        $q = new WP_Query([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'cmn_location',
                    'value' => $location,
                    'compare' => 'LIKE',
                ],
            ],
        ]);
        return $q->found_posts;
    }

    private function get_activity_items($types, $limit) {
        global $wpdb;
        $types = array_map([$this, 'normalize_activity_type'], array_filter((array) $types));
        if (!$types) {
            return [];
        }
        $table = $this->get_activity_table();
        $placeholders = implode(',', array_fill(0, count($types), '%s'));
        $sql = "SELECT * FROM {$table} WHERE activity_type IN ({$placeholders}) AND (activity_type != 'task' OR completed_at IS NULL) ORDER BY created_at DESC LIMIT %d";
        $params = array_merge($types, [(int) $limit]);
        $rows = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
        if ($rows) {
            return $rows;
        }
        $legacy_types = array_map(function ($t) {
            return $t === 'task' ? 'todo' : $t;
        }, $types);
        $legacy = get_posts([
            'post_type' => 'cmn_activity',
            'posts_per_page' => $limit,
            'meta_query' => [
                [
                    'key' => 'cmn_activity_type',
                    'value' => $legacy_types,
                    'compare' => 'IN',
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        $rows = [];
        foreach ($legacy as $activity) {
            $rows[] = [
                'id' => $activity->ID,
                'activity_type' => $this->normalize_activity_type(get_post_meta($activity->ID, 'cmn_activity_type', true)),
                'subject' => $activity->post_title,
                'notes' => get_post_meta($activity->ID, 'cmn_activity_content', true),
                'due_date' => get_post_meta($activity->ID, 'cmn_activity_date', true),
                'duration_minutes' => get_post_meta($activity->ID, 'cmn_activity_duration', true),
                'completed_at' => get_post_meta($activity->ID, 'cmn_activity_status', true) === 'done' ? get_post_time('Y-m-d H:i:s', true, $activity) : null,
            ];
        }
        return $rows;
    }

    private function get_pending_requests($limit, $portal_url) {
        $items = [];
        $schools = get_posts([
            'post_type' => 'cmn_school',
            'posts_per_page' => $limit,
            'meta_query' => [
                [
                    'key' => 'cmn_status',
                    'value' => 'pending',
                ],
            ],
        ]);
        foreach ($schools as $school) {
            $location = get_post_meta($school->ID, 'cmn_location', true);
            $school_code = get_post_meta($school->ID, 'cmn_school_id', true);
            $items[] = [
                'title' => $school->post_title,
                'subtitle' => 'School access request',
                'meta' => $location ? 'Pending · ' . $location : 'Pending',
                'url' => add_query_arg(['view' => 'schools', 'school_id' => $school_code], $portal_url),
            ];
        }

        $candidates = get_posts([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => $limit,
            'meta_query' => [
                [
                    'key' => 'cmn_status',
                    'value' => 'pending',
                ],
            ],
        ]);
        foreach ($candidates as $candidate) {
            $roles = (array) get_post_meta($candidate->ID, 'cmn_roles', true);
            $role_label = $roles ? $roles[0] : 'Candidate';
            $items[] = [
                'title' => $candidate->post_title,
                'subtitle' => 'Candidate application',
                'meta' => 'Pending · ' . $role_label,
                'url' => add_query_arg(['view' => 'candidates', 'candidate_id' => $candidate->ID], $portal_url),
            ];
        }

        $bookings = get_posts([
            'post_type' => 'cmn_booking',
            'posts_per_page' => $limit,
            'meta_query' => [
                [
                    'key' => 'cmn_status',
                    'value' => 'requested',
                ],
            ],
        ]);
        foreach ($bookings as $booking) {
            $role = get_post_meta($booking->ID, 'cmn_role', true);
            $date = get_post_meta($booking->ID, 'cmn_start_date', true) ?: get_post_meta($booking->ID, 'cmn_date', true);
            $items[] = [
                'title' => $booking->post_title,
                'subtitle' => $role ? 'Cover request · ' . $role : 'Cover request',
                'meta' => $date ? 'Start · ' . $date : 'Requested',
                'url' => add_query_arg(['view' => 'bookings'], $portal_url),
            ];
        }

        if (count($items) > $limit) {
            $items = array_slice($items, 0, $limit);
        }
        return $items;
    }

    private function get_active_placements($limit) {
        $items = [];
        $bookings = get_posts([
            'post_type' => 'cmn_booking',
            'posts_per_page' => $limit,
            'meta_query' => [
                [
                    'key' => 'cmn_status',
                    'value' => 'approved',
                ],
            ],
        ]);
        $today = date('Y-m-d');
        foreach ($bookings as $booking) {
            $school_id = (int) get_post_meta($booking->ID, 'cmn_school_id', true);
            $school_name = $school_id ? get_the_title($school_id) : 'Placement';
            $role = get_post_meta($booking->ID, 'cmn_role', true);
            $date = get_post_meta($booking->ID, 'cmn_start_date', true) ?: get_post_meta($booking->ID, 'cmn_date', true);
            $meta = $date === $today ? 'Today' : ($date ?: 'Scheduled');
            $items[] = [
                'title' => $school_name,
                'subtitle' => $role ?: 'Placement',
                'meta' => $meta,
            ];
        }
        return $items;
    }

    private function get_recent_interactions($limit) {
        global $wpdb;
        $items = [];
        $table = $this->get_activity_table();
        $activities = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d",
            (int) $limit
        ), ARRAY_A);
        if (!$activities) {
            $legacy = get_posts([
                'post_type' => 'cmn_activity',
                'posts_per_page' => $limit,
                'orderby' => 'date',
                'order' => 'DESC',
            ]);
            foreach ($legacy as $activity) {
                $type = get_post_meta($activity->ID, 'cmn_activity_type', true);
                $related_type = get_post_meta($activity->ID, 'cmn_related_type', true);
                $related_id = (int) get_post_meta($activity->ID, 'cmn_related_id', true);
                $entity_title = $related_id ? get_the_title($related_id) : '';
                $subtitle = $type ? ucfirst($type) : 'Interaction';
                $content = get_post_meta($activity->ID, 'cmn_activity_content', true);
                if ($content) {
                    $subtitle .= ' · ' . wp_trim_words($content, 5, '…');
                }
                $time = human_time_diff(get_post_time('U', true, $activity), current_time('timestamp', true)) . ' ago';
                $items[] = [
                    'title' => $entity_title ?: $activity->post_title,
                    'subtitle' => $subtitle,
                    'meta' => $time,
                ];
            }
            return $items;
        }
        foreach ($activities as $activity) {
            $type = $activity['activity_type'] ?? '';
            $related_type = $activity['entity_type'] ?? '';
            $related_ref = $activity['entity_ref'] ?? '';
            $entity_title = '';
            if ($related_type === 'school') {
                $entity_title = $this->get_school_name_by_domain($related_ref);
            }
            $subtitle = $type ? ucfirst($type) : 'Interaction';
            $content = $activity['notes'] ?? '';
            if ($content) {
                $subtitle .= ' · ' . wp_trim_words($content, 5, '…');
            }
            $time = human_time_diff(strtotime($activity['created_at'] ?? 'now'), current_time('timestamp', true)) . ' ago';
            $items[] = [
                'title' => $entity_title ?: ($activity['subject'] ?? 'Interaction'),
                'subtitle' => $subtitle,
                'meta' => $time,
            ];
        }
        return $items;
    }

    public function render_register_school_shortcode() {
        $portal_home = home_url('/covermenow-one');
        $submitted = isset($_GET['cmn_submitted']) && $_GET['cmn_submitted'] === '1';
        $client_complete = isset($_GET['cmn_client']) && $_GET['cmn_client'] === '1';
        $token = isset($_GET['token']) ? sanitize_text_field(wp_unslash($_GET['token'])) : '';
        $token_data = $token ? $this->validate_client_token($token) : null;
        $token_invalid = $token && !$token_data;
        $token_domain = $token_data['school_email_domain'] ?? '';
        $token_school_id = $token_domain ? $this->get_school_post_id_by_domain($token_domain) : 0;
        $is_token_mode = $token_data && $token_school_id;
        $prefill = [
            'school_name' => $is_token_mode ? get_the_title($token_school_id) : '',
            'location' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_location', true) : '',
            'email' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_email', true) : '',
            'contact_name' => $is_token_mode ? (get_post_meta($token_school_id, 'cmn_primary_contact_name', true) ?: get_post_meta($token_school_id, 'cmn_contact1', true)) : '',
            'contact_role' => $is_token_mode ? (get_post_meta($token_school_id, 'cmn_primary_contact_role', true) ?: get_post_meta($token_school_id, 'cmn_contact_role', true)) : '',
            'phone' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_phone', true) : '',
            'website' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_website', true) : '',
            'address1' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_address_line1', true) : '',
            'address2' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_address_line2', true) : '',
            'town' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_town', true) : '',
            'county' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_county', true) : '',
            'postcode' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_postcode', true) : '',
            'school_type' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_school_type', true) : '',
            'pupil_count' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_pupil_count', true) : '',
            'supply_frequency' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_supply_frequency', true) : '',
            'use_agencies' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_use_agencies', true) : '',
            'agency_count' => $is_token_mode ? get_post_meta($token_school_id, 'cmn_agency_count', true) : '',
        ];
        ob_start();
        ?>
        <section class="cmn-portal cmn-portal-bg">
            <a class="cmn-back-link" href="<?php echo esc_url($portal_home); ?>">Back to portal</a>
            <header class="cmn-portal-header">
                <h2>School Registration</h2>
                <p><?php echo $is_token_mode ? 'Complete your school profile.' : 'Request access for your school. We will review and approve manually.'; ?></p>
            </header>
            <div class="cmn-register-shell">
                <div class="cmn-register-brand">
                    <h3>
                        <span class="brand-main">CoverMeNow</span>
                        <span class="brand-accent">ONE</span>
                    </h3>
                    <p><span class="tag-white">One</span> <span class="brand-accent">system</span>. <span class="tag-white">Total</span> <span class="brand-accent">cover</span>.</p>
                </div>

                <?php if ($token_invalid) : ?>
                    <div class="cmn-register-success">
                        <h4>Invalid or expired link</h4>
                        <p>Please contact your account manager for a new link.</p>
                    </div>
                <?php elseif ($submitted) : ?>
                    <div class="cmn-register-success">
                        <?php if ($client_complete) : ?>
                            <h4>Profile complete</h4>
                            <p>Thank you. Your client profile is now complete.</p>
                        <?php else : ?>
                            <h4>Thank you for your interest</h4>
                            <p>Applications are processed within 24 hours.</p>
                            <p>If you have any questions, email school@covermenow.co.uk or call 07438 766 532.</p>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <div class="cmn-register-benefits">
                        <div class="cmn-hero-banner">
                            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/school-hero.png'); ?>" alt="">
                        </div>
                    </div>

                    <form class="cmn-form cmn-register-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('cmn_register_school', 'cmn_register_school_nonce'); ?>
                        <input type="hidden" name="action" value="cmn_register_school">
                        <?php if ($is_token_mode) : ?>
                            <input type="hidden" name="cmn_client_token" value="<?php echo esc_attr($token); ?>">
                        <?php endif; ?>
                        <div class="cmn-required-note">* Required</div>
                        <div class="cmn-form-grid">
                            <label>School Name *<input type="text" name="cmn_school_name" placeholder="School Name" required value="<?php echo esc_attr($prefill['school_name']); ?>"></label>
                            <label>Location *<input type="text" name="cmn_location" placeholder="Town / City" required value="<?php echo esc_attr($prefill['location']); ?>"></label>
                            <label>Primary Contact *<input type="text" name="cmn_contact1" placeholder="Full Name" required value="<?php echo esc_attr($prefill['contact_name']); ?>"></label>
                            <label>Role / Position *<input type="text" name="cmn_contact_role" placeholder="Headteacher / HR / Business Manager" required value="<?php echo esc_attr($prefill['contact_role']); ?>"></label>
                            <label>Email *<input type="email" name="cmn_email" placeholder="Email" required value="<?php echo esc_attr($prefill['email']); ?>"<?php echo $is_token_mode ? ' readonly' : ''; ?>></label>
                            <label>Phone *<input type="tel" name="cmn_phone" placeholder="Phone" required value="<?php echo esc_attr($prefill['phone']); ?>"></label>
                            <label>Website<input type="url" name="cmn_website" placeholder="Website" value="<?php echo esc_attr($prefill['website']); ?>"></label>
                        </div>

                        <div class="cmn-form-group">
                            <span class="cmn-form-label">School Address</span>
                            <div class="cmn-form-grid">
                                <label>Address Line 1 *<input type="text" name="cmn_address_line1" placeholder="Address Line 1" required value="<?php echo esc_attr($prefill['address1']); ?>"></label>
                                <label>Address Line 2<input type="text" name="cmn_address_line2" placeholder="Address Line 2" value="<?php echo esc_attr($prefill['address2']); ?>"></label>
                                <label>Town / City *<input type="text" name="cmn_town" placeholder="Town / City" required value="<?php echo esc_attr($prefill['town']); ?>"></label>
                                <label>County *<input type="text" name="cmn_county" placeholder="County" required value="<?php echo esc_attr($prefill['county']); ?>"></label>
                                <label>Post Code *<input type="text" name="cmn_postcode" placeholder="Post Code" required value="<?php echo esc_attr($prefill['postcode']); ?>"></label>
                            </div>
                        </div>

                        <div class="cmn-form-group">
                            <span class="cmn-form-label">School Profile</span>
                            <div class="cmn-form-grid">
                                <label>What type of school are you registering?
                                    <select name="cmn_school_type" required>
                                        <option value="" disabled <?php echo $prefill['school_type'] === '' ? 'selected' : ''; ?>>Select school type</option>
                                        <option value="Primary"<?php echo $prefill['school_type'] === 'Primary' ? ' selected' : ''; ?>>Primary</option>
                                        <option value="Secondary"<?php echo $prefill['school_type'] === 'Secondary' ? ' selected' : ''; ?>>Secondary</option>
                                        <option value="All-through"<?php echo $prefill['school_type'] === 'All-through' ? ' selected' : ''; ?>>All-through</option>
                                        <option value="SEN / Alternative Provision"<?php echo $prefill['school_type'] === 'SEN / Alternative Provision' ? ' selected' : ''; ?>>SEN / Alternative Provision</option>
                                        <option value="Academy (single school)"<?php echo $prefill['school_type'] === 'Academy (single school)' ? ' selected' : ''; ?>>Academy (single school)</option>
                                        <option value="Multi-Academy Trust"<?php echo $prefill['school_type'] === 'Multi-Academy Trust' ? ' selected' : ''; ?>>Multi-Academy Trust</option>
                                        <option value="Other"<?php echo $prefill['school_type'] === 'Other' ? ' selected' : ''; ?>>Other</option>
                                    </select>
                                </label>
                                <label>Approximately how many pupils are on roll?
                                    <select name="cmn_pupil_count" required>
                                        <option value="" disabled <?php echo $prefill['pupil_count'] === '' ? 'selected' : ''; ?>>Select pupil count</option>
                                        <option value="Under 200"<?php echo $prefill['pupil_count'] === 'Under 200' ? ' selected' : ''; ?>>Under 200</option>
                                        <option value="200–500"<?php echo $prefill['pupil_count'] === '200–500' ? ' selected' : ''; ?>>200–500</option>
                                        <option value="500–1,000"<?php echo $prefill['pupil_count'] === '500–1,000' ? ' selected' : ''; ?>>500–1,000</option>
                                        <option value="Over 1,000"<?php echo $prefill['pupil_count'] === 'Over 1,000' ? ' selected' : ''; ?>>Over 1,000</option>
                                    </select>
                                </label>
                                <label>How often do you use supply staff?
                                    <select name="cmn_supply_frequency" required>
                                        <option value="" disabled <?php echo $prefill['supply_frequency'] === '' ? 'selected' : ''; ?>>Select frequency</option>
                                        <option value="Daily"<?php echo $prefill['supply_frequency'] === 'Daily' ? ' selected' : ''; ?>>Daily</option>
                                        <option value="Several times a week"<?php echo $prefill['supply_frequency'] === 'Several times a week' ? ' selected' : ''; ?>>Several times a week</option>
                                        <option value="Weekly"<?php echo $prefill['supply_frequency'] === 'Weekly' ? ' selected' : ''; ?>>Weekly</option>
                                        <option value="Monthly"<?php echo $prefill['supply_frequency'] === 'Monthly' ? ' selected' : ''; ?>>Monthly</option>
                                        <option value="Only in emergencies"<?php echo $prefill['supply_frequency'] === 'Only in emergencies' ? ' selected' : ''; ?>>Only in emergencies</option>
                                        <option value="Rarely / never"<?php echo $prefill['supply_frequency'] === 'Rarely / never' ? ' selected' : ''; ?>>Rarely / never</option>
                                    </select>
                                </label>
                                <label>Do you currently use supply agencies?
                                    <select name="cmn_use_agencies" required>
                                        <option value="" disabled <?php echo $prefill['use_agencies'] === '' ? 'selected' : ''; ?>>Select option</option>
                                        <option value="Yes – regularly"<?php echo $prefill['use_agencies'] === 'Yes – regularly' ? ' selected' : ''; ?>>Yes – regularly</option>
                                        <option value="Yes – occasionally"<?php echo $prefill['use_agencies'] === 'Yes – occasionally' ? ' selected' : ''; ?>>Yes – occasionally</option>
                                        <option value="Only as a last resort"<?php echo $prefill['use_agencies'] === 'Only as a last resort' ? ' selected' : ''; ?>>Only as a last resort</option>
                                        <option value="No – we do not use agencies"<?php echo $prefill['use_agencies'] === 'No – we do not use agencies' ? ' selected' : ''; ?>>No – we do not use agencies</option>
                                    </select>
                                </label>
                                <label>How many supply agencies do you typically work with?
                                    <select name="cmn_agency_count" required>
                                        <option value="" disabled <?php echo $prefill['agency_count'] === '' ? 'selected' : ''; ?>>Select count</option>
                                        <option value="1"<?php echo $prefill['agency_count'] === '1' ? ' selected' : ''; ?>>1</option>
                                        <option value="2–3"<?php echo $prefill['agency_count'] === '2–3' ? ' selected' : ''; ?>>2–3</option>
                                        <option value="4–5"<?php echo $prefill['agency_count'] === '4–5' ? ' selected' : ''; ?>>4–5</option>
                                        <option value="More than 5"<?php echo $prefill['agency_count'] === 'More than 5' ? ' selected' : ''; ?>>More than 5</option>
                                        <option value="Not sure"<?php echo $prefill['agency_count'] === 'Not sure' ? ' selected' : ''; ?>>Not sure</option>
                                    </select>
                                </label>
                            </div>
                        </div>

                        <button class="cmn-primary" type="submit"><?php echo $is_token_mode ? 'Complete Profile' : 'Request Access'; ?></button>
                        <div class="cmn-register-expectations">
                            <strong>Access is controlled.</strong>
                            <p>This is not instant access. All registrations are reviewed and verified. A member of our team will arrange a verification call before approval.</p>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    public function render_register_candidate_shortcode() {
        ob_start();
        $submitted = isset($_GET['cmn_submitted']) && $_GET['cmn_submitted'] === '1';
        $submitted_email = '';
        if ($submitted && isset($_GET['cmn_email'])) {
            $submitted_email = sanitize_email(wp_unslash($_GET['cmn_email']));
        }
        ?>
        <section class="cmn-portal">
            <a class="cmn-back-link" href="<?php echo esc_url(home_url('/covermenow-one')); ?>">Back to portal</a>
            <header class="cmn-portal-header"></header>
            <div class="cmn-register-shell">
                <div class="cmn-register-brand">
                    <h3>
                        <span class="brand-main">CoverMeNow</span>
                        <span class="brand-accent">ONE</span>
                    </h3>
                    <p><span class="tag-white">One</span> <span class="brand-accent">system</span>. <span class="tag-white">Total</span> <span class="brand-accent">cover</span>.</p>
                </div>
                <?php if ($submitted) : ?>
                    <div class="cmn-register-success">
                        <h4>Thank you for registering</h4>
                        <p>A verification email has been sent to <?php echo esc_html($submitted_email ?: 'your email address'); ?>.</p>
                        <p>Once you verify your email, you can complete the rest of your profile.</p>
                    </div>
                <?php else : ?>
                    <div class="cmn-register-benefits">
                        <h4>Why candidates use CoverMeNow <span class="brand-accent">ONE</span></h4>
                        <div class="cmn-benefit-grid">
                            <article class="cmn-benefit-card">
                                <h5>Work only when you choose</h5>
                                <p class="cmn-benefit-line"><span class="cmn-benefit-check">✓</span>Confirm your availability the night before</p>
                                <div class="cmn-benefit-figure">
                                    <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/use1.png'); ?>" alt="" />
                                </div>
                                <span class="cmn-benefit-caption">Confirm your availability the night before</span>
                            </article>
                            <article class="cmn-benefit-card">
                                <h5>Be visible to schools before the day starts</h5>
                                <p class="cmn-benefit-line"><span class="cmn-benefit-check">✓</span>Get noticed first thing in the morning</p>
                                <div class="cmn-benefit-figure">
                                    <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/use2.png'); ?>" alt="" />
                                </div>
                                <span class="cmn-benefit-caption">Get noticed first thing in the morning</span>
                            </article>
                            <article class="cmn-benefit-card">
                                <h5>No job boards, no applications, no chasing</h5>
                                <p class="cmn-benefit-line"><span class="cmn-benefit-check">✓</span>Mark availability and get approached directly</p>
                                <div class="cmn-benefit-figure">
                                    <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/use3.png'); ?>" alt="" />
                                </div>
                                <span class="cmn-benefit-caption">Mark availability and get approached directly</span>
                            </article>
                            <article class="cmn-benefit-card">
                                <h5>Free online courses and career growth</h5>
                                <p class="cmn-benefit-line"><span class="cmn-benefit-check">✓</span>Upgrade your skills and boost your visibility</p>
                                <div class="cmn-benefit-figure">
                                    <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/courses.png'); ?>" alt="" />
                                </div>
                                <span class="cmn-benefit-caption">Upgrade your skills and boost your visibility</span>
                            </article>
                        </div>
                    </div>
                    <form class="cmn-form cmn-register-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                        <?php wp_nonce_field('cmn_register_candidate', 'cmn_register_candidate_nonce'); ?>
                        <input type="hidden" name="action" value="cmn_register_candidate">
                        <div class="cmn-required-note">* Required</div>
                        <div class="cmn-form-grid">
                            <label>Full Name *<input type="text" name="cmn_candidate_name" placeholder="Full Name" required></label>
                            <label>Email *<input type="email" name="cmn_email" placeholder="Email" required></label>
                            <label>Phone *<input type="tel" name="cmn_phone" placeholder="Phone" required></label>
                            <label>Location *<input type="text" name="cmn_location" placeholder="Town / City" required></label>
                        </div>
                        <div class="cmn-form-group">
                            <span class="cmn-form-label">Address</span>
                            <div class="cmn-form-grid">
                                <label>House Name / Number *<input type="text" name="cmn_house_number" placeholder="House Name / Number" required></label>
                                <label>Address Line 1 *<input type="text" name="cmn_address_line1" placeholder="Address Line 1" required></label>
                                <label>Address Line 2<input type="text" name="cmn_address_line2" placeholder="Address Line 2"></label>
                                <label>Address Line 3<input type="text" name="cmn_address_line3" placeholder="Address Line 3"></label>
                                <label>Town / City *<input type="text" name="cmn_town" placeholder="Town / City" required></label>
                                <label>County *<input type="text" name="cmn_county" placeholder="County" required></label>
                                <label>Post Code *<input type="text" name="cmn_postcode" placeholder="Post Code" required></label>
                            </div>
                        </div>
                        <div class="cmn-form-group">
                            <span class="cmn-form-label">Uploads</span>
                            <label>CV Upload *<input type="file" name="cmn_cv_file" required></label>
                            <label>Identification Document (Passport / Driving Licence)
                                <input type="file" name="cmn_id_file">
                            </label>
                            <label class="cmn-dbs-upload">DBS Upload<input type="file" name="cmn_dbs_file"></label>
                            <div class="cmn-radio-grid">
                                <span class="cmn-radio-label">Is your DBS an enhanced DBS on the update service? <a class="cmn-link" href="https://www.gov.uk/dbs-update-service" target="_blank" rel="noopener">Click here</a> for more info.</span>
                                <div class="cmn-inline-row">
                                    <label class="cmn-inline-check"><input type="radio" name="cmn_dbs_update_service" value="yes"> Yes</label>
                                    <label class="cmn-inline-check"><input type="radio" name="cmn_dbs_update_service" value="no"> No</label>
                                </div>
                            </div>
                            <label class="cmn-inline-check"><input type="checkbox" name="cmn_no_dbs" value="1" data-dbs-toggle> I do not have a DBS.</label>
                            <p class="cmn-muted cmn-muted-small cmn-dbs-note" hidden>DBS verification unlocks visibility to schools. If you don't have one yet, we can help.</p>
                        </div>
                        <div class="cmn-form-group">
                            <span class="cmn-form-label">What sort of jobs are you looking for?</span>
                            <div class="cmn-check-grid">
                                <label class="cmn-check-card"><input type="checkbox" name="cmn_roles[]" value="Teaching Assistant"> <span>Teaching Assistant</span></label>
                                <label class="cmn-check-card"><input type="checkbox" name="cmn_roles[]" value="Teacher"> <span>Teacher</span></label>
                                <label class="cmn-check-card"><input type="checkbox" name="cmn_roles[]" value="Cover Supervisor"> <span>Cover Supervisor</span></label>
                                <label class="cmn-check-card"><input type="checkbox" name="cmn_roles[]" value="Learning Support Assistant"> <span>Learning Support Assistant</span></label>
                                <label class="cmn-check-card cmn-check-other"><input type="checkbox" name="cmn_roles[]" value="Other" data-other-toggle> <span>Other</span></label>
                            </div>
                            <label class="cmn-other-field" hidden>Tell us what role
                                <input type="text" name="cmn_roles_other" placeholder="e.g., SEN Teacher, HLTA, Exam Invigilator">
                            </label>
                        </div>
                        <div class="cmn-form-group">
                            <span class="cmn-form-label">Travel</span>
                            <div class="cmn-radio-grid">
                                <span class="cmn-radio-label">Do you own a UK driving license?</span>
                                <div class="cmn-inline-row">
                                    <label class="cmn-inline-check"><input type="radio" name="cmn_driving_licence" value="yes" data-licence="yes" data-licence-toggle> Yes</label>
                                    <label class="cmn-inline-check"><input type="radio" name="cmn_driving_licence" value="no" data-licence="no" data-licence-toggle> No</label>
                                </div>
                            </div>
                            <div class="cmn-car-field" hidden>
                                <span class="cmn-radio-label">Do you own a vehicle?</span>
                                <div class="cmn-inline-row">
                                    <label class="cmn-inline-check"><input type="radio" name="cmn_car_owner" value="yes"> Yes</label>
                                    <label class="cmn-inline-check"><input type="radio" name="cmn_car_owner" value="no"> No</label>
                                </div>
                            </div>
                            <label>How far are you willing to travel? (this includes public transport)
                                <input type="text" name="cmn_travel_distance" placeholder="e.g., 10 miles, 30 minutes">
                            </label>
                        </div>
                        <div class="cmn-form-group">
                            <span class="cmn-form-label">Availability (Mon-Fri)</span>
                            <div class="cmn-check-grid">
                                <label class="cmn-check-card cmn-check-all"><input type="checkbox" id="cmn-select-all-days"> <span>Select all</span></label>
                                <label class="cmn-check-card"><input type="checkbox" name="cmn_availability_days[]" value="Monday" data-day> <span>Monday</span></label>
                                <label class="cmn-check-card"><input type="checkbox" name="cmn_availability_days[]" value="Tuesday" data-day> <span>Tuesday</span></label>
                                <label class="cmn-check-card"><input type="checkbox" name="cmn_availability_days[]" value="Wednesday" data-day> <span>Wednesday</span></label>
                                <label class="cmn-check-card"><input type="checkbox" name="cmn_availability_days[]" value="Thursday" data-day> <span>Thursday</span></label>
                                <label class="cmn-check-card"><input type="checkbox" name="cmn_availability_days[]" value="Friday" data-day> <span>Friday</span></label>
                            </div>
                        </div>
                        <label>Additional notes
                            <textarea name="cmn_notes" rows="4" placeholder="Anything else we should know?"></textarea>
                        </label>
                        <button class="cmn-primary" type="submit">Register</button>
                        <div class="cmn-register-expectations">
                            <strong>Access is controlled.</strong>
                            <p>This is not instant access. All registrations are reviewed, and only available candidates are shown to schools. All applications are vetted for safeguarding standards.</p>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    public function render_school_landing_shortcode() {
        $school_page = get_page_by_title('School Registration');
        $login_page = get_page_by_title('Login');
        $school_url = $school_page ? get_permalink($school_page) : home_url('/school-registration');
        $login_url = $login_page ? get_permalink($login_page) : home_url('/login');

        ob_start();
        ?>
        <section class="cmn-portal">
            <header class="cmn-portal-header">
                <h2>CoverMeNow ONE for Schools</h2>
                <p>Reliable same-day and short-notice cover without job posts, applications, or chasing.</p>
            </header>
            <div class="cmn-portal-grid">
                <div class="cmn-panel-card">
                    <h3>Real-time availability</h3>
                    <p>Only vetted candidates who actively mark availability are visible.</p>
                </div>
                <div class="cmn-panel-card">
                    <h3>Admin-controlled cover</h3>
                    <p>All cover is approved, logged, and audit-ready.</p>
                </div>
                <div class="cmn-panel-card">
                    <h3>No early-morning scramble</h3>
                    <p>Cover is confirmed quickly without job boards or back-and-forth.</p>
                </div>
            </div>
            <div class="cmn-portal-grid">
                <div class="cmn-panel-card">
                    <h3>How it works</h3>
                    <div class="cmn-actions-grid">
                        <span>1. Candidates are pre-vetted and onboarded.</span>
                        <span>2. Candidates mark availability in real time.</span>
                        <span>3. Admin confirms and logs cover.</span>
                    </div>
                </div>
                <div class="cmn-panel-card">
                    <h3>Request access</h3>
                    <p>Approval required to keep safeguarding and control in place.</p>
                    <div class="cmn-actions-grid">
                        <a class="cmn-ghost" href="<?php echo esc_url($school_url); ?>">Apply to Register (School)</a>
                        <a class="cmn-ghost" href="<?php echo esc_url($login_url); ?>">Log In</a>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    public function render_candidate_landing_shortcode() {
        $candidate_page = get_page_by_title('Candidate Registration');
        $login_page = get_page_by_title('Login');
        $candidate_url = $candidate_page ? get_permalink($candidate_page) : home_url('/candidate-registration');
        $login_url = $login_page ? get_permalink($login_page) : home_url('/login');

        ob_start();
        ?>
        <section class="cmn-portal">
            <header class="cmn-portal-header">
                <h2>CoverMeNow ONE for Candidates</h2>
                <p>Join a vetted, availability-led system that schools rely on for short-notice cover.</p>
            </header>
            <div class="cmn-portal-grid">
                <div class="cmn-panel-card">
                    <h3>Professional onboarding</h3>
                    <p>Approved candidates are part of a controlled safeguarding-led system.</p>
                </div>
                <div class="cmn-panel-card">
                    <h3>Availability-led work</h3>
                    <p>Mark availability and be seen by schools when you are actually free.</p>
                </div>
                <div class="cmn-panel-card">
                    <h3>Clear admin process</h3>
                    <p>Requests are confirmed and logged—no job board noise.</p>
                </div>
            </div>
            <div class="cmn-portal-grid">
                <div class="cmn-panel-card">
                    <h3>How it works</h3>
                    <div class="cmn-actions-grid">
                        <span>1. Apply to register.</span>
                        <span>2. Complete vetting and DBS checks.</span>
                        <span>3. Mark availability to receive cover requests.</span>
                    </div>
                </div>
                <div class="cmn-panel-card">
                    <h3>Apply to join</h3>
                    <p>Registration is reviewed—access is approved manually.</p>
                    <div class="cmn-actions-grid">
                        <a class="cmn-ghost" href="<?php echo esc_url($candidate_url); ?>">Apply to Register (Candidate)</a>
                        <a class="cmn-ghost" href="<?php echo esc_url($login_url); ?>">Log In</a>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    public function render_school_dashboard_shortcode() {
        if (!is_user_logged_in()) {
            return $this->render_login_shortcode();
        }
        $is_preview = isset($_GET['as']) && sanitize_text_field($_GET['as']) === 'school' && $this->can_preview_dashboards();
        if (!$is_preview && !$this->is_school_user()) {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access restricted</h3><p>This section is available to school users only.</p></div></section>';
        }
        $user_school_id = $this->resolve_school_id_for_user();
        $school_status = $user_school_id ? get_post_meta($user_school_id, 'cmn_status', true) : '';
        if (!$is_preview && $school_status !== 'client') {
            return '<section class="cmn-portal"><div class="cmn-panel-card"><h3>Access pending</h3><p>Your school access will be enabled once your application is approved.</p></div></section>';
        }
        $school_location = $user_school_id ? get_post_meta($user_school_id, 'cmn_location', true) : '';
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        $tab = isset($_GET['school']) ? sanitize_text_field($_GET['school']) : 'dashboard';
        $availability_date = $this->get_tomorrow_date();
        $availability_label = $availability_date ? date_i18n('l, F jS', strtotime($availability_date)) : 'Tomorrow';
        $availability_candidates = $availability_date ? $this->get_available_candidates_with_times($availability_date, $user_school_id ?: 0, 24) : [];
        $school_domain = $user_school_id ? get_post_meta($user_school_id, 'cmn_school_email_domain', true) : '';
        if (!$school_domain && $user_school_id) {
            $school_email = get_post_meta($user_school_id, 'cmn_email', true);
            $school_domain = $this->get_email_domain($school_email);
        }
        $school_requests = $school_domain ? $this->get_school_candidate_requests($school_domain, 20) : [];
        $school_bookings = [];
        if ($user_school_id) {
            $booking_query = new WP_Query([
                'post_type' => 'cmn_booking',
                'posts_per_page' => 20,
                'meta_query' => [
                    [
                        'key' => 'cmn_school_id',
                        'value' => $user_school_id,
                    ],
                    [
                        'key' => 'cmn_status',
                        'value' => 'approved',
                    ],
                ],
                'meta_key' => 'cmn_date',
                'orderby' => 'meta_value',
                'order' => 'DESC',
            ]);
            if ($booking_query->have_posts()) {
                $school_bookings = $booking_query->posts;
            }
            wp_reset_postdata();
        }
        $school_ready_responses = (!$is_preview && $this->is_school_user()) ? $this->get_school_ready_responses(get_current_user_id()) : [];
        $ready_response_notice = isset($_GET['cmn_ready_response_msg']) ? sanitize_text_field(wp_unslash($_GET['cmn_ready_response_msg'])) : '';
        $can_request = !$is_preview && $user_school_id && $school_status === 'client';
        $nav_items = [
            'dashboard' => 'Dashboard',
            'cover' => 'COVER ME NOW',
            'calendar' => 'Calendar',
            'requests' => 'Requests / Jobs',
            'team' => 'My Team',
            'profile' => 'Profile',
            'support' => 'Support',
            'settings' => 'Settings',
        ];
        ob_start();
        ?>
        <section class="cmn-portal cmn-school-portal cmn-portal-light">
            <div class="cmn-portal-topbar">
                <div class="cmn-topbar-left"><?php echo $this->render_portal_branding(); ?></div>
                <div class="cmn-topbar-right">
                    <?php echo $this->render_notifications_bell(get_current_user_id()); ?>
                    <span>Welcome, <?php echo esc_html(wp_get_current_user()->display_name); ?></span>
                    <a class="cmn-topbar-logout" href="<?php echo esc_url(wp_logout_url($portal_url)); ?>">Logout</a>
                </div>
            </div>
            <div class="cmn-school-shell">
                <aside class="cmn-school-nav">
                    <div class="cmn-school-nav-title">School Dashboard</div>
                    <nav class="cmn-school-nav-links">
                        <?php foreach ($nav_items as $key => $label) : ?>
                            <?php $link = add_query_arg(['school' => $key], $portal_url); ?>
                            <a class="cmn-school-nav-link<?php echo $tab === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url($link); ?>"><?php echo esc_html($label); ?></a>
                        <?php endforeach; ?>
                        <a class="cmn-school-nav-link" href="<?php echo esc_url(wp_logout_url($portal_url)); ?>">Logout</a>
                    </nav>
                </aside>
                <main class="cmn-school-main">
                    <?php if ($tab === 'dashboard') : ?>
                        <header class="cmn-school-header">
                            <h2>Candidates Available Tomorrow Morning</h2>
                            <p>Updated daily for <?php echo esc_html($availability_label); ?>.</p>
                        </header>
                        <div class="cmn-available-list">
                            <?php
                            if ($availability_candidates) :
                                foreach ($availability_candidates as $item) :
                                    $candidate = $item['post'];
                                    $candidate_status = get_post_meta($candidate->ID, 'cmn_status', true);
                                    if ($candidate_status && $candidate_status !== 'approved') {
                                        continue;
                                    }
                                    $role_meta = (array) get_post_meta($candidate->ID, 'cmn_roles', true);
                                    $role_label = $role_meta ? $role_meta[0] : 'Candidate';
                                    $location = get_post_meta($candidate->ID, 'cmn_location', true);
                                    $name_parts = preg_split('/\\s+/', trim((string) $candidate->post_title));
                                    $first_name = $name_parts ? $name_parts[0] : $candidate->post_title;
                                    $candidate_user_id = (int) get_post_meta($candidate->ID, 'cmn_user_id', true);
                                    $strength = $this->get_candidate_strength_metrics($candidate->ID, $candidate_user_id);
                            ?>
                                    <div class="cmn-available-card">
                                        <div class="cmn-available-header">
                                            <strong><?php echo esc_html($first_name); ?></strong>
                                            <span class="cmn-pill cmn-pill--available">Available Tomorrow Morning</span>
                                        </div>
                                        <span class="cmn-muted"><?php echo esc_html($role_label . ($location ? ' · ' . $location : '')); ?></span>
                                        <div class="cmn-strength-score">
                                            <strong>CMN Strength Score: <?php echo esc_html((int) $strength['score']); ?>/100</strong>
                                            <span>Profile <?php echo esc_html((int) $strength['profile_completion']); ?>% · Docs <?php echo esc_html((int) $strength['docs_approved']); ?>/<?php echo esc_html((int) $strength['docs_total']); ?> approved</span>
                                            <span>Bookings <?php echo esc_html((int) $strength['bookings_completed']); ?> · Rating <?php echo esc_html(number_format((float) $strength['avg_rating'], 1)); ?>/5 · Experience <?php echo esc_html((int) $strength['years_experience']); ?>y</span>
                                        </div>
                                        <?php if ($can_request) : ?>
                                            <label class="cmn-inline-ready-response">Auto-message
                                                <select data-request-ready-response>
                                                    <option value="">Default template</option>
                                                    <option value="none">None</option>
                                                    <?php foreach ($school_ready_responses as $ready_response) : ?>
                                                        <option value="<?php echo esc_attr((int) ($ready_response['id'] ?? 0)); ?>"><?php echo esc_html((string) ($ready_response['title'] ?? 'Template')); ?><?php echo (int) ($ready_response['is_default'] ?? 0) === 1 ? ' (Default)' : ''; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </label>
                                            <button class="cmn-primary" type="button" data-request-candidate data-candidate-id="<?php echo esc_attr($candidate->ID); ?>">Request This Candidate</button>
                                            <div class="cmn-request-message" data-request-message></div>
                                        <?php else : ?>
                                            <span class="cmn-muted">Requests are available to client schools.</span>
                                        <?php endif; ?>
                                    </div>
                            <?php
                                endforeach;
                            else :
                            ?>
                                <div class="cmn-dashboard-card">No candidates marked available yet.</div>
                            <?php endif; ?>
                        </div>
                        <div class="cmn-dashboard-row cmn-dashboard-row-equal">
                            <div class="cmn-dashboard-card cmn-compliance-status">
                                <div class="cmn-card-header">
                                    <h3>My Requests</h3>
                                    <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['school' => 'requests'], $portal_url)); ?>">View all</a>
                                </div>
                                <?php if ($school_requests) : ?>
                                    <div class="cmn-list">
                                        <?php foreach (array_slice($school_requests, 0, 5) as $request) : ?>
                                            <?php
                                            $candidate = get_post((int) $request['candidate_id']);
                                            if (!$candidate) {
                                                continue;
                                            }
                                            $requested_date = $request['requested_date'] ?? '';
                                            $requested_label = $requested_date ? date_i18n('M j, Y', strtotime($requested_date)) : 'Tomorrow';
                                            $status_label = ucfirst($request['status'] ?? 'pending');
                                            $status_class = 'cmn-pill--' . sanitize_html_class($request['status'] ?? 'pending');
                                            ?>
                                            <div class="cmn-list-item">
                                                <strong><?php echo esc_html($candidate->post_title); ?></strong>
                                                <span><?php echo esc_html($requested_label); ?> <span class="cmn-pill <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else : ?>
                                    <div class="cmn-empty">No requests yet.</div>
                                <?php endif; ?>
                            </div>
                            <div class="cmn-dashboard-card cmn-doc-upload-card">
                                <div class="cmn-card-header">
                                    <h3>Bookings</h3>
                                    <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['school' => 'requests'], $portal_url)); ?>">View all</a>
                                </div>
                                <?php if ($school_bookings) : ?>
                                    <div class="cmn-list">
                                        <?php foreach (array_slice($school_bookings, 0, 5) as $booking) : ?>
                                            <?php
                                            $booking_candidate_id = (int) get_post_meta($booking->ID, 'cmn_candidate_id', true);
                                            $booking_date = get_post_meta($booking->ID, 'cmn_date', true) ?: get_post_meta($booking->ID, 'cmn_start_date', true);
                                            $candidate_post = $booking_candidate_id ? get_post($booking_candidate_id) : null;
                                            $candidate_name = $candidate_post ? $candidate_post->post_title : 'Candidate';
                                            $name_bits = preg_split('/\\s+/', trim((string) $candidate_name));
                                            $first = $name_bits ? $name_bits[0] : $candidate_name;
                                            $initial = isset($name_bits[1]) ? strtoupper(substr($name_bits[1], 0, 1)) . '.' : '';
                                            $display_name = trim($first . ' ' . $initial);
                                            ?>
                                            <div class="cmn-list-item">
                                                <strong><?php echo esc_html($display_name); ?></strong>
                                                <span><?php echo esc_html($booking_date ? date_i18n('M j, Y', strtotime($booking_date)) : ''); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else : ?>
                                    <div class="cmn-empty">No bookings yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif ($tab === 'cover') : ?>
                        <header class="cmn-school-header">
                            <h2>COVER ME NOW</h2>
                        </header>
                        <p>Candidates available for <?php echo esc_html($availability_label); ?>.</p>
                        <div class="cmn-available-list">
                            <?php
                            if ($availability_candidates) :
                                foreach ($availability_candidates as $item) :
                                    $candidate = $item['post'];
                                    $candidate_status = get_post_meta($candidate->ID, 'cmn_status', true);
                                    if ($candidate_status && $candidate_status !== 'approved') {
                                        continue;
                                    }
                                    $role_meta = (array) get_post_meta($candidate->ID, 'cmn_roles', true);
                                    $role_label = $role_meta ? $role_meta[0] : 'Candidate';
                                    $location = get_post_meta($candidate->ID, 'cmn_location', true);
                                    $name_parts = preg_split('/\\s+/', trim((string) $candidate->post_title));
                                    $first_name = $name_parts ? $name_parts[0] : $candidate->post_title;
                                    $candidate_user_id = (int) get_post_meta($candidate->ID, 'cmn_user_id', true);
                                    $strength = $this->get_candidate_strength_metrics($candidate->ID, $candidate_user_id);
                            ?>
                                    <div class="cmn-available-card">
                                        <div class="cmn-available-header">
                                            <strong><?php echo esc_html($first_name); ?></strong>
                                            <span class="cmn-pill cmn-pill--available">Available Tomorrow Morning</span>
                                        </div>
                                        <span class="cmn-muted"><?php echo esc_html($role_label . ($location ? ' · ' . $location : '')); ?></span>
                                        <div class="cmn-strength-score">
                                            <strong>CMN Strength Score: <?php echo esc_html((int) $strength['score']); ?>/100</strong>
                                            <span>Profile <?php echo esc_html((int) $strength['profile_completion']); ?>% · Docs <?php echo esc_html((int) $strength['docs_approved']); ?>/<?php echo esc_html((int) $strength['docs_total']); ?> approved</span>
                                            <span>Bookings <?php echo esc_html((int) $strength['bookings_completed']); ?> · Rating <?php echo esc_html(number_format((float) $strength['avg_rating'], 1)); ?>/5 · Experience <?php echo esc_html((int) $strength['years_experience']); ?>y</span>
                                        </div>
                                        <?php if ($can_request) : ?>
                                            <label class="cmn-inline-ready-response">Auto-message
                                                <select data-request-ready-response>
                                                    <option value="">Default template</option>
                                                    <option value="none">None</option>
                                                    <?php foreach ($school_ready_responses as $ready_response) : ?>
                                                        <option value="<?php echo esc_attr((int) ($ready_response['id'] ?? 0)); ?>"><?php echo esc_html((string) ($ready_response['title'] ?? 'Template')); ?><?php echo (int) ($ready_response['is_default'] ?? 0) === 1 ? ' (Default)' : ''; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </label>
                                            <button class="cmn-primary" type="button" data-request-candidate data-candidate-id="<?php echo esc_attr($candidate->ID); ?>">Request This Candidate</button>
                                            <div class="cmn-request-message" data-request-message></div>
                                        <?php else : ?>
                                            <span class="cmn-muted">Requests are available to client schools.</span>
                                        <?php endif; ?>
                                    </div>
                            <?php
                                endforeach;
                            else :
                            ?>
                                <div class="cmn-dashboard-card">No candidates marked available yet.</div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($tab === 'calendar') : ?>
                        <header class="cmn-school-header">
                            <h2>Calendar</h2>
                        </header>
                        <div class="cmn-school-calendar">
                            <div class="cmn-calendar-panel">
                                <div class="cmn-calendar-grid">
                                    <div class="cmn-calendar-day">S</div>
                                    <div class="cmn-calendar-day">M</div>
                                    <div class="cmn-calendar-day">T</div>
                                    <div class="cmn-calendar-day">W</div>
                                    <div class="cmn-calendar-day">T</div>
                                    <div class="cmn-calendar-day">F</div>
                                    <div class="cmn-calendar-day">S</div>
                                    <?php for ($i = 1; $i <= 30; $i++) : ?>
                                        <?php
                                        $class = 'cmn-calendar-cell';
                                        if ($i % 7 === 0) {
                                            $class .= ' is-unavailable';
                                        } elseif ($i % 5 === 0) {
                                            $class .= ' is-available';
                                        }
                                        ?>
                                        <div class="<?php echo esc_attr($class); ?>"><span><?php echo esc_html($i); ?></span></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="cmn-calendar-detail">
                                <h3>Cover Details</h3>
                                <p>Select a date to view or edit cover needs.</p>
                                <button class="cmn-primary">Add Cover Need</button>
                            </div>
                        </div>
                    <?php elseif ($tab === 'requests') : ?>
                        <header class="cmn-school-header">
                            <h2>Requests / Jobs</h2>
                        </header>
                        <div class="cmn-dashboard-card">
                            <h3>Requests Sent</h3>
                            <?php if ($school_requests) : ?>
                                <div class="cmn-list">
                                    <?php foreach ($school_requests as $request) : ?>
                                        <?php
                                        $candidate = get_post((int) $request['candidate_id']);
                                        if (!$candidate) {
                                            continue;
                                        }
                                        $requested_date = $request['requested_date'] ?? '';
                                        $requested_label = $requested_date ? date_i18n('M j, Y', strtotime($requested_date)) : 'Tomorrow';
                                        $charge_rate = isset($request['school_charge_rate']) ? (float) $request['school_charge_rate'] : 0.0;
                                        $request_status = strtolower((string) ($request['status'] ?? 'requested'));
                                        $request_booking_id = $this->get_booking_id_for_request((int) ($request['id'] ?? 0));
                                        $is_completed_booking = $request_booking_id ? $this->is_booking_feedback_eligible($request_booking_id) : false;
                                        $school_feedback_payload = $is_completed_booking ? $this->get_booking_feedback_payload($request_booking_id, get_current_user_id(), 'school') : null;
                                        $school_feedback_submitted = !empty($school_feedback_payload['viewer_feedback']);
                                        ?>
                                        <div class="cmn-list-item">
                                            <strong><?php echo esc_html($candidate->post_title); ?></strong>
                                            <span><?php echo esc_html($requested_label . ' · ' . ucfirst($request_status)); ?> · Charge £<?php echo esc_html(number_format($charge_rate, 2)); ?></span>
                                            <?php if ($request_booking_id && in_array($request_status, ['accepted', 'confirmed'], true)) : ?>
                                                <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['school' => 'requests', 'cmn_booking_chat' => $request_booking_id, 'cmn_thread_type' => 'booking_details'], $portal_url)); ?>">Open booking chat</a>
                                            <?php endif; ?>
                                            <?php if ($is_completed_booking && !$school_feedback_submitted) : ?>
                                                <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['school' => 'requests', 'cmn_booking_chat' => $request_booking_id, 'cmn_thread_type' => 'booking_details', 'cmn_feedback_prompt' => '1'], $portal_url)); ?>">Rate Candidate</a>
                                                <span class="cmn-status-chip is-pending">Feedback pending</span>
                                            <?php elseif ($school_feedback_submitted) : ?>
                                                <span class="cmn-status-chip is-approved">Feedback submitted</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="cmn-empty">No requests sent yet.</div>
                            <?php endif; ?>
                        </div>
                        <?php
                        $school_chat_booking_id = isset($_GET['cmn_booking_chat']) ? (int) $_GET['cmn_booking_chat'] : 0;
                        $school_chat_thread_type = sanitize_key((string) ($_GET['cmn_thread_type'] ?? 'booking_details'));
                        if (!in_array($school_chat_thread_type, ['booking_details', 'pay_negotiation', 'decline_followup'], true)) {
                            $school_chat_thread_type = 'booking_details';
                        }
                        if ($school_chat_booking_id) :
                            $school_chat_thread = $this->get_booking_thread_by_booking($school_chat_booking_id, $school_chat_thread_type);
                            if ($school_chat_thread && $this->user_can_access_booking_thread((int) $school_chat_thread['id'], get_current_user_id())) :
                                $school_booking_ref = get_the_title($school_chat_booking_id);
                                if (!$school_booking_ref) {
                                    $school_booking_ref = 'Booking #' . (int) $school_chat_booking_id;
                                }
                        ?>
                            <div class="cmn-dashboard-card cmn-compliance-status">
                                <div class="cmn-card-header">
                                    <h3>Booking Chat</h3>
                                    <span class="cmn-muted"><?php echo esc_html($school_booking_ref); ?></span>
                                </div>
                                <div class="cmn-booking-feedback-summary" data-booking-feedback-summary></div>
                                <div class="cmn-support-messages" data-booking-chat data-thread-id="<?php echo esc_attr((int) $school_chat_thread['id']); ?>" data-thread-role="school" data-booking-id="<?php echo esc_attr($school_chat_booking_id); ?>" data-feedback-role="school">
                                    <?php foreach ($this->get_booking_thread_messages((int) $school_chat_thread['id']) as $chat_msg) : ?>
                                        <?php
                                        $chat_sender_role = sanitize_key((string) ($chat_msg['sender_role_type'] ?? 'system'));
                                        $chat_class = 'is-system';
                                        if ($chat_sender_role === 'school') {
                                            $chat_class = 'is-user is-school';
                                        } elseif (in_array($chat_sender_role, ['candidate', 'account_manager', 'admin'], true)) {
                                            $chat_class = 'is-admin is-' . $chat_sender_role;
                                        }
                                        ?>
                                        <div class="cmn-support-bubble cmn-booking-bubble <?php echo esc_attr($chat_class); ?>">
                                            <div class="cmn-support-meta"><?php echo esc_html(ucfirst(str_replace('_', ' ', $chat_msg['sender_role_type']))); ?> · <?php echo esc_html(date_i18n('M j, g:ia', strtotime($chat_msg['created_at']))); ?></div>
                                            <div class="cmn-support-text"><?php echo esc_html($chat_msg['message']); ?></div>
                                            <?php $chat_attachments = $this->get_booking_message_attachments($chat_msg); ?>
                                            <?php if ($chat_attachments) : ?>
                                                <div class="cmn-support-attachments">
                                                    <?php foreach ($chat_attachments as $attachment) : ?>
                                                        <a class="cmn-support-attachment-chip" href="<?php echo esc_url($attachment['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($attachment['filename']); ?></a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-support-reply" enctype="multipart/form-data">
                                    <?php wp_nonce_field('cmn_booking_chat', 'cmn_booking_chat_nonce'); ?>
                                    <input type="hidden" name="action" value="cmn_booking_chat_post">
                                    <input type="hidden" name="cmn_thread_id" value="<?php echo esc_attr((int) $school_chat_thread['id']); ?>">
                                    <textarea name="cmn_message" rows="3" required placeholder="Type your message..."></textarea>
                                    <input type="file" name="cmn_attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.txt">
                                    <button class="cmn-primary" type="submit">Send</button>
                                </form>
                            </div>
                            <div class="cmn-booking-feedback-modal" data-booking-feedback-modal hidden>
                                <div class="cmn-booking-feedback-modal__overlay"></div>
                                <div class="cmn-booking-feedback-modal__card" role="dialog" aria-modal="true" aria-labelledby="cmn-booking-feedback-title-school">
                                    <h3 id="cmn-booking-feedback-title-school">Rate Candidate</h3>
                                    <p class="cmn-muted" data-booking-feedback-subtitle></p>
                                    <form class="cmn-booking-feedback-form" data-booking-feedback-form>
                                        <input type="hidden" name="booking_id" value="<?php echo esc_attr($school_chat_booking_id); ?>">
                                        <div class="cmn-feedback-question">
                                            <label data-booking-feedback-label="stars_1">Punctuality</label>
                                            <div class="cmn-star-picker" data-booking-feedback-stars="stars_1"></div>
                                            <input type="hidden" name="stars_1" value="">
                                            <span class="cmn-star-score" data-booking-feedback-score="stars_1">0/5</span>
                                        </div>
                                        <div class="cmn-feedback-question">
                                            <label data-booking-feedback-label="stars_2">Professionalism</label>
                                            <div class="cmn-star-picker" data-booking-feedback-stars="stars_2"></div>
                                            <input type="hidden" name="stars_2" value="">
                                            <span class="cmn-star-score" data-booking-feedback-score="stars_2">0/5</span>
                                        </div>
                                        <div class="cmn-feedback-question">
                                            <label data-booking-feedback-label="stars_3">Classroom management</label>
                                            <div class="cmn-star-picker" data-booking-feedback-stars="stars_3"></div>
                                            <input type="hidden" name="stars_3" value="">
                                            <span class="cmn-star-score" data-booking-feedback-score="stars_3">0/5</span>
                                        </div>
                                        <div class="cmn-feedback-question">
                                            <label data-booking-feedback-label="stars_overall">Overall</label>
                                            <div class="cmn-star-picker" data-booking-feedback-stars="stars_overall"></div>
                                            <input type="hidden" name="stars_overall" value="">
                                            <span class="cmn-star-score" data-booking-feedback-score="stars_overall">0/5</span>
                                        </div>
                                        <div class="cmn-feedback-question" data-booking-feedback-would-rebook hidden>
                                            <label>Would you work here again?</label>
                                            <div class="cmn-feedback-toggle" data-booking-feedback-toggle>
                                                <button type="button" class="cmn-ghost" data-booking-feedback-toggle-value="1">Yes</button>
                                                <button type="button" class="cmn-ghost" data-booking-feedback-toggle-value="0">No</button>
                                            </div>
                                            <input type="hidden" name="would_rebook" value="">
                                        </div>
                                        <div class="cmn-feedback-question">
                                            <label>Tags</label>
                                            <div class="cmn-feedback-tags" data-booking-feedback-tags></div>
                                        </div>
                                        <div class="cmn-feedback-question">
                                            <label>Optional comments</label>
                                            <textarea name="comment" rows="3"></textarea>
                                        </div>
                                        <div class="cmn-settings-actions">
                                            <button class="cmn-primary" type="submit">Submit feedback</button>
                                            <span class="cmn-muted" data-booking-feedback-msg></span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php
                            endif;
                        endif;
                        ?>
                    <?php elseif ($tab === 'team') : ?>
                        <header class="cmn-school-header">
                            <h2>My Team</h2>
                        </header>
                        <div class="cmn-dashboard-card">
                            <h3>Account Manager</h3>
                            <p><?php echo esc_html(get_post_meta($user_school_id, 'cmn_account_manager_name', true) ?: 'CoverMeNow ONE'); ?></p>
                            <p><?php echo esc_html(get_post_meta($user_school_id, 'cmn_account_manager_email', true) ?: ''); ?></p>
                        </div>
                        <div class="cmn-dashboard-card">
                            <h3>Cover Manager</h3>
                            <p><?php echo esc_html(get_post_meta($user_school_id, 'cmn_cover_manager', true) ?: ''); ?></p>
                        </div>
                    <?php elseif ($tab === 'profile') : ?>
                        <header class="cmn-school-header">
                            <h2>Profile</h2>
                        </header>
                        <div class="cmn-profile-grid">
                            <div class="cmn-dashboard-card cmn-doc-upload-card cmn-dashboard-card-wide">
                                <h3>School Details</h3>
                                <p><strong>Location:</strong> <?php echo esc_html(get_post_meta($user_school_id, 'cmn_location', true)); ?></p>
                                <p><strong>Phone:</strong> <?php echo esc_html(get_post_meta($user_school_id, 'cmn_phone', true)); ?></p>
                                <p><strong>Email:</strong> <?php echo esc_html(get_post_meta($user_school_id, 'cmn_email', true)); ?></p>
                                <p><strong>Website:</strong> <?php echo esc_html(get_post_meta($user_school_id, 'cmn_website', true)); ?></p>
                            </div>
                            <div class="cmn-dashboard-card">
                                <h3>Contacts</h3>
                                <p><?php echo esc_html(get_post_meta($user_school_id, 'cmn_contact1', true)); ?></p>
                                <p><?php echo esc_html(get_post_meta($user_school_id, 'cmn_contact1_email', true)); ?></p>
                            </div>
                        </div>
                    <?php elseif ($tab === 'support') : ?>
                        <header class="cmn-school-header">
                            <h2>Support</h2>
                        </header>
                        <div class="cmn-support-user" data-support-root data-support-mode="user" data-support-role="school">
                            <div class="cmn-support-header-row">
                                <div>
                                    <h3>Need help?</h3>
                                    <p class="cmn-muted">Email school@covermenow.co.uk or open a support ticket.</p>
                                </div>
                                <button class="cmn-primary" type="button" data-support-open>Open Support Ticket</button>
                            </div>
                            <div class="cmn-support-dashboard" data-support-dashboard>
                                <button class="cmn-support-tile is-active" type="button" data-support-tile="all">
                                    <span>All tickets</span>
                                    <strong data-support-count="all">0</strong>
                                </button>
                                <button class="cmn-support-tile" type="button" data-support-tile="open">
                                    <span>Open tickets</span>
                                    <strong data-support-count="open">0</strong>
                                </button>
                                <button class="cmn-support-tile" type="button" data-support-tile="closed">
                                    <span>Closed tickets</span>
                                    <strong data-support-count="closed">0</strong>
                                </button>
                                <button class="cmn-support-tile" type="button" data-support-tile="feedback_insights">
                                    <span>Recent feedback</span>
                                    <strong data-support-count="feedback_avg">0.0/5</strong>
                                </button>
                            </div>
                            <div class="cmn-support-body">
                                <div class="cmn-support-sidebar">
                                    <div class="cmn-support-filters">
                                        <button class="cmn-ghost is-active" type="button" data-support-filter="all">All</button>
                                        <button class="cmn-ghost" type="button" data-support-filter="open">Open</button>
                                        <button class="cmn-ghost" type="button" data-support-filter="closed">Closed</button>
                                        <button class="cmn-ghost" type="button" data-support-filter="needs_feedback">Needs feedback</button>
                                    </div>
                                    <div class="cmn-support-list" data-support-list>
                                        <div class="cmn-muted">Loading tickets...</div>
                                    </div>
                                </div>
                                <div class="cmn-support-thread" data-support-thread>
                                    <div class="cmn-support-thread-header">
                                        <div>
                                            <strong data-support-thread-title>My Tickets</strong>
                                            <span class="cmn-muted" data-support-thread-ref>Select a ticket to view messages.</span>
                                            <span class="cmn-status-chip is-approved" data-support-feedback-badge hidden>Feedback submitted</span>
                                        </div>
                                        <div class="cmn-support-thread-actions">
                                            <button class="cmn-ghost" type="button" data-support-reopen-ticket disabled>Reopen Ticket</button>
                                            <button class="cmn-ghost" type="button" data-support-save-transcript disabled>Save Transcript</button>
                                            <button class="cmn-ghost" type="button" data-support-email-transcript disabled>Send Transcript to Email</button>
                                        </div>
                                    </div>
                                    <div class="cmn-support-messages" data-support-messages></div>
                                    <form class="cmn-support-reply" data-support-reply>
                                        <textarea name="message" rows="4" placeholder="Type your reply..." required></textarea>
                                        <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.txt">
                                        <button class="cmn-primary" type="submit">Send Reply</button>
                                    </form>
                                    <div class="cmn-support-feedback" data-support-feedback></div>
                                </div>
                            </div>
                        </div>
                        <div class="cmn-support-feedback-modal" data-support-feedback-modal hidden>
                            <div class="cmn-support-feedback-modal__overlay"></div>
                            <div class="cmn-support-feedback-modal__card" role="dialog" aria-modal="true" aria-labelledby="cmn-support-feedback-title-school">
                                <h3 id="cmn-support-feedback-title-school">Support Feedback</h3>
                                <p class="cmn-muted">Rate your support experience for this closed ticket.</p>
                                <form class="cmn-support-feedback-modal-form" data-support-feedback-modal-form>
                                    <div class="cmn-feedback-question">
                                        <label>Support quality</label>
                                        <div class="cmn-star-picker" data-feedback-stars="support_rating"></div>
                                        <input type="hidden" name="support_rating" value="">
                                        <span class="cmn-star-score" data-feedback-score="support_rating">0/5</span>
                                    </div>
                                    <div class="cmn-feedback-question">
                                        <label>Response time</label>
                                        <div class="cmn-star-picker" data-feedback-stars="response_time_rating"></div>
                                        <input type="hidden" name="response_time_rating" value="">
                                        <span class="cmn-star-score" data-feedback-score="response_time_rating">0/5</span>
                                    </div>
                                    <div class="cmn-feedback-question">
                                        <label>Overall satisfaction</label>
                                        <div class="cmn-star-picker" data-feedback-stars="overall_satisfaction"></div>
                                        <input type="hidden" name="overall_satisfaction" value="">
                                        <span class="cmn-star-score" data-feedback-score="overall_satisfaction">0/5</span>
                                    </div>
                                    <div class="cmn-feedback-question">
                                        <label>Did we resolve your issue?</label>
                                        <div class="cmn-feedback-toggle" data-feedback-toggle>
                                            <button type="button" class="cmn-ghost" data-feedback-resolved="1">Yes</button>
                                            <button type="button" class="cmn-ghost" data-feedback-resolved="0">No</button>
                                        </div>
                                        <input type="hidden" name="issue_resolved" value="">
                                    </div>
                                    <div class="cmn-feedback-question">
                                        <label>Additional comments (optional)</label>
                                        <textarea name="comments" rows="3"></textarea>
                                    </div>
                                    <div class="cmn-settings-actions">
                                        <button class="cmn-primary" type="submit">Submit feedback</button>
                                        <span class="cmn-muted" data-support-feedback-modal-msg></span>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="cmn-support-insights-modal" data-support-insights-modal hidden>
                            <div class="cmn-support-insights-modal__overlay" data-support-insights-close></div>
                            <div class="cmn-support-insights-modal__card" role="dialog" aria-modal="true" aria-label="Feedback insights">
                                <div class="cmn-support-insights-header">
                                    <h3>Feedback insights</h3>
                                    <button class="cmn-ghost cmn-btn-mini" type="button" data-support-insights-close>Close</button>
                                </div>
                                <div class="cmn-support-insights-filters">
                                    <button class="cmn-ghost is-active" type="button" data-support-insights-filter="recent">Recent</button>
                                    <button class="cmn-ghost" type="button" data-support-insights-filter="lowest">Lowest scores</button>
                                    <button class="cmn-ghost" type="button" data-support-insights-filter="unresolved">Resolved = No</button>
                                    <button class="cmn-ghost" type="button" data-support-insights-filter="overall_lte_3">Overall ≤ 3</button>
                                </div>
                                <div class="cmn-support-insights-list" data-support-insights-list>
                                    <div class="cmn-muted">No feedback yet.</div>
                                </div>
                            </div>
                        </div>
                        <div class="cmn-modal" data-support-modal>
                            <div class="cmn-modal-content">
                                <div class="cmn-modal-header">
                                    <h3>Open Support Ticket</h3>
                                    <button class="cmn-ghost cmn-btn-mini" type="button" data-support-modal-close>Close</button>
                                </div>
                                <form class="cmn-form" data-support-form>
                                    <label>Category
                                        <select name="category">
                                            <option value="">Select</option>
                                            <option value="Account">Account</option>
                                            <option value="Booking">Booking</option>
                                            <option value="Documents">Documents</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </label>
                                    <label>Subject
                                        <input type="text" name="subject" required>
                                    </label>
                                    <label>Message
                                        <textarea name="message" rows="4" required></textarea>
                                    </label>
                                    <label>Attachments (optional)
                                        <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.txt">
                                    </label>
                                    <button class="cmn-primary" type="submit">Submit Ticket</button>
                                    <div class="cmn-muted" data-support-form-msg></div>
                                </form>
                            </div>
                        </div>
                    <?php elseif ($tab === 'settings') : ?>
                        <header class="cmn-school-header">
                            <h2>Settings</h2>
                        </header>
                        <?php if ($ready_response_notice) : ?>
                            <div class="cmn-register-success"><?php echo esc_html($ready_response_notice); ?></div>
                        <?php endif; ?>
                        <div class="cmn-dashboard-card">
                            <h3>Colour Scheme</h3>
                            <p class="cmn-muted">Choose how the portal looks for your account.</p>
                            <div data-theme-settings>
                                <label>Theme
                                    <select name="cmn_theme_scheme" data-theme-select>
                                        <?php foreach ($this->get_theme_scheme_choices() as $key => $label) : ?>
                                            <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <div class="cmn-settings-actions">
                                    <button class="cmn-primary" type="button" data-theme-save>Save scheme</button>
                                    <span class="cmn-muted" data-theme-message></span>
                                </div>
                            </div>
                        </div>
                        <div class="cmn-dashboard-card" data-ready-response-root>
                            <h3>Ready Responses</h3>
                            <p class="cmn-muted">Create saved templates that can auto-post in booking chat when a candidate accepts.</p>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-ready-response-form" data-ready-response-form>
                                <?php wp_nonce_field('cmn_ready_response_manage', 'cmn_ready_response_nonce'); ?>
                                <input type="hidden" name="action" value="cmn_ready_response_save">
                                <input type="hidden" name="cmn_ready_response_id" value="" data-ready-response-id>
                                <label>Template title
                                    <input type="text" name="cmn_ready_response_title" required data-ready-response-title>
                                </label>
                                <label>Message template
                                    <textarea name="cmn_ready_response_template" rows="8" required data-ready-response-template placeholder="Hi {candidate_name}, please arrive at {start_time} and report to {contact_name} at reception."></textarea>
                                </label>
                                <label class="cmn-inline-check">
                                    <input type="checkbox" name="cmn_ready_response_is_default" value="1" data-ready-response-default>
                                    Set as default template
                                </label>
                                <div class="cmn-settings-actions">
                                    <button class="cmn-primary" type="submit" data-ready-response-submit>Save template</button>
                                    <button class="cmn-ghost" type="button" data-ready-response-reset>Clear</button>
                                </div>
                                <div class="cmn-ready-response-preview-wrap">
                                    <button class="cmn-ghost cmn-btn-mini" type="button" data-ready-response-preview>Preview sample output</button>
                                    <pre class="cmn-ready-response-preview" data-ready-response-preview-output hidden></pre>
                                </div>
                            </form>
                            <div class="cmn-ready-response-list">
                                <?php if ($school_ready_responses) : ?>
                                    <?php foreach ($school_ready_responses as $ready_response) : ?>
                                        <?php
                                        $rr_id = (int) ($ready_response['id'] ?? 0);
                                        $rr_title = (string) ($ready_response['title'] ?? 'Template');
                                        $rr_template = (string) ($ready_response['message_template'] ?? '');
                                        $rr_default = (int) ($ready_response['is_default'] ?? 0) === 1;
                                        ?>
                                        <div class="cmn-ready-response-item">
                                            <div class="cmn-ready-response-item__main">
                                                <strong><?php echo esc_html($rr_title); ?></strong>
                                                <?php if ($rr_default) : ?><span class="cmn-status-chip is-approved">Default</span><?php endif; ?>
                                                <p class="cmn-muted"><?php echo esc_html(wp_trim_words($rr_template, 18, '…')); ?></p>
                                            </div>
                                            <div class="cmn-ready-response-item__actions">
                                                <button
                                                    class="cmn-ghost cmn-btn-mini"
                                                    type="button"
                                                    data-ready-response-edit
                                                    data-ready-response-id="<?php echo esc_attr($rr_id); ?>"
                                                    data-ready-response-title="<?php echo esc_attr($rr_title); ?>"
                                                    data-ready-response-template="<?php echo esc_attr(base64_encode($rr_template)); ?>"
                                                    data-ready-response-default="<?php echo $rr_default ? '1' : '0'; ?>"
                                                >Edit</button>
                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Delete this template?');">
                                                    <?php wp_nonce_field('cmn_ready_response_delete', 'cmn_ready_response_delete_nonce'); ?>
                                                    <input type="hidden" name="action" value="cmn_ready_response_delete">
                                                    <input type="hidden" name="cmn_ready_response_id" value="<?php echo esc_attr($rr_id); ?>">
                                                    <button class="cmn-ghost cmn-btn-mini" type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="cmn-empty">No templates saved yet.</div>
                                <?php endif; ?>
                            </div>
                            <div class="cmn-muted">
                                Available smart tags: <code>{candidate_name}</code>, <code>{school_name}</code>, <code>{booking_date}</code>, <code>{start_time}</code>, <code>{end_time}</code>, <code>{location_name}</code>, <code>{location_address}</code>, <code>{reception_instructions}</code>, <code>{parking_info}</code>, <code>{teacher_name}</code>, <code>{contact_name}</code>, <code>{contact_phone}</code>, <code>{notes}</code>.
                            </div>
                        </div>
                        <div class="cmn-dashboard-card">
                            <h3>Notifications</h3>
                            <label class="cmn-inline-check"><input type="checkbox" checked> Email notifications</label>
                            <label class="cmn-inline-check"><input type="checkbox" checked> Booking updates</label>
                        </div>
                    <?php else : ?>
                        <header class="cmn-school-header">
                            <h2>School Dashboard</h2>
                            <p>We're here to help. Find answers or send us a message below.</p>
                        </header>
                        <div class="cmn-school-dashboard-grid">
                            <div class="cmn-cover-cta">COVER ME NOW</div>
                            <div class="cmn-dashboard-card">
                                <h3>Next 7 Days Cover Status</h3>
                                <p>5 Days covered fully</p>
                                <p>2 Days partially filled</p>
                                <p>0 Days needing cover</p>
                                <button class="cmn-ghost">View Requests</button>
                            </div>
                            <div class="cmn-dashboard-card">
                                <h3>Open Cover Requests</h3>
                                <p>1 Open Cover</p>
                                <button class="cmn-ghost">View Requests</button>
                            </div>
                            <div class="cmn-dashboard-card">
                                <h3>Upcoming Absences Logged</h3>
                                <div class="cmn-calendar-grid">
                                    <div class="cmn-calendar-day">S</div>
                                    <div class="cmn-calendar-day">M</div>
                                    <div class="cmn-calendar-day">T</div>
                                    <div class="cmn-calendar-day">W</div>
                                    <div class="cmn-calendar-day">T</div>
                                    <div class="cmn-calendar-day">F</div>
                                    <div class="cmn-calendar-day">S</div>
                                    <?php for ($i = 1; $i <= 14; $i++) : ?>
                                        <div class="cmn-calendar-cell<?php echo $i % 4 === 0 ? ' is-unavailable' : ''; ?>"><span><?php echo esc_html($i); ?></span></div>
                                    <?php endfor; ?>
                                </div>
                                <button class="cmn-ghost">View Calendar</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    public function render_candidate_dashboard_shortcode() {
        $user = wp_get_current_user();
        $candidate_id = 0;
        $is_preview = isset($_GET['as']) && sanitize_text_field($_GET['as']) === 'candidate' && $this->can_preview_dashboards();
        $candidate_page = get_page_by_title('Candidate Registration');
        $candidate_url = $candidate_page ? get_permalink($candidate_page) : home_url('/candidate-registration');
        if ($user && $user->user_email) {
            $candidate = get_posts([
                'post_type' => 'cmn_candidate',
                'posts_per_page' => 1,
                'meta_query' => [
                    [
                        'key' => 'cmn_email',
                        'value' => $user->user_email,
                    ],
                ],
            ]);
            if (!empty($candidate)) {
                $candidate_id = $candidate[0]->ID;
            }
        }

        $meta = function ($key) use ($candidate_id) {
            return $candidate_id ? get_post_meta($candidate_id, $key, true) : '';
        };
        $roles = (array) $meta('cmn_roles');
        $role_label = $roles ? $roles[0] : 'Candidate';
        $candidate_user_id = $user ? (int) $user->ID : 0;
        $first_name = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'first_name', true) : '';
        $last_name = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'last_name', true) : '';
        $profile_name = trim($first_name . ' ' . $last_name);
        if ($profile_name === '') {
            $profile_name = $candidate_id ? get_the_title($candidate_id) : ($user ? $user->display_name : 'Candidate');
            $parts = preg_split('/\s+/', trim((string) $profile_name));
            if ($first_name === '') {
                $first_name = (string) ($parts[0] ?? '');
            }
            if ($last_name === '' && count($parts) > 1) {
                $last_name = (string) end($parts);
            }
        }
        $profile_email = $meta('cmn_email') ?: ($user ? $user->user_email : '');
        $profile_phone = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'phone', true) : '';
        if ($profile_phone === '') {
            $profile_phone = (string) $meta('cmn_phone');
        }
        $profile_postcode = $meta('cmn_postcode');
        $travel_distance = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'travel_radius', true) : '';
        if ($travel_distance === '') {
            $travel_distance = (string) $meta('cmn_travel_distance');
        }
        $role_type = $candidate_user_id ? (string) get_user_meta($candidate_user_id, 'role_type', true) : '';
        if ($role_type !== '') {
            $role_label = $role_type;
        }
        $doc_dbs = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, 'dbs');
        $doc_id = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, 'id');
        $doc_cv = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, 'cv');
        $doc_summary = $this->get_candidate_doc_summary([
            'dbs' => $doc_dbs,
            'id' => $doc_id,
            'cv' => $doc_cv,
        ]);
        $compliance_status_payload = $this->get_candidate_compliance_payload($candidate_id, $candidate_user_id);
        if ($candidate_id && $candidate_user_id) {
            $this->sync_candidate_admin_verification_status($candidate_id, $candidate_user_id, [
                'dbs' => $doc_dbs,
                'id' => $doc_id,
                'cv' => $doc_cv,
            ]);
        }
        $learning_opt_in = $candidate_user_id ? get_user_meta($candidate_user_id, 'cmn_learning_notify_opt_in', true) === '1' : false;

        $completion_fields = [
            $first_name,
            $last_name,
            $profile_email,
            $profile_phone,
            $role_label,
            $travel_distance,
            $doc_cv['uploaded'] ? '1' : '',
            $doc_dbs['uploaded'] ? '1' : '',
            $doc_id['uploaded'] ? '1' : '',
        ];
        $completed = 0;
        foreach ($completion_fields as $value) {
            if (!empty($value)) {
                $completed++;
            }
        }
        $completion_percent = (int) round(($completed / max(1, count($completion_fields))) * 100);
        if ($candidate_id && $candidate_user_id) {
            $completion_percent = $this->update_candidate_profile_completion($candidate_id, $candidate_user_id);
        }

        $tz = wp_timezone();
        try {
            $now = new DateTime('now', $tz);
        } catch (Exception $e) {
            $now = new DateTime('@' . current_time('timestamp'));
            $now->setTimezone($tz);
            error_log('CMN availability fallback DateTime used in candidate dashboard: ' . $e->getMessage());
        }
        $availability_window = $this->get_candidate_availability_window($now);
        $availability_allowed = !empty($availability_window['is_open']);
        $target_date = (string) ($availability_window['target_date'] ?? '');
        if (!$target_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $target_date)) {
            $target_date = date_i18n('Y-m-d', strtotime('+1 day', current_time('timestamp')));
            error_log('CMN availability fallback target_date used in candidate dashboard render.');
        }
        $already_marked = $candidate_id ? $this->has_candidate_availability($candidate_id, $target_date) : false;
        $calendar_blocked = $candidate_id ? $this->is_candidate_unavailable($candidate_id, $target_date) : false;

        $calendar_start = $now->format('Y-m-01');
        $calendar_end = (clone $now)->modify('+1 month')->format('Y-m-t');
        $calendar_min_month = $now->format('Y-m');
        $calendar_max_month = (clone $now)->modify('+1 month')->format('Y-m');
        $calendar_map = $candidate_id ? $this->get_candidate_calendar_map($candidate_id, $calendar_start, $calendar_end) : [];
        $calendar_data = $calendar_map;
        $cal_month = $now->format('Y-m');
        $month_start = new DateTime($cal_month . '-01', $tz);
        $days_in_month = (int) $month_start->format('t');
        $start_weekday = (int) $month_start->format('N'); // 1 (Mon) - 7 (Sun)

        $available_count = 0;
        $unavailable_count = 0;
        $next_available = '';
        foreach ($calendar_data as $date => $status) {
            if ($status === 'available') {
                $available_count++;
                if ($date >= $now->format('Y-m-d') && ($next_available === '' || $date < $next_available)) {
                    $next_available = $date;
                }
            }
            if ($status === 'unavailable') {
                $unavailable_count++;
            }
        }
        $tour_dismissed = $user && $user->ID ? get_user_meta($user->ID, 'cmn_candidate_tour_dismissed', true) === '1' : true;
        $next_available_label = $next_available ? (new DateTime($next_available, $tz))->format('l, F jS') : 'Not set';

        $today = current_time('Y-m-d');
        $upcoming_booking = null;
        $past_bookings = [];
        $candidate_requests = [];
        if ($candidate_id) {
            $upcoming_query = new WP_Query([
                'post_type' => 'cmn_booking',
                'posts_per_page' => 1,
                'meta_query' => [
                    [
                        'key' => 'cmn_candidate_id',
                        'value' => $candidate_id,
                    ],
                    [
                        'key' => 'cmn_status',
                        'value' => 'approved',
                    ],
                    [
                        'key' => 'cmn_date',
                        'value' => $today,
                        'compare' => '>=',
                        'type' => 'DATE',
                    ],
                ],
                'meta_key' => 'cmn_date',
                'orderby' => 'meta_value',
                'order' => 'ASC',
            ]);
            if ($upcoming_query->have_posts()) {
                $upcoming_booking = $upcoming_query->posts[0];
            }
            wp_reset_postdata();
            $past_query = new WP_Query([
                'post_type' => 'cmn_booking',
                'posts_per_page' => 5,
                'meta_query' => [
                    [
                        'key' => 'cmn_candidate_id',
                        'value' => $candidate_id,
                    ],
                    [
                        'key' => 'cmn_status',
                        'value' => 'approved',
                    ],
                    [
                        'key' => 'cmn_date',
                        'value' => $today,
                        'compare' => '<',
                        'type' => 'DATE',
                    ],
                ],
                'meta_key' => 'cmn_date',
                'orderby' => 'meta_value',
                'order' => 'DESC',
            ]);
            if ($past_query->have_posts()) {
                $past_bookings = $past_query->posts;
            }
            wp_reset_postdata();
            $candidate_requests = $this->get_candidate_requests_for_candidate($candidate_id, 20);
        }

        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        $tab = isset($_GET['candidate']) ? sanitize_text_field($_GET['candidate']) : 'dashboard';
        $nav_items = [
            'dashboard' => 'Dashboard',
            'profile' => 'Profile',
            'learning' => 'Learning Centre',
            'bookings' => 'Bookings',
            'calendar' => 'Calendar',
            'support' => 'Support',
            'settings' => 'Settings',
        ];
        $logout_url = wp_logout_url($portal_url);

        $render_static_calendar = function () use ($calendar_data, $cal_month, $start_weekday, $days_in_month) {
            ?>
            <div class="cmn-calendar-grid cmn-calendar-static">
                <div class="cmn-calendar-day">S</div>
                <div class="cmn-calendar-day">M</div>
                <div class="cmn-calendar-day">T</div>
                <div class="cmn-calendar-day">W</div>
                <div class="cmn-calendar-day">T</div>
                <div class="cmn-calendar-day">F</div>
                <div class="cmn-calendar-day">S</div>
                <?php for ($i = 1; $i < $start_weekday; $i++) : ?>
                    <div class="cmn-calendar-cell is-empty"></div>
                <?php endfor; ?>
                <?php for ($day = 1; $day <= $days_in_month; $day++) : ?>
                    <?php
                    $date = $cal_month . '-' . str_pad((string) $day, 2, '0', STR_PAD_LEFT);
                    $status = $calendar_data[$date] ?? '';
                    $classes = 'cmn-calendar-cell';
                    if ($status === 'available') {
                        $classes .= ' is-available';
                    } elseif ($status === 'unavailable') {
                        $classes .= ' is-unavailable';
                    }
                    ?>
                    <div class="<?php echo esc_attr($classes); ?>">
                        <span><?php echo esc_html($day); ?></span>
                    </div>
                <?php endfor; ?>
            </div>
            <?php
        };

        ob_start();
        ?>
        <section class="cmn-portal cmn-candidate-portal cmn-portal-light">
            <div class="cmn-portal-topbar">
                <div class="cmn-topbar-left"><?php echo $this->render_portal_branding(); ?></div>
                <div class="cmn-topbar-right">
                    <div data-tour-target="bell"><?php echo $this->render_notifications_bell($user ? $user->ID : 0); ?></div>
                    <span>Welcome, <?php echo esc_html($user ? $user->display_name : 'Candidate'); ?></span>
                    <a class="cmn-topbar-logout" href="<?php echo esc_url(wp_logout_url($portal_url)); ?>" data-tour-target="logout-top">Logout</a>
                </div>
            </div>
            <div class="cmn-candidate-shell">
                <aside class="cmn-candidate-nav">
                    <div class="cmn-candidate-nav-title">Candidate Dashboard</div>
                    <nav class="cmn-candidate-nav-links">
                        <?php foreach ($nav_items as $key => $label) : ?>
                            <?php
                            $link = $key === 'settings'
                                ? add_query_arg(['view' => 'candidate-settings'], $this->get_portal_base_url())
                                : add_query_arg(['candidate' => $key], $portal_url);
                            $active = $tab === $key ? ' is-active' : '';
                            ?>
                            <a class="cmn-candidate-nav-link<?php echo esc_attr($active); ?>" href="<?php echo esc_url($link); ?>" data-candidate-nav="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></a>
                        <?php endforeach; ?>
                        <a class="cmn-candidate-nav-link" href="<?php echo esc_url($logout_url); ?>" data-tour-target="logout-nav">Logout</a>
                    </nav>
                </aside>
                <main class="cmn-candidate-main" data-candidate-tour="<?php echo $tour_dismissed ? '0' : '1'; ?>">
                    <?php
                    if ($candidate_id) {
                        $invite_query = new WP_Query([
                            'post_type' => 'cmn_booking',
                            'posts_per_page' => 1,
                            'meta_query' => [
                                [
                                    'key' => 'cmn_candidate_id',
                                    'value' => $candidate_id,
                                ],
                                [
                                    'key' => 'cmn_status',
                                    'value' => 'candidate_invited',
                                ],
                            ],
                        ]);
                        if ($invite_query->have_posts()) :
                            $invite_query->the_post();
                            $invite_deadline = (int) get_post_meta(get_the_ID(), 'cmn_candidate_deadline', true);
                            $invite_token = get_post_meta(get_the_ID(), 'cmn_candidate_token', true);
                            $invite_remaining = $invite_deadline ? max(0, $invite_deadline - time()) : 0;
                    ?>
                        <div class="cmn-invite-banner">
                            <div>
                                <strong>New booking request</strong>
                                <span>Response needed within <?php echo esc_html(ceil($invite_remaining / 60)); ?> minutes.</span>
                            </div>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="cmn_candidate_response">
                                <input type="hidden" name="booking_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                <input type="hidden" name="token" value="<?php echo esc_attr($invite_token); ?>">
                                <button class="cmn-primary" type="submit" name="response" value="accept">Accept</button>
                                <button class="cmn-ghost" type="submit" name="response" value="decline">Decline</button>
                            </form>
                        </div>
                    <?php
                        wp_reset_postdata();
                        endif;
                    }
                    ?>
                    <?php if ($tab === 'profile') : ?>
                        <header class="cmn-candidate-header" data-tour-target="profile-tab">
                            <h2>Candidate Profile</h2>
                        </header>
                        <div class="cmn-profile-progress" data-tour-target="profile-sections" data-profile-root>
                            <div>
                                <span>Profile Completion</span>
                                <div class="cmn-progress-bar"><span data-profile-completion-bar style="width: <?php echo esc_attr($completion_percent); ?>%;"></span></div>
                            </div>
                            <strong data-profile-completion-text><?php echo esc_html($completion_percent); ?>% Complete</strong>
                            <button class="cmn-ghost" type="button" data-profile-edit="personal">Edit</button>
                        </div>
                        <div class="cmn-profile-grid">
                            <div class="cmn-dashboard-card" data-profile-personal-card>
                                <div class="cmn-card-header">
                                    <h3>Personal Details</h3>
                                    <button class="cmn-ghost" type="button" data-profile-edit="personal">Edit</button>
                                </div>
                                <div data-profile-view="personal">
                                    <p><strong data-profile-full-name><?php echo esc_html($profile_name); ?></strong></p>
                                    <p data-profile-email><?php echo esc_html($profile_email); ?></p>
                                    <p data-profile-phone><?php echo esc_html($profile_phone ?: 'Not set'); ?></p>
                                </div>
                                <form class="cmn-form cmn-inline-edit-form" data-profile-form="personal" hidden>
                                    <label>First name
                                        <input type="text" name="first_name" value="<?php echo esc_attr($first_name); ?>" required>
                                    </label>
                                    <label>Last name
                                        <input type="text" name="last_name" value="<?php echo esc_attr($last_name); ?>" required>
                                    </label>
                                    <label>Phone
                                        <input type="text" name="phone" value="<?php echo esc_attr($profile_phone); ?>" required>
                                    </label>
                                    <div class="cmn-inline-edit-actions">
                                        <button class="cmn-primary" type="submit">Save</button>
                                        <button class="cmn-ghost" type="button" data-profile-cancel="personal">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            <div class="cmn-dashboard-card" data-profile-role-card>
                                <div class="cmn-card-header">
                                    <h3>Role & Preferences</h3>
                                    <button class="cmn-ghost" type="button" data-profile-edit="role">Edit</button>
                                </div>
                                <div data-profile-view="role">
                                    <p>Role Type: <span data-profile-role><?php echo esc_html($role_label); ?></span></p>
                                    <p>Travel Radius: <span data-profile-travel><?php echo esc_html($travel_distance ?: 'Not set'); ?></span></p>
                                </div>
                                <form class="cmn-form cmn-inline-edit-form" data-profile-form="role" hidden>
                                    <label>Role type
                                        <input type="text" name="role_type" value="<?php echo esc_attr($role_label); ?>" required>
                                    </label>
                                    <label>Travel radius
                                        <input type="text" name="travel_radius" value="<?php echo esc_attr($travel_distance); ?>" required>
                                    </label>
                                    <div class="cmn-inline-edit-actions">
                                        <button class="cmn-primary" type="submit">Save</button>
                                        <button class="cmn-ghost" type="button" data-profile-cancel="role">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            <div class="cmn-dashboard-card">
                                <div class="cmn-card-header">
                                    <h3>Compliance Status</h3>
                                    <span class="cmn-status-chip <?php echo esc_attr($doc_summary['badge_class']); ?>" data-compliance-status><?php echo esc_html($doc_summary['badge_label']); ?></span>
                                </div>
                                <div class="cmn-compliance-progress" data-compliance-progress>
                                    <div class="cmn-compliance-progress-head">
                                        <span>Compliance Progress</span>
                                        <strong data-compliance-score-text><?php echo esc_html((int) ($compliance_status_payload['score'] ?? 0)); ?>%</strong>
                                    </div>
                                    <div class="cmn-progress-bar cmn-compliance-progress-bar">
                                        <span data-compliance-score-bar style="width: <?php echo esc_attr((int) ($compliance_status_payload['score'] ?? 0)); ?>%;"></span>
                                    </div>
                                </div>
                                <ul class="cmn-status-list" data-compliance-missing>
                                    <li class="<?php echo ($doc_dbs['doc_status'] ?? '') === 'approved' ? 'is-ok' : 'is-warn'; ?>" data-compliance-doc="dbs">DBS <?php echo esc_html($doc_dbs['status_label'] ?? 'Not Uploaded'); ?></li>
                                    <li class="<?php echo ($doc_cv['doc_status'] ?? '') === 'approved' ? 'is-ok' : 'is-warn'; ?>" data-compliance-doc="cv">CV <?php echo esc_html($doc_cv['status_label'] ?? 'Not Uploaded'); ?></li>
                                    <li class="<?php echo ($doc_id['doc_status'] ?? '') === 'approved' ? 'is-ok' : 'is-warn'; ?>" data-compliance-doc="id">ID <?php echo esc_html($doc_id['status_label'] ?? 'Not Uploaded'); ?></li>
                                </ul>
                            </div>
                            <div class="cmn-dashboard-card">
                                <div class="cmn-card-header">
                                    <h3>Documents Upload</h3>
                                    <span class="cmn-status-chip <?php echo esc_attr($doc_summary['badge_class']); ?>"><?php echo esc_html($doc_summary['badge_label']); ?></span>
                                </div>
                                <div class="cmn-doc-actions" data-candidate-docs>
                                    <div class="cmn-doc-card cmn-doc-tile" data-doc-row="id" data-doc-type="id">
                                        <div class="cmn-doc-card-head">
                                            <strong>Photo ID</strong>
                                            <span class="cmn-status-chip <?php echo esc_attr($doc_id['badge_class'] ?? ($doc_id['uploaded'] ? 'is-pending' : 'is-declined')); ?>" data-doc-badge="id" data-doc-state="<?php echo esc_attr($doc_id['doc_status'] ?? ($doc_id['uploaded'] ? 'pending' : 'not_uploaded')); ?>"><?php echo esc_html($doc_id['status_label'] ?? ($doc_id['uploaded'] ? 'Pending Review' : 'Not Uploaded')); ?></span>
                                        </div>
                                        <div class="cmn-doc-card-meta">
                                            <div class="cmn-doc-line" data-doc-status="id"><?php echo esc_html($doc_id['uploaded'] ? $doc_id['filename'] : 'Not uploaded'); ?></div>
                                            <div class="cmn-doc-subline">
                                                <span data-doc-date="id"><?php echo esc_html($doc_id['uploaded_at'] ? date_i18n('M j, Y g:ia', strtotime($doc_id['uploaded_at'])) : '-'); ?></span>
                                                <span data-doc-size="id"><?php echo esc_html($doc_id['filesize_label'] ?: '-'); ?></span>
                                            </div>
                                            <?php if (($doc_id['doc_status'] ?? '') === 'rejected' && !empty($doc_id['review_reason'])) : ?>
                                                <div class="cmn-doc-subline cmn-doc-reason-row"><span data-doc-reason="id">Reason: <?php echo esc_html($doc_id['review_reason']); ?></span></div>
                                            <?php else : ?>
                                                <div class="cmn-doc-subline cmn-doc-reason-row" data-doc-reason="id"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="cmn-doc-buttons">
                                            <input type="file" class="cmn-hidden-input" data-doc-input="id" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                            <button class="<?php echo $doc_id['uploaded'] ? 'cmn-ghost' : 'cmn-primary'; ?>" type="button" data-doc-upload-trigger="id"><?php echo $doc_id['uploaded'] ? 'Replace' : 'Upload'; ?></button>
                                            <button class="cmn-ghost" type="button" data-doc-view="id"<?php echo $doc_id['uploaded'] ? '' : ' disabled'; ?>>View / Download</button>
                                            <button class="cmn-ghost" type="button" data-doc-delete="id"<?php echo $doc_id['uploaded'] ? '' : ' disabled'; ?>>Remove</button>
                                        </div>
                                        <div class="cmn-doc-progress" data-doc-progress="id" hidden><span style="width:0%"></span></div>
                                    </div>
                                    <div class="cmn-doc-card cmn-doc-tile" data-doc-row="dbs" data-doc-type="dbs">
                                        <div class="cmn-doc-card-head">
                                            <strong>DBS</strong>
                                            <span class="cmn-status-chip <?php echo esc_attr($doc_dbs['badge_class'] ?? ($doc_dbs['uploaded'] ? 'is-pending' : 'is-declined')); ?>" data-doc-badge="dbs" data-doc-state="<?php echo esc_attr($doc_dbs['doc_status'] ?? ($doc_dbs['uploaded'] ? 'pending' : 'not_uploaded')); ?>"><?php echo esc_html($doc_dbs['status_label'] ?? ($doc_dbs['uploaded'] ? 'Pending Review' : 'Not Uploaded')); ?></span>
                                        </div>
                                        <div class="cmn-doc-card-meta">
                                            <div class="cmn-doc-line" data-doc-status="dbs"><?php echo esc_html($doc_dbs['uploaded'] ? $doc_dbs['filename'] : 'Not uploaded'); ?></div>
                                            <div class="cmn-doc-subline">
                                                <span data-doc-date="dbs"><?php echo esc_html($doc_dbs['uploaded_at'] ? date_i18n('M j, Y g:ia', strtotime($doc_dbs['uploaded_at'])) : '-'); ?></span>
                                                <span data-doc-size="dbs"><?php echo esc_html($doc_dbs['filesize_label'] ?: '-'); ?></span>
                                            </div>
                                            <?php if (($doc_dbs['doc_status'] ?? '') === 'rejected' && !empty($doc_dbs['review_reason'])) : ?>
                                                <div class="cmn-doc-subline cmn-doc-reason-row"><span data-doc-reason="dbs">Reason: <?php echo esc_html($doc_dbs['review_reason']); ?></span></div>
                                            <?php else : ?>
                                                <div class="cmn-doc-subline cmn-doc-reason-row" data-doc-reason="dbs"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="cmn-doc-buttons">
                                            <input type="file" class="cmn-hidden-input" data-doc-input="dbs" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                            <button class="<?php echo $doc_dbs['uploaded'] ? 'cmn-ghost' : 'cmn-primary'; ?>" type="button" data-doc-upload-trigger="dbs"><?php echo $doc_dbs['uploaded'] ? 'Replace' : 'Upload'; ?></button>
                                            <button class="cmn-ghost" type="button" data-doc-view="dbs"<?php echo $doc_dbs['uploaded'] ? '' : ' disabled'; ?>>View / Download</button>
                                            <button class="cmn-ghost" type="button" data-doc-delete="dbs"<?php echo $doc_dbs['uploaded'] ? '' : ' disabled'; ?>>Remove</button>
                                        </div>
                                        <div class="cmn-doc-progress" data-doc-progress="dbs" hidden><span style="width:0%"></span></div>
                                    </div>
                                    <div class="cmn-doc-card cmn-doc-tile" data-doc-row="cv" data-doc-type="cv">
                                        <div class="cmn-doc-card-head">
                                            <strong>CV</strong>
                                            <span class="cmn-status-chip <?php echo esc_attr($doc_cv['badge_class'] ?? ($doc_cv['uploaded'] ? 'is-pending' : 'is-declined')); ?>" data-doc-badge="cv" data-doc-state="<?php echo esc_attr($doc_cv['doc_status'] ?? ($doc_cv['uploaded'] ? 'pending' : 'not_uploaded')); ?>"><?php echo esc_html($doc_cv['status_label'] ?? ($doc_cv['uploaded'] ? 'Pending Review' : 'Not Uploaded')); ?></span>
                                        </div>
                                        <div class="cmn-doc-card-meta">
                                            <div class="cmn-doc-line" data-doc-status="cv"><?php echo esc_html($doc_cv['uploaded'] ? $doc_cv['filename'] : 'Not uploaded'); ?></div>
                                            <div class="cmn-doc-subline">
                                                <span data-doc-date="cv"><?php echo esc_html($doc_cv['uploaded_at'] ? date_i18n('M j, Y g:ia', strtotime($doc_cv['uploaded_at'])) : '-'); ?></span>
                                                <span data-doc-size="cv"><?php echo esc_html($doc_cv['filesize_label'] ?: '-'); ?></span>
                                            </div>
                                            <?php if (($doc_cv['doc_status'] ?? '') === 'rejected' && !empty($doc_cv['review_reason'])) : ?>
                                                <div class="cmn-doc-subline cmn-doc-reason-row"><span data-doc-reason="cv">Reason: <?php echo esc_html($doc_cv['review_reason']); ?></span></div>
                                            <?php else : ?>
                                                <div class="cmn-doc-subline cmn-doc-reason-row" data-doc-reason="cv"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="cmn-doc-buttons">
                                            <input type="file" class="cmn-hidden-input" data-doc-input="cv" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                            <button class="<?php echo $doc_cv['uploaded'] ? 'cmn-ghost' : 'cmn-primary'; ?>" type="button" data-doc-upload-trigger="cv"><?php echo $doc_cv['uploaded'] ? 'Replace' : 'Upload'; ?></button>
                                            <button class="cmn-ghost" type="button" data-doc-view="cv"<?php echo $doc_cv['uploaded'] ? '' : ' disabled'; ?>>View / Download</button>
                                            <button class="cmn-ghost" type="button" data-doc-delete="cv"<?php echo $doc_cv['uploaded'] ? '' : ' disabled'; ?>>Remove</button>
                                        </div>
                                        <div class="cmn-doc-progress" data-doc-progress="cv" hidden><span style="width:0%"></span></div>
                                    </div>
                                    <div class="cmn-muted" data-doc-message></div>
                                </div>
                            </div>
                            <div class="cmn-dashboard-card cmn-dashboard-card-wide">
                                <div class="cmn-card-header">
                                    <h3>Admin Verification Status</h3>
                                    <span class="cmn-status-chip <?php echo esc_attr($doc_summary['badge_class']); ?>" data-admin-verification-status><?php echo esc_html($doc_summary['badge_label']); ?></span>
                                </div>
                                <p data-admin-verification-copy><?php echo esc_html($doc_summary['copy']); ?></p>
                            </div>
                        </div>
                    <?php elseif ($tab === 'calendar') : ?>
                        <header class="cmn-candidate-header" data-tour-target="calendar-tab">
                            <h2>Manage Availability</h2>
                            <p>Tap dates to mark yourself available (green) or unavailable (red).</p>
                        </header>
                        <div class="cmn-availability-layout">
                            <div class="cmn-availability-left">
                                <div class="cmn-dashboard-card cmn-profile-summary">
                                    <div class="cmn-profile-summary-header">
                                        <div class="cmn-profile-avatar"><?php echo esc_html(strtoupper(substr($profile_name, 0, 1))); ?></div>
                                        <div>
                                            <strong><?php echo esc_html($profile_name); ?></strong>
                                            <span><?php echo esc_html($role_label); ?></span>
                                            <div class="cmn-profile-progress-line">
                                                <span>Profile <?php echo esc_html($completion_percent); ?>% Complete</span>
                                                <div class="cmn-progress-bar"><span style="width: <?php echo esc_attr($completion_percent); ?>%;"></span></div>
                                            </div>
                                            <p class="cmn-profile-alert"><?php echo $doc_dbs['uploaded'] ? 'DBS Verified' : 'Unverified DBS Check'; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="cmn-dashboard-card">
                                    <div class="cmn-card-header">
                                        <h3>Personal Details</h3>
                                        <button class="cmn-ghost" type="button">Edit</button>
                                    </div>
                                    <p>Email: <?php echo esc_html($profile_email); ?></p>
                                    <p>Mobile: <?php echo esc_html($profile_phone ?: 'Not set'); ?></p>
                                    <p>Postcode: <?php echo esc_html($profile_postcode ?: 'Not set'); ?></p>
                                    <p>Role Type: <?php echo esc_html($role_label); ?></p>
                                </div>
                                <div class="cmn-dashboard-card">
                                    <div class="cmn-card-header">
                                        <h3>Compliance Documents</h3>
                                        <button class="cmn-ghost" type="button">Edit</button>
                                    </div>
                                    <ul class="cmn-status-list">
                                        <li class="<?php echo $doc_cv['uploaded'] ? 'is-ok' : 'is-warn'; ?>">Upload CV <span><?php echo $doc_cv['uploaded'] ? 'Completed' : 'Required'; ?></span></li>
                                        <li class="<?php echo $doc_dbs['uploaded'] ? 'is-ok' : 'is-warn'; ?>">Upload DBS <span><?php echo $doc_dbs['uploaded'] ? 'Completed' : 'Required'; ?></span></li>
                                        <li class="<?php echo $doc_id['uploaded'] ? 'is-ok' : 'is-warn'; ?>">Upload Photo ID <span><?php echo $doc_id['uploaded'] ? 'Completed' : 'Required'; ?></span></li>
                                    </ul>
                                    <button class="cmn-primary" type="button">Request DBS Verification</button>
                                </div>
                            </div>
                            <div class="cmn-availability-right">
                                <div class="cmn-dashboard-card cmn-availability-card-wide">
                                    <form class="cmn-calendar-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                        <?php wp_nonce_field('cmn_save_calendar', 'cmn_save_calendar_nonce'); ?>
                                        <input type="hidden" name="action" value="cmn_save_calendar">
                                        <input type="hidden" name="cmn_calendar_month" value="<?php echo esc_attr($cal_month); ?>">
                                        <input type="hidden" name="cmn_calendar_data" value="<?php echo esc_attr(wp_json_encode($calendar_data)); ?>">
                                        <div class="cmn-calendar-grid">
                                            <div class="cmn-calendar-day">S</div>
                                            <div class="cmn-calendar-day">M</div>
                                            <div class="cmn-calendar-day">T</div>
                                            <div class="cmn-calendar-day">W</div>
                                            <div class="cmn-calendar-day">T</div>
                                            <div class="cmn-calendar-day">F</div>
                                            <div class="cmn-calendar-day">S</div>
                                            <?php for ($i = 1; $i < $start_weekday; $i++) : ?>
                                                <div class="cmn-calendar-cell is-empty"></div>
                                            <?php endfor; ?>
                                            <?php for ($day = 1; $day <= $days_in_month; $day++) : ?>
                                                <?php
                                                $date = $cal_month . '-' . str_pad((string) $day, 2, '0', STR_PAD_LEFT);
                                                $status = $calendar_data[$date] ?? '';
                                                $classes = 'cmn-calendar-cell';
                                                if ($status === 'available') {
                                                    $classes .= ' is-available';
                                                } elseif ($status === 'unavailable') {
                                                    $classes .= ' is-unavailable';
                                                }
                                                ?>
                                                <button type="button" class="<?php echo esc_attr($classes); ?>" data-date="<?php echo esc_attr($date); ?>">
                                                    <span><?php echo esc_html($day); ?></span>
                                                </button>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="cmn-availability-stats">
                                            <div><span class="cmn-dot is-available"></span> Next Available Date: <strong><?php echo esc_html($next_available_label); ?></strong></div>
                                            <div><span class="cmn-dot is-available"></span> <?php echo esc_html($available_count); ?> Available Days Marked</div>
                                            <div><span class="cmn-dot is-unavailable"></span> <?php echo esc_html($unavailable_count); ?> Unavailable Days Marked</div>
                                        </div>
                                        <div class="cmn-availability-actions">
                                            <button class="cmn-ghost" type="button" data-calendar-set="clear">Clear Availability</button>
                                            <button class="cmn-primary" type="submit">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($tab === 'learning') : ?>
                        <header class="cmn-candidate-header" data-tour-target="learning-centre">
                            <h2>Learning Centre - Coming soon</h2>
                        </header>
                        <div class="cmn-learning-grid">
                            <div class="cmn-dashboard-card">
                                <h3>Learning Centre - Coming soon</h3>
                                <p>We’re adding training and resources soon.</p>
                                <label class="cmn-inline-check">
                                    <input type="checkbox" data-learning-opt-in <?php echo $learning_opt_in ? 'checked' : ''; ?>>
                                    Notify me when courses are available
                                </label>
                                <button class="cmn-primary" type="button" data-learning-save>Save preference</button>
                                <div class="cmn-muted" data-learning-message>
                                    <?php echo $learning_opt_in ? 'You’ll be notified when courses go live.' : ''; ?>
                                </div>
                            </div>
                            <div class="cmn-dashboard-card" data-tour-target="certificates">
                                <h3>Certificates</h3>
                                <p>Upload and manage your compliance documents.</p>
                                <button class="cmn-ghost" type="button">View Certificates</button>
                            </div>
                        </div>
                    <?php elseif ($tab === 'settings') : ?>
                        <?php
                        $current_candidate_user_id = get_current_user_id();
                        $candidate_email_confirm = '';
                        if ($current_candidate_user_id) {
                            $candidate_user_obj = get_user_by('id', $current_candidate_user_id);
                            $candidate_email_confirm = $candidate_user_obj ? (string) $candidate_user_obj->user_email : '';
                        }
                        $deletion_requested = $current_candidate_user_id ? get_user_meta($current_candidate_user_id, 'cmn_deletion_requested', true) : '';
                        $deletion_requested_at = $current_candidate_user_id ? (string) get_user_meta($current_candidate_user_id, 'cmn_deletion_requested_at', true) : '';
                        ?>
                        <header class="cmn-candidate-header" data-tour-target="settings">
                            <h2>Settings</h2>
                        </header>
                        <div class="cmn-dashboard-card" data-candidate-settings>
                            <h3>Appearance</h3>
                            <p class="cmn-muted">Choose your portal colour scheme.</p>
                            <label>Theme
                                <select name="cmn_theme" data-settings-theme>
                                    <?php foreach ($this->get_theme_scheme_choices() as $theme_key => $theme_label) : ?>
                                        <option value="<?php echo esc_attr($theme_key); ?>"><?php echo esc_html($theme_label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <h3>Email Notifications</h3>
                            <p class="cmn-muted">Control which email updates you receive.</p>
                            <div class="cmn-settings-grid">
                                <label class="cmn-inline-check"><input type="checkbox" data-settings-pref="cmn_notify_email_support_updates"> Support updates</label>
                                <label class="cmn-inline-check"><input type="checkbox" data-settings-pref="cmn_notify_email_booking_request"> Booking requests</label>
                                <label class="cmn-inline-check"><input type="checkbox" data-settings-pref="cmn_notify_email_booking_confirmed"> Booking confirmations</label>
                                <label class="cmn-inline-check"><input type="checkbox" data-settings-pref="cmn_notify_email_booking_cancelled"> Booking cancellations</label>
                                <label class="cmn-inline-check"><input type="checkbox" data-settings-pref="cmn_notify_email_profile_reminders"> Profile reminders</label>
                                <label class="cmn-inline-check"><input type="checkbox" data-settings-pref="cmn_notify_email_learning_courses"> Learning course updates</label>
                            </div>
                            <div class="cmn-settings-actions">
                                <button class="cmn-primary" type="button" data-settings-save>Save settings</button>
                                <span class="cmn-muted" data-settings-message></span>
                            </div>
                            <div class="cmn-settings-danger-zone">
                                <h3>Delete Account</h3>
                                <p class="cmn-muted">This will permanently remove your access and delete your profile. This cannot be undone.</p>
                                <label>Type your email to confirm
                                    <input type="email" data-delete-confirm-email placeholder="<?php echo esc_attr($candidate_email_confirm); ?>" <?php echo $deletion_requested ? 'disabled' : ''; ?>>
                                </label>
                                <div class="cmn-settings-actions">
                                    <button class="cmn-danger" type="button" data-delete-request-btn <?php echo $deletion_requested ? 'disabled' : ''; ?>>Request account deletion</button>
                                    <span class="cmn-muted" data-delete-request-msg>
                                        <?php if ($deletion_requested) : ?>
                                            Pending admin action<?php echo $deletion_requested_at ? ' since ' . esc_html(date_i18n('M j, Y g:ia', strtotime($deletion_requested_at))) : ''; ?>.
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($tab === 'bookings') : ?>
                        <header class="cmn-candidate-header" data-tour-target="bookings-tab">
                            <h2>Bookings</h2>
                        </header>
                        <?php $booking_notice = isset($_GET['cmn_notice']) ? sanitize_text_field(wp_unslash($_GET['cmn_notice'])) : ''; ?>
                        <?php if ($booking_notice) : ?>
                            <div class="cmn-register-success"><?php echo esc_html($booking_notice); ?></div>
                        <?php endif; ?>
                        <div class="cmn-dashboard-card">
                            <div class="cmn-card-header">
                                <h3>Booking Requests</h3>
                                <span class="cmn-muted">15-minute response window</span>
                            </div>
                            <?php if ($candidate_requests) : ?>
                                <div class="cmn-list">
                                    <?php foreach ($candidate_requests as $request) : ?>
                                        <?php
                                        $request_id = (int) ($request['id'] ?? 0);
                                        $request_status = strtolower((string) ($request['status'] ?? 'requested'));
                                        $expires_at = $this->get_request_expires_at($request);
                                        $is_expired = $this->is_request_expired($request);
                                        if ($request_status === 'requested' && $is_expired) {
                                            $request_status = 'expired';
                                        }
                                        $requested_date = sanitize_text_field($request['requested_date'] ?? '');
                                        $requested_label = $requested_date ? date_i18n('l, F jS', strtotime($requested_date)) : 'Tomorrow';
                                        $candidate_rate = isset($request['candidate_pay_rate']) ? (float) $request['candidate_pay_rate'] : 0.0;
                                        $location_label = '';
                                        $school_id_for_request = (int) ($request['school_id'] ?? 0);
                                        if (!$school_id_for_request && !empty($request['school_email_domain'])) {
                                            $school_id_for_request = $this->get_school_post_id_by_domain((string) $request['school_email_domain']);
                                        }
                                        if ($school_id_for_request) {
                                            $location_label = (string) get_post_meta($school_id_for_request, 'cmn_location', true);
                                        }
                                        $booking_id_for_request = $this->get_booking_id_for_request($request_id);
                                        ?>
                                        <div class="cmn-list-item cmn-request-card">
                                            <strong><?php echo esc_html($requested_label); ?></strong>
                                            <span><?php echo esc_html($location_label ?: 'Location shared after acceptance'); ?></span>
                                            <span class="cmn-pill cmn-pill--status">Pay: £<?php echo esc_html(number_format($candidate_rate, 2)); ?></span>
                                            <span class="cmn-pill cmn-pill--<?php echo esc_attr($request_status); ?>"><?php echo esc_html(ucfirst(str_replace('_', ' ', $request_status))); ?></span>
                                            <?php if ($request_status === 'requested') : ?>
                                                <?php if ($expires_at) : ?>
                                                    <small class="cmn-request-countdown" data-request-expires="<?php echo esc_attr(gmdate('c', strtotime($expires_at))); ?>">Time remaining...</small>
                                                <?php endif; ?>
                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                                    <?php wp_nonce_field('cmn_candidate_request_action', 'cmn_candidate_request_action_nonce'); ?>
                                                    <input type="hidden" name="action" value="cmn_candidate_request_action">
                                                    <input type="hidden" name="cmn_request_id" value="<?php echo esc_attr($request_id); ?>">
                                                    <input type="hidden" name="cmn_request_action" value="accept">
                                                    <button class="cmn-primary" type="submit">Accept</button>
                                                </form>
                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                                    <?php wp_nonce_field('cmn_candidate_request_action', 'cmn_candidate_request_action_nonce'); ?>
                                                    <input type="hidden" name="action" value="cmn_candidate_request_action">
                                                    <input type="hidden" name="cmn_request_id" value="<?php echo esc_attr($request_id); ?>">
                                                    <input type="hidden" name="cmn_request_action" value="decline">
                                                    <input type="text" name="cmn_decline_reason" placeholder="Reason (optional)">
                                                    <button class="cmn-ghost" type="submit">Decline</button>
                                                </form>
                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                                    <?php wp_nonce_field('cmn_candidate_request_action', 'cmn_candidate_request_action_nonce'); ?>
                                                    <input type="hidden" name="action" value="cmn_candidate_request_action">
                                                    <input type="hidden" name="cmn_request_id" value="<?php echo esc_attr($request_id); ?>">
                                                    <input type="hidden" name="cmn_request_action" value="negotiate">
                                                    <button class="cmn-ghost" type="submit">Negotiate rate</button>
                                                </form>
                                            <?php elseif ($request_status === 'expired') : ?>
                                                <small class="cmn-muted">Request expired.</small>
                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                                    <?php wp_nonce_field('cmn_candidate_request_action', 'cmn_candidate_request_action_nonce'); ?>
                                                    <input type="hidden" name="action" value="cmn_candidate_request_action">
                                                    <input type="hidden" name="cmn_request_id" value="<?php echo esc_attr($request_id); ?>">
                                                    <input type="hidden" name="cmn_request_action" value="still_needed">
                                                    <button class="cmn-ghost" type="submit">Request still needed?</button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($booking_id_for_request && in_array($request_status, ['accepted', 'confirmed'], true)) : ?>
                                                <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['candidate' => 'bookings', 'cmn_booking_chat' => $booking_id_for_request, 'cmn_thread_type' => 'booking_details'], $portal_url)); ?>">Open booking chat</a>
                                            <?php endif; ?>
                                            <?php if ($booking_id_for_request && $request_status === 'requested') : ?>
                                                <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['candidate' => 'bookings', 'cmn_booking_chat' => $booking_id_for_request, 'cmn_thread_type' => 'pay_negotiation'], $portal_url)); ?>">Open pay negotiation chat</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="cmn-empty">No booking requests yet.</div>
                            <?php endif; ?>
                        </div>
                        <?php
                        $chat_booking_id = isset($_GET['cmn_booking_chat']) ? (int) $_GET['cmn_booking_chat'] : 0;
                        $chat_thread_type = sanitize_key((string) ($_GET['cmn_thread_type'] ?? 'booking_details'));
                        if (!in_array($chat_thread_type, ['booking_details', 'pay_negotiation', 'decline_followup'], true)) {
                            $chat_thread_type = 'booking_details';
                        }
                        if ($chat_booking_id) :
                            $chat_thread = $this->get_booking_thread_by_booking($chat_booking_id, $chat_thread_type);
                            if ($chat_thread && $this->user_can_access_booking_thread((int) $chat_thread['id'], get_current_user_id())) :
                                $chat_ack = get_user_meta(get_current_user_id(), 'cmn_booking_chat_ack_' . $chat_booking_id, true) === '1';
                                $candidate_booking_ref = get_the_title($chat_booking_id);
                                if (!$candidate_booking_ref) {
                                    $candidate_booking_ref = 'Booking #' . (int) $chat_booking_id;
                                }
                        ?>
                            <div class="cmn-dashboard-card">
                                <div class="cmn-card-header">
                                    <h3><?php echo esc_html($chat_thread_type === 'pay_negotiation' ? 'Pay Negotiation Chat' : 'Booking Chat'); ?></h3>
                                    <span class="cmn-muted"><?php echo esc_html($candidate_booking_ref); ?></span>
                                </div>
                                <?php if ($chat_thread_type === 'booking_details' && !$chat_ack) : ?>
                                    <div class="cmn-booking-disclaimer-modal" data-booking-disclaimer-modal>
                                        <div class="cmn-booking-disclaimer-modal__backdrop"></div>
                                        <div class="cmn-booking-disclaimer-modal__card">
                                            <h4>Booking chat</h4>
                                            <p>You are about to be placed in a chat with the school, the account manager, and yourself. Pay must not be discussed in this chat.</p>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-inline">
                                                <?php wp_nonce_field('cmn_booking_chat_ack', 'cmn_booking_chat_ack_nonce'); ?>
                                                <input type="hidden" name="action" value="cmn_booking_chat_ack">
                                                <input type="hidden" name="cmn_booking_id" value="<?php echo esc_attr($chat_booking_id); ?>">
                                                <button class="cmn-primary" type="submit">I understand - continue</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <div class="cmn-booking-feedback-summary" data-booking-feedback-summary></div>
                                    <div class="cmn-support-messages" data-booking-chat data-thread-id="<?php echo esc_attr((int) $chat_thread['id']); ?>" data-thread-role="candidate" data-booking-id="<?php echo esc_attr($chat_booking_id); ?>" data-feedback-role="candidate">
                                        <?php foreach ($this->get_booking_thread_messages((int) $chat_thread['id']) as $chat_msg) : ?>
                                            <?php
                                            $chat_sender_role = sanitize_key((string) ($chat_msg['sender_role_type'] ?? 'system'));
                                            $chat_class = 'is-system';
                                            if ($chat_sender_role === 'candidate') {
                                                $chat_class = 'is-user is-candidate';
                                            } elseif (in_array($chat_sender_role, ['school', 'account_manager', 'admin'], true)) {
                                                $chat_class = 'is-admin is-' . $chat_sender_role;
                                            }
                                            ?>
                                            <div class="cmn-support-bubble cmn-booking-bubble <?php echo esc_attr($chat_class); ?>">
                                                <div class="cmn-support-meta"><?php echo esc_html(ucfirst(str_replace('_', ' ', $chat_msg['sender_role_type']))); ?> · <?php echo esc_html(date_i18n('M j, g:ia', strtotime($chat_msg['created_at']))); ?></div>
                                                <div class="cmn-support-text"><?php echo esc_html($chat_msg['message']); ?></div>
                                                <?php $chat_attachments = $this->get_booking_message_attachments($chat_msg); ?>
                                                <?php if ($chat_attachments) : ?>
                                                    <div class="cmn-support-attachments">
                                                        <?php foreach ($chat_attachments as $attachment) : ?>
                                                            <a class="cmn-support-attachment-chip" href="<?php echo esc_url($attachment['url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($attachment['filename']); ?></a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cmn-support-reply" enctype="multipart/form-data">
                                        <?php wp_nonce_field('cmn_booking_chat', 'cmn_booking_chat_nonce'); ?>
                                        <input type="hidden" name="action" value="cmn_booking_chat_post">
                                        <input type="hidden" name="cmn_thread_id" value="<?php echo esc_attr((int) $chat_thread['id']); ?>">
                                        <textarea name="cmn_message" rows="3" required placeholder="Type your message..."></textarea>
                                        <input type="file" name="cmn_attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.txt">
                                        <button class="cmn-primary" type="submit">Send</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="cmn-booking-feedback-modal" data-booking-feedback-modal hidden>
                                <div class="cmn-booking-feedback-modal__overlay"></div>
                                <div class="cmn-booking-feedback-modal__card" role="dialog" aria-modal="true" aria-labelledby="cmn-booking-feedback-title-candidate">
                                    <h3 id="cmn-booking-feedback-title-candidate">Rate School</h3>
                                    <p class="cmn-muted" data-booking-feedback-subtitle></p>
                                    <form class="cmn-booking-feedback-form" data-booking-feedback-form>
                                        <input type="hidden" name="booking_id" value="<?php echo esc_attr($chat_booking_id); ?>">
                                        <div class="cmn-feedback-question">
                                            <label data-booking-feedback-label="stars_1">Check-in/Reception</label>
                                            <div class="cmn-star-picker" data-booking-feedback-stars="stars_1"></div>
                                            <input type="hidden" name="stars_1" value="">
                                            <span class="cmn-star-score" data-booking-feedback-score="stars_1">0/5</span>
                                        </div>
                                        <div class="cmn-feedback-question">
                                            <label data-booking-feedback-label="stars_2">Clarity of Instructions</label>
                                            <div class="cmn-star-picker" data-booking-feedback-stars="stars_2"></div>
                                            <input type="hidden" name="stars_2" value="">
                                            <span class="cmn-star-score" data-booking-feedback-score="stars_2">0/5</span>
                                        </div>
                                        <div class="cmn-feedback-question">
                                            <label data-booking-feedback-label="stars_3">Support On-site</label>
                                            <div class="cmn-star-picker" data-booking-feedback-stars="stars_3"></div>
                                            <input type="hidden" name="stars_3" value="">
                                            <span class="cmn-star-score" data-booking-feedback-score="stars_3">0/5</span>
                                        </div>
                                        <div class="cmn-feedback-question">
                                            <label data-booking-feedback-label="stars_overall">Overall</label>
                                            <div class="cmn-star-picker" data-booking-feedback-stars="stars_overall"></div>
                                            <input type="hidden" name="stars_overall" value="">
                                            <span class="cmn-star-score" data-booking-feedback-score="stars_overall">0/5</span>
                                        </div>
                                        <div class="cmn-feedback-question" data-booking-feedback-would-rebook hidden>
                                            <label>Would you work here again?</label>
                                            <div class="cmn-feedback-toggle" data-booking-feedback-toggle>
                                                <button type="button" class="cmn-ghost" data-booking-feedback-toggle-value="1">Yes</button>
                                                <button type="button" class="cmn-ghost" data-booking-feedback-toggle-value="0">No</button>
                                            </div>
                                            <input type="hidden" name="would_rebook" value="">
                                        </div>
                                        <div class="cmn-feedback-question">
                                            <label>Tags</label>
                                            <div class="cmn-feedback-tags" data-booking-feedback-tags></div>
                                        </div>
                                        <div class="cmn-feedback-question">
                                            <label>Optional comments</label>
                                            <textarea name="comment" rows="3"></textarea>
                                        </div>
                                        <div class="cmn-settings-actions">
                                            <button class="cmn-primary" type="submit">Submit feedback</button>
                                            <span class="cmn-muted" data-booking-feedback-msg></span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php
                            endif;
                        endif;
                        ?>
                        <?php
                        $booking_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'upcoming';
                        $booking_args = [];
                        if ($candidate_id) {
                            $booking_args = [
                                'post_type' => 'cmn_booking',
                                'posts_per_page' => 20,
                                'meta_query' => [
                                    [
                                        'key' => 'cmn_candidate_id',
                                        'value' => $candidate_id,
                                    ],
                                ],
                            ];
                        }
                        $booking_query = $booking_args ? new WP_Query($booking_args) : null;
                        ?>
                        <div class="cmn-bookings-toolbar">
                            <div class="cmn-booking-tabs">
                                <a class="cmn-booking-tab<?php echo $booking_tab === 'upcoming' ? ' is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['candidate' => 'bookings', 'tab' => 'upcoming'], $portal_url)); ?>">Upcoming</a>
                                <a class="cmn-booking-tab<?php echo $booking_tab === 'past' ? ' is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['candidate' => 'bookings', 'tab' => 'past'], $portal_url)); ?>">Past</a>
                            </div>
                        </div>
                        <div class="cmn-booking-table">
                            <div class="cmn-booking-row header">
                                <div>Date</div>
                                <div>School</div>
                                <div>Role</div>
                                <div>Status</div>
                                <div>Feedback</div>
                            </div>
                            <?php if ($booking_query && $booking_query->have_posts()) : ?>
                                <?php while ($booking_query->have_posts()) : $booking_query->the_post(); ?>
                                    <?php
                                    $booking_id = get_the_ID();
                                    $status_val = get_post_meta(get_the_ID(), 'cmn_status', true);
                                    $school_id = (int) get_post_meta(get_the_ID(), 'cmn_school_id', true);
                                    $school_name = $school_id ? get_the_title($school_id) : 'School';
                                    $role = get_post_meta(get_the_ID(), 'cmn_role', true);
                                    $date = get_post_meta(get_the_ID(), 'cmn_start_date', true) ?: get_post_meta(get_the_ID(), 'cmn_date', true);
                                    $deadline = (int) get_post_meta(get_the_ID(), 'cmn_candidate_deadline', true);
                                    $token = get_post_meta(get_the_ID(), 'cmn_candidate_token', true);
                                    $remaining = $deadline ? max(0, $deadline - time()) : 0;
                                    $feedback_eligible = $this->is_booking_feedback_eligible($booking_id);
                                    $feedback_payload = $feedback_eligible ? $this->get_booking_feedback_payload($booking_id, get_current_user_id(), 'candidate') : null;
                                    $feedback_submitted = !empty($feedback_payload['viewer_feedback']);
                                    ?>
                                    <div class="cmn-booking-row">
                                        <div><strong><?php echo esc_html($date ?: '—'); ?></strong></div>
                                        <div><?php echo esc_html($school_name); ?></div>
                                        <div><?php echo esc_html($role); ?></div>
                                        <div class="cmn-booking-status<?php echo $status_val === 'candidate_invited' ? ' is-pending' : ''; ?>"><?php echo esc_html($status_val); ?></div>
                                        <div>
                                            <?php if ($feedback_eligible && !$feedback_submitted) : ?>
                                                <span class="cmn-status-chip is-pending">Pending</span>
                                                <a class="cmn-ghost cmn-btn-mini" href="<?php echo esc_url(add_query_arg(['candidate' => 'bookings', 'cmn_booking_chat' => $booking_id, 'cmn_thread_type' => 'booking_details', 'cmn_feedback_prompt' => '1'], $portal_url)); ?>">Leave feedback</a>
                                            <?php elseif ($feedback_submitted) : ?>
                                                <span class="cmn-status-chip is-approved">Submitted</span>
                                            <?php else : ?>
                                                <span class="cmn-muted">—</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($status_val === 'candidate_invited' && $token && $remaining > 0) : ?>
                                        <div class="cmn-booking-response">
                                            <span>Response required within <?php echo esc_html(ceil($remaining / 60)); ?> mins.</span>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                <input type="hidden" name="action" value="cmn_candidate_response">
                                                <input type="hidden" name="booking_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                                <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
                                                <button class="cmn-primary" type="submit" name="response" value="accept">Accept</button>
                                                <button class="cmn-ghost" type="submit" name="response" value="decline">Decline</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                <?php endwhile; wp_reset_postdata(); ?>
                            <?php else : ?>
                                <div class="cmn-booking-empty">No bookings currently.</div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($tab === 'support') : ?>
                        <header class="cmn-candidate-header" data-tour-target="support-hub">
                            <h2>Support</h2>
                        </header>
                        <div class="cmn-support-user" data-support-root data-support-mode="user" data-support-role="candidate">
                            <div class="cmn-support-header-row">
                                <div>
                                    <h3>Need help?</h3>
                                    <p class="cmn-muted">Email candidate@covermenow.co.uk or open a support ticket.</p>
                                </div>
                                <button class="cmn-primary" type="button" data-support-open>Open Support Ticket</button>
                            </div>
                            <div class="cmn-support-dashboard" data-support-dashboard>
                                <button class="cmn-support-tile is-active" type="button" data-support-tile="all">
                                    <span>All tickets</span>
                                    <strong data-support-count="all">0</strong>
                                </button>
                                <button class="cmn-support-tile" type="button" data-support-tile="open">
                                    <span>Open tickets</span>
                                    <strong data-support-count="open">0</strong>
                                </button>
                                <button class="cmn-support-tile" type="button" data-support-tile="closed">
                                    <span>Closed tickets</span>
                                    <strong data-support-count="closed">0</strong>
                                </button>
                                <button class="cmn-support-tile" type="button" data-support-tile="feedback_insights">
                                    <span>Recent feedback</span>
                                    <strong data-support-count="feedback_avg">0.0/5</strong>
                                </button>
                            </div>
                            <div class="cmn-support-body">
                                <div class="cmn-support-sidebar">
                                    <div class="cmn-support-filters">
                                        <button class="cmn-ghost is-active" type="button" data-support-filter="all">All</button>
                                        <button class="cmn-ghost" type="button" data-support-filter="open">Open</button>
                                        <button class="cmn-ghost" type="button" data-support-filter="closed">Closed</button>
                                        <button class="cmn-ghost" type="button" data-support-filter="needs_feedback">Needs feedback</button>
                                    </div>
                                    <div class="cmn-support-list" data-support-list>
                                        <div class="cmn-muted">Loading tickets...</div>
                                    </div>
                                </div>
                                <div class="cmn-support-thread" data-support-thread>
                                    <div class="cmn-support-thread-header">
                                        <div>
                                            <strong data-support-thread-title>My Tickets</strong>
                                            <span class="cmn-muted" data-support-thread-ref>Select a ticket to view messages.</span>
                                            <span class="cmn-status-chip is-approved" data-support-feedback-badge hidden>Feedback submitted</span>
                                        </div>
                                        <div class="cmn-support-thread-actions">
                                            <button class="cmn-ghost" type="button" data-support-reopen-ticket disabled>Reopen Ticket</button>
                                            <button class="cmn-ghost" type="button" data-support-save-transcript disabled>Save Transcript</button>
                                            <button class="cmn-ghost" type="button" data-support-email-transcript disabled>Send Transcript to Email</button>
                                        </div>
                                    </div>
                                    <div class="cmn-support-messages" data-support-messages></div>
                                    <form class="cmn-support-reply" data-support-reply>
                                        <textarea name="message" rows="4" placeholder="Type your reply..." required></textarea>
                                        <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.txt">
                                        <button class="cmn-primary" type="submit">Send Reply</button>
                                    </form>
                                    <div class="cmn-support-feedback" data-support-feedback></div>
                                </div>
                            </div>
                        </div>
                        <div class="cmn-support-feedback-modal" data-support-feedback-modal hidden>
                            <div class="cmn-support-feedback-modal__overlay"></div>
                            <div class="cmn-support-feedback-modal__card" role="dialog" aria-modal="true" aria-labelledby="cmn-support-feedback-title">
                                <h3 id="cmn-support-feedback-title">Support Feedback</h3>
                                <p class="cmn-muted">Rate your support experience for this closed ticket.</p>
                                <form class="cmn-support-feedback-modal-form" data-support-feedback-modal-form>
                                    <div class="cmn-feedback-question">
                                        <label>Support quality</label>
                                        <div class="cmn-star-picker" data-feedback-stars="support_rating"></div>
                                        <input type="hidden" name="support_rating" value="">
                                        <span class="cmn-star-score" data-feedback-score="support_rating">0/5</span>
                                    </div>
                                    <div class="cmn-feedback-question">
                                        <label>Response time</label>
                                        <div class="cmn-star-picker" data-feedback-stars="response_time_rating"></div>
                                        <input type="hidden" name="response_time_rating" value="">
                                        <span class="cmn-star-score" data-feedback-score="response_time_rating">0/5</span>
                                    </div>
                                    <div class="cmn-feedback-question">
                                        <label>Overall satisfaction</label>
                                        <div class="cmn-star-picker" data-feedback-stars="overall_satisfaction"></div>
                                        <input type="hidden" name="overall_satisfaction" value="">
                                        <span class="cmn-star-score" data-feedback-score="overall_satisfaction">0/5</span>
                                    </div>
                                    <div class="cmn-feedback-question">
                                        <label>Did we resolve your issue?</label>
                                        <div class="cmn-feedback-toggle" data-feedback-toggle>
                                            <button type="button" class="cmn-ghost" data-feedback-resolved="1">Yes</button>
                                            <button type="button" class="cmn-ghost" data-feedback-resolved="0">No</button>
                                        </div>
                                        <input type="hidden" name="issue_resolved" value="">
                                    </div>
                                    <div class="cmn-feedback-question">
                                        <label>Additional comments (optional)</label>
                                        <textarea name="comments" rows="3"></textarea>
                                    </div>
                                    <div class="cmn-settings-actions">
                                        <button class="cmn-primary" type="submit">Submit feedback</button>
                                        <span class="cmn-muted" data-support-feedback-modal-msg></span>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="cmn-support-insights-modal" data-support-insights-modal hidden>
                            <div class="cmn-support-insights-modal__overlay" data-support-insights-close></div>
                            <div class="cmn-support-insights-modal__card" role="dialog" aria-modal="true" aria-label="Feedback insights">
                                <div class="cmn-support-insights-header">
                                    <h3>Feedback insights</h3>
                                    <button class="cmn-ghost cmn-btn-mini" type="button" data-support-insights-close>Close</button>
                                </div>
                                <div class="cmn-support-insights-filters">
                                    <button class="cmn-ghost is-active" type="button" data-support-insights-filter="recent">Recent</button>
                                    <button class="cmn-ghost" type="button" data-support-insights-filter="lowest">Lowest scores</button>
                                    <button class="cmn-ghost" type="button" data-support-insights-filter="unresolved">Resolved = No</button>
                                    <button class="cmn-ghost" type="button" data-support-insights-filter="overall_lte_3">Overall ≤ 3</button>
                                </div>
                                <div class="cmn-support-insights-list" data-support-insights-list>
                                    <div class="cmn-muted">No feedback yet.</div>
                                </div>
                            </div>
                        </div>
                        <div class="cmn-modal" data-support-modal>
                            <div class="cmn-modal-content">
                                <div class="cmn-modal-header">
                                    <h3>Open Support Ticket</h3>
                                    <button class="cmn-ghost cmn-btn-mini" type="button" data-support-modal-close>Close</button>
                                </div>
                                <form class="cmn-form" data-support-form>
                                    <label>Category
                                        <select name="category">
                                            <option value="">Select</option>
                                            <option value="Account">Account</option>
                                            <option value="Booking">Booking</option>
                                            <option value="Documents">Documents</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </label>
                                    <label>Subject
                                        <input type="text" name="subject" required>
                                    </label>
                                    <label>Message
                                        <textarea name="message" rows="4" required></textarea>
                                    </label>
                                    <label>Attachments (optional)
                                        <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.txt">
                                    </label>
                                    <button class="cmn-primary" type="submit">Submit Ticket</button>
                                    <div class="cmn-muted" data-support-form-msg></div>
                                </form>
                            </div>
                        </div>
                    <?php else : ?>
                        <?php
                        $tomorrow_label = date_i18n('l, F jS', strtotime($target_date));
                        $availability_subtext = $availability_allowed ? 'Confirm availability for ' . $tomorrow_label . '.' : 'You can confirm availability from 7pm until 8am.';
                        $availability_button_helper = '';
                        if (!$availability_allowed) {
                            $availability_button_helper = 'You can confirm availability from 7pm until 8am.';
                        } elseif ($calendar_blocked) {
                            $availability_button_helper = 'You’ve marked yourself unavailable for tomorrow in your calendar.';
                        }
                        $availability_state_class = 'is-neutral';
                        $availability_state_text = 'Not confirmed yet.';
                        if ($calendar_blocked) {
                            $availability_state_class = 'is-blocked';
                            $availability_state_text = 'Confirmed unavailable.';
                        } elseif ($already_marked) {
                            $availability_state_class = 'is-confirmed';
                            $availability_state_text = 'Confirmed available.';
                        }
                        $availability_button_disabled = (!$availability_allowed);
                        $availability_unlock_at = '';
                        if (!$availability_allowed) {
                            $unlock_at = clone $now;
                            $unlock_at->setTime(19, 0, 0);
                            $availability_unlock_at = $unlock_at->format(DateTime::ATOM);
                        }
                        ?>
                        <div class="cmn-availability-hero <?php echo esc_attr($availability_state_class); ?>" data-availability-card data-tour-target="availability-button">
                            <div class="cmn-availability-hero-content">
                                <h2>Are you available tomorrow morning?</h2>
                                <p class="cmn-availability-subtext"><?php echo esc_html($availability_subtext); ?></p>
                                <div class="cmn-availability-status <?php echo esc_attr($availability_state_class); ?>" data-availability-message>
                                    <?php echo esc_html($availability_state_text); ?>
                                </div>
                            </div>
                            <div class="cmn-availability-hero-action">
                                <button id="cmn-tomorrow-availability-btn" class="cmn-primary cmn-availability-btn" type="button" data-availability-button<?php echo $availability_button_disabled ? ' disabled' : ''; ?> data-availability-date="<?php echo esc_attr($target_date); ?>" data-available="<?php echo $already_marked ? '1' : '0'; ?>" data-calendar-blocked="<?php echo $calendar_blocked ? '1' : '0'; ?>"<?php echo $availability_unlock_at ? ' data-availability-unlock-at="' . esc_attr($availability_unlock_at) . '"' : ''; ?>>
                                    <?php echo esc_html($already_marked ? 'I’m NOT available tomorrow morning' : 'I’m available tomorrow morning'); ?>
                                </button>
                                <div class="cmn-availability-helper" data-availability-helper><?php echo esc_html($availability_button_helper); ?></div>
                            </div>
                        </div>
                        <div class="cmn-dashboard-row cmn-dashboard-row-equal">
                            <div class="cmn-dashboard-card" data-tour-target="upcoming-bookings">
                                <div class="cmn-card-header">
                                    <h3>Upcoming Booking</h3>
                                    <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['candidate' => 'bookings'], $portal_url)); ?>">View all</a>
                                </div>
                                <?php if ($upcoming_booking) : ?>
                                    <?php
                                    $booking_date = get_post_meta($upcoming_booking->ID, 'cmn_date', true) ?: get_post_meta($upcoming_booking->ID, 'cmn_start_date', true);
                                    $school_id = (int) get_post_meta($upcoming_booking->ID, 'cmn_school_id', true);
                                    $school_name = $school_id ? get_the_title($school_id) : 'School';
                                    ?>
                                    <strong><?php echo esc_html($booking_date ? date_i18n('l, F jS', strtotime($booking_date)) : ''); ?></strong>
                                    <span><?php echo esc_html($school_name); ?></span>
                                    <span class="cmn-pill cmn-pill--confirmed">Confirmed</span>
                                <?php else : ?>
                                    <div class="cmn-empty">No upcoming bookings yet.</div>
                                <?php endif; ?>
                            </div>
                            <div class="cmn-dashboard-card" data-tour-target="booking-history">
                                <div class="cmn-card-header">
                                    <h3>Booking History</h3>
                                    <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['candidate' => 'bookings', 'tab' => 'past'], $portal_url)); ?>">View all</a>
                                </div>
                                <?php if ($past_bookings) : ?>
                                    <div class="cmn-list">
                                        <?php foreach ($past_bookings as $booking) : ?>
                                            <?php
                                            $booking_date = get_post_meta($booking->ID, 'cmn_date', true) ?: get_post_meta($booking->ID, 'cmn_start_date', true);
                                            $school_id = (int) get_post_meta($booking->ID, 'cmn_school_id', true);
                                            $school_name = $school_id ? get_the_title($school_id) : 'School';
                                            ?>
                                            <div class="cmn-list-item">
                                                <strong><?php echo esc_html($school_name); ?></strong>
                                                <span><?php echo esc_html($booking_date ? date_i18n('M j, Y', strtotime($booking_date)) : ''); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else : ?>
                                    <div class="cmn-empty">No past bookings yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="cmn-dashboard-card cmn-calendar-planner" data-candidate-calendar data-tour-target="availability-planner"
                             data-calendar-month="<?php echo esc_attr($calendar_min_month); ?>"
                             data-calendar-min="<?php echo esc_attr($calendar_min_month); ?>"
                             data-calendar-max="<?php echo esc_attr($calendar_max_month); ?>"
                             data-calendar-data="<?php echo esc_attr(wp_json_encode($calendar_map)); ?>">
                            <div class="cmn-card-header cmn-calendar-header">
                                <h3>Availability Planner</h3>
                                <div class="cmn-calendar-controls">
                                    <button class="cmn-ghost" type="button" data-calendar-prev>&larr;</button>
                                    <span data-calendar-label></span>
                                    <button class="cmn-ghost" type="button" data-calendar-next>&rarr;</button>
                                </div>
                            </div>
                            <div class="cmn-calendar-grid cmn-calendar-interactive" data-calendar-grid></div>
                            <div class="cmn-calendar-range-controls">
                                <label>Start date
                                    <input type="date" data-calendar-range-start min="<?php echo esc_attr(current_time('Y-m-d')); ?>" max="<?php echo esc_attr((new DateTime(current_time('Y-m-d'), wp_timezone()))->modify('+30 days')->format('Y-m-d')); ?>">
                                </label>
                                <label>End date
                                    <input type="date" data-calendar-range-end min="<?php echo esc_attr(current_time('Y-m-d')); ?>" max="<?php echo esc_attr((new DateTime(current_time('Y-m-d'), wp_timezone()))->modify('+30 days')->format('Y-m-d')); ?>">
                                </label>
                                <button class="cmn-ghost" type="button" data-calendar-bulk="available">Mark as Available</button>
                                <button class="cmn-ghost" type="button" data-calendar-bulk="unavailable">Mark as Unavailable</button>
                                <button class="cmn-ghost" type="button" data-calendar-clear="range">Clear selected range</button>
                                <button class="cmn-ghost" type="button" data-calendar-clear="next30">Clear availability (next 30 days)</button>
                            </div>
                            <div class="cmn-calendar-legend">
                                <span><span class="cmn-dot is-available"></span> Available</span>
                                <span><span class="cmn-dot is-unavailable"></span> Unavailable</span>
                                <span><span class="cmn-dot is-weekend"></span> Weekend locked</span>
                                <span><span class="cmn-dot"></span> Neutral</span>
                            </div>
                            <div class="cmn-calendar-feedback" data-calendar-feedback></div>
                        </div>
                        <div class="cmn-dashboard-row cmn-dashboard-row-equal">
                            <div class="cmn-dashboard-card" data-tour-target="profile-documents">
                                <div class="cmn-card-header">
                                    <h3>Profile & Documents</h3>
                                    <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['candidate' => 'profile'], $portal_url)); ?>">View profile</a>
                                </div>
                                <p data-profile-completion-copy>Profile <?php echo esc_html($completion_percent); ?>% complete</p>
                                <div class="cmn-progress-bar"><span data-profile-completion-bar style="width: <?php echo esc_attr($completion_percent); ?>%;"></span></div>
                                <ul class="cmn-status-list">
                                    <li class="<?php echo $doc_cv['uploaded'] ? 'is-ok' : 'is-warn'; ?>">CV <?php echo $doc_cv['uploaded'] ? 'Uploaded' : 'Required'; ?></li>
                                    <li class="<?php echo $doc_dbs['uploaded'] ? 'is-ok' : 'is-warn'; ?>">DBS <?php echo $doc_dbs['uploaded'] ? 'Uploaded' : 'Required'; ?></li>
                                    <li class="<?php echo $doc_id['uploaded'] ? 'is-ok' : 'is-warn'; ?>">ID <?php echo $doc_id['uploaded'] ? 'Uploaded' : 'Required'; ?></li>
                                </ul>
                            </div>
                            <div class="cmn-dashboard-card" data-tour-target="availability-summary">
                                <div class="cmn-card-header">
                                    <h3>Availability Summary</h3>
                                    <a class="cmn-ghost" href="<?php echo esc_url(add_query_arg(['candidate' => 'calendar'], $portal_url)); ?>">Manage</a>
                                </div>
                                <p>Next available date</p>
                                <strong data-summary-next-date><?php echo esc_html($next_available_label); ?></strong>
                                <div class="cmn-availability-stats">
                                    <div><span class="cmn-dot is-available"></span> <span data-summary-available><?php echo esc_html($available_count); ?></span> Available days</div>
                                    <div><span class="cmn-dot is-unavailable"></span> <span data-summary-unavailable><?php echo esc_html($unavailable_count); ?></span> Unavailable days</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($is_preview) : ?>
                        <div class="cmn-preview-register">
                            <a class="cmn-primary" href="<?php echo esc_url($candidate_url); ?>">Apply to Register (Candidate)</a>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    public function render_available_wall_shortcode() {
        ob_start();
        ?>
        <section class="cmn-portal">
            <header class="cmn-portal-header">
                <h2>Available Tomorrow</h2>
                <p>These candidates switched on availability between 7pm and 8am.</p>
            </header>
            <div class="cmn-wall">
                <?php echo $this->render_available_candidates_wall(); ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    private function render_available_candidates_list($school_id = 0) {
        $items = $this->get_available_candidates($school_id);
        if (empty($items)) {
            return '<div class="cmn-list-item"><span>No candidates marked available yet.</span></div>';
        }
        $out = '';
        foreach ($items as $item) {
            $location = get_post_meta($item->ID, 'cmn_location', true);
            $out .= '<div class="cmn-list-item">';
            $out .= '<strong>' . esc_html($item->post_title) . '</strong>';
            $out .= '<span>' . esc_html($location) . '</span>';
            $out .= '<button class="cmn-ghost" type="button">Request</button>';
            $out .= '</div>';
        }
        return $out;
    }

    private function render_available_candidates_wall() {
        $items = $this->get_available_candidates();
        if (empty($items)) {
            return '<div class="cmn-wall-card"><p>No candidates marked available yet.</p></div>';
        }
        $out = '';
        foreach ($items as $item) {
            $location = get_post_meta($item->ID, 'cmn_location', true);
            $out .= '<div class="cmn-wall-card">';
            $out .= '<h4>' . esc_html($item->post_title) . '</h4>';
            $out .= '<p>' . esc_html($location) . '</p>';
            $out .= '<button class="cmn-primary" type="button">Request</button>';
            $out .= '</div>';
        }
        return $out;
    }

    private function get_available_candidate_rows($date, $school_id = 0, $limit = 12) {
        global $wpdb;
        $table = $this->get_candidate_availability_table();
        if (!$date || !$table) {
            return [];
        }
        $params = [$date, 'morning'];
        $where = "ca.available_date = %s AND ca.available_type = %s";
        if ($school_id) {
            $assigned = $this->get_assigned_candidates($school_id);
            if (!$assigned) {
                return [];
            }
            $assigned = array_map('intval', $assigned);
            $placeholders = implode(',', array_fill(0, count($assigned), '%d'));
            $where .= " AND ca.candidate_id IN ({$placeholders})";
            $params = array_merge($params, $assigned);
        }
        $limit_sql = $limit ? ' LIMIT ' . intval($limit) : '';
        $calendar_table = $this->get_candidate_calendar_table();
        if ($calendar_table) {
            $params[] = $date;
            $sql = "SELECT ca.candidate_id, ca.created_at FROM {$table} ca WHERE {$where} AND NOT EXISTS (SELECT 1 FROM {$calendar_table} cc WHERE cc.candidate_id = ca.candidate_id AND cc.date = %s AND cc.status = 'unavailable') ORDER BY ca.created_at DESC{$limit_sql}";
        } else {
            $sql = "SELECT ca.candidate_id, ca.created_at FROM {$table} ca WHERE {$where} ORDER BY ca.created_at DESC{$limit_sql}";
        }
        return $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
    }

    private function get_available_candidates($school_id = 0) {
        $tomorrow = $this->get_tomorrow_date();
        $rows = $this->get_available_candidate_rows($tomorrow, $school_id, 12);
        if (!$rows) {
            return [];
        }
        $ids = array_map('intval', array_column($rows, 'candidate_id'));
        if (!$ids) {
            return [];
        }
        $posts = get_posts([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => count($ids),
            'post__in' => $ids,
            'orderby' => 'post__in',
            'meta_query' => [
                [
                    'key' => 'cmn_status',
                    'value' => 'approved',
                ],
            ],
        ]);
        return $posts;
    }

    private function get_available_candidates_with_times($date, $school_id = 0, $limit = 6) {
        $rows = $this->get_available_candidate_rows($date, $school_id, $limit);
        if (!$rows) {
            return [];
        }
        $ids = array_map('intval', array_column($rows, 'candidate_id'));
        if (!$ids) {
            return [];
        }
        $posts = get_posts([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => count($ids),
            'post__in' => $ids,
            'orderby' => 'post__in',
        ]);
        $indexed = [];
        foreach ($posts as $post) {
            $indexed[$post->ID] = $post;
        }
        $output = [];
        foreach ($rows as $row) {
            $candidate_id = (int) $row['candidate_id'];
            if (!isset($indexed[$candidate_id])) {
                continue;
            }
            $output[] = [
                'post' => $indexed[$candidate_id],
                'created_at' => $row['created_at'],
            ];
        }
        return $output;
    }

    public function register_meta_boxes() {
        add_meta_box(
            'cmn_school_details',
            'School Details',
            [$this, 'render_school_meta_box'],
            'cmn_school',
            'normal',
            'high'
        );

        add_meta_box(
            'cmn_candidate_details',
            'Candidate Details',
            [$this, 'render_candidate_meta_box'],
            'cmn_candidate',
            'normal',
            'high'
        );
    }

    public function render_school_meta_box($post) {
        wp_nonce_field('cmn_school_meta', 'cmn_school_meta_nonce');
        $fields = [
            'cmn_school_id' => 'School ID',
            'cmn_location' => 'Location',
            'cmn_address_line1' => 'Address Line 1',
            'cmn_address_line2' => 'Address Line 2',
            'cmn_town' => 'Town / City',
            'cmn_county' => 'County',
            'cmn_postcode' => 'Post Code',
            'cmn_phone' => 'Phone',
            'cmn_switchboard' => 'Switchboard',
            'cmn_website' => 'Website',
            'cmn_status' => 'Status',
            'cmn_school_type' => 'School Type',
            'cmn_pupil_count' => 'Pupil Count',
            'cmn_supply_frequency' => 'Supply Frequency',
            'cmn_use_agencies' => 'Uses Supply Agencies',
            'cmn_agency_count' => 'Supply Agency Count',
            'cmn_account_manager' => 'Account Manager',
            'cmn_account_manager_name' => 'Account Manager Name',
            'cmn_account_manager_email' => 'Account Manager Email',
            'cmn_cover_manager' => 'Cover Manager',
            'cmn_email' => 'Email',
            'cmn_contact1' => 'Contact 1 Name',
            'cmn_contact_role' => 'Contact 1 Role',
            'cmn_contact1_email' => 'Contact 1 Email',
            'cmn_contact2' => 'Contact 2 Name',
            'cmn_contact2_email' => 'Contact 2 Email',
            'cmn_contact3' => 'Contact 3 Name',
            'cmn_contact3_email' => 'Contact 3 Email',
            'cmn_spoke_to_cm' => 'Spoke To CM (0/1)',
            'cmn_email_name' => 'Email Name',
            'cmn_assigned_candidates' => 'Assigned Candidate IDs (comma-separated)',
        ];
        echo '<table class="form-table">';
        foreach ($fields as $key => $label) {
            $value = get_post_meta($post->ID, $key, true);
            echo '<tr>';
            echo '<th><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
            echo '<td><input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" /></td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    public function render_candidate_meta_box($post) {
        wp_nonce_field('cmn_candidate_meta', 'cmn_candidate_meta_nonce');
        $fields = [
            'cmn_location' => 'Location',
            'cmn_phone' => 'Phone',
            'cmn_email' => 'Email',
            'cmn_status' => 'Status',
            'cmn_available_tomorrow' => 'Available Tomorrow (0/1)',
            'cmn_availability_notes' => 'Availability Notes',
            'cmn_cv_file' => 'CV File URL',
            'cmn_dbs_file' => 'DBS File URL',
            'cmn_dbs_update_service' => 'DBS Update Service (yes/no)',
            'cmn_id_file' => 'ID Document URL',
            'cmn_default_rate' => 'Default Rate (£)',
        ];
        echo '<table class="form-table">';
        foreach ($fields as $key => $label) {
            $value = get_post_meta($post->ID, $key, true);
            echo '<tr>';
            echo '<th><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
            echo '<td><input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" /></td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    public function save_school_meta($post_id) {
        if (!isset($_POST['cmn_school_meta_nonce']) || !wp_verify_nonce($_POST['cmn_school_meta_nonce'], 'cmn_school_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        $previous_status = get_post_meta($post_id, 'cmn_status', true);
        $fields = [
            'cmn_school_id',
            'cmn_location',
            'cmn_address_line1',
            'cmn_address_line2',
            'cmn_town',
            'cmn_county',
            'cmn_postcode',
            'cmn_phone',
            'cmn_switchboard',
            'cmn_website',
            'cmn_status',
            'cmn_school_type',
            'cmn_pupil_count',
            'cmn_supply_frequency',
            'cmn_use_agencies',
            'cmn_agency_count',
            'cmn_account_manager',
            'cmn_account_manager_name',
            'cmn_account_manager_email',
            'cmn_cover_manager',
            'cmn_email',
            'cmn_email_name',
            'cmn_primary_contact_name',
            'cmn_primary_contact_email',
            'cmn_primary_contact_phone',
            'cmn_primary_contact_role',
            'cmn_school_email_domain',
            'cmn_contact1',
            'cmn_contact_role',
            'cmn_contact1_email',
            'cmn_contact2',
            'cmn_contact2_email',
            'cmn_contact3',
            'cmn_contact3_email',
            'cmn_spoke_to_cm',
            'cmn_assigned_candidates',
            'cmn_pipeline_stage',
        ];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                if ($field === 'cmn_assigned_candidates') {
                    $raw = sanitize_text_field($_POST[$field]);
                    $ids = array_filter(array_map('intval', array_map('trim', explode(',', $raw))));
                    update_post_meta($post_id, $field, $ids);
                } else {
                    if ($field === 'cmn_school_id') {
                        $new_id = strtoupper(trim(sanitize_text_field($_POST[$field])));
                        if ($new_id !== '') {
                            $existing_id = $this->get_school_post_id_by_school_id($new_id);
                            if ($existing_id && (int) $existing_id !== (int) $post_id) {
                                continue;
                            }
                        }
                        update_post_meta($post_id, $field, $new_id);
                    } elseif ($field === 'cmn_email') {
                        $email = sanitize_email($_POST[$field]);
                        update_post_meta($post_id, $field, $email);
                        $domain = $this->get_email_domain($email);
                        if ($domain !== '') {
                            $existing_id = $this->get_school_post_id_by_domain($domain);
                            if (!$existing_id || (int) $existing_id === (int) $post_id) {
                                update_post_meta($post_id, 'cmn_school_email_domain', $domain);
                            }
                        }
                    } else {
                        update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
                    }
                }
            }
        }
        if (isset($_POST['cmn_cover_manager'])) {
            $this->store_cover_manager_split($post_id, sanitize_text_field($_POST['cmn_cover_manager']));
        }
        if (isset($_POST['cmn_status'])) {
            $new_status = sanitize_text_field($_POST['cmn_status']);
            if ($new_status !== $previous_status) {
                $this->send_school_status_email($post_id, $new_status);
            }
        }
        $this->upsert_school_index($post_id);
    }

    private function send_school_status_email($post_id, $new_status) {
        $email = sanitize_email(get_post_meta($post_id, 'cmn_email', true));
        if ($email === '') {
            return;
        }
        $school_name = get_the_title($post_id);
        $status = strtolower(trim(preg_replace('/\\s+/', ' ', $new_status)));
        $contact_line = "If you have any questions, email school@covermenow.co.uk or call 07438 766 532.";
        $login_page = get_page_by_title('Login');
        $login_url = $login_page ? get_permalink($login_page) : home_url('/login');

        $subject = '';
        $message = '';
        if (in_array($status, ['accepted', 'approved'], true)) {
            $subject = 'Your CoverMeNow ONE access is live';
            $message = "Hi {$school_name},\n\nYour application has been accepted and your access is now live.\nLog in here: {$login_url}\n\n{$contact_line}";
        } elseif (in_array($status, ['further info needed', 'further information needed', 'needs more info', 'more info needed'], true)) {
            $subject = 'More information needed for your application';
            $message = "Hi {$school_name},\n\nWe need a bit more information to complete your application. Please reply to this email.\n\n{$contact_line}";
        } elseif (in_array($status, ['declined', 'rejected'], true)) {
            $subject = 'Update on your application';
            $message = "Hi {$school_name},\n\nThank you for your interest. Unfortunately your application has been declined.\n\n{$contact_line}";
        } else {
            return;
        }

        $previous_log_type = $GLOBALS['cmn_school_mail_log_type'] ?? null;
        $GLOBALS['cmn_school_mail_log_type'] = 'school_status_update';
        $this->send_school_email($email, $subject, $message);
        if ($previous_log_type !== null) {
            $GLOBALS['cmn_school_mail_log_type'] = $previous_log_type;
        } else {
            unset($GLOBALS['cmn_school_mail_log_type']);
        }
    }

    public function save_candidate_meta($post_id) {
        if (!isset($_POST['cmn_candidate_meta_nonce']) || !wp_verify_nonce($_POST['cmn_candidate_meta_nonce'], 'cmn_candidate_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        $fields = [
            'cmn_location',
            'cmn_phone',
            'cmn_email',
            'cmn_status',
            'cmn_available_tomorrow',
            'cmn_availability_notes',
            'cmn_cv_file',
            'cmn_dbs_file',
            'cmn_dbs_update_service',
            'cmn_id_file',
            'cmn_default_rate',
        ];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }

    public function register_admin_menu() {
        add_menu_page(
            'CoverMeNow ONE',
            'CoverMeNow ONE',
            'manage_options',
            'cmn-dashboard',
            [$this, 'render_dashboard_page'],
            'dashicons-shield',
            3
        );

        add_submenu_page(
            'cmn-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'cmn-dashboard',
            [$this, 'render_dashboard_page']
        );

        add_submenu_page(
            'cmn-dashboard',
            'Schools CRM',
            'Schools CRM',
            'manage_options',
            'cmn-schools-crm',
            [$this, 'render_schools_crm_page']
        );

        add_submenu_page(
            'cmn-dashboard',
            'Candidates CRM',
            'Candidates CRM',
            'manage_options',
            'cmn-candidates-crm',
            [$this, 'render_candidates_crm_page']
        );

        add_submenu_page(
            null,
            'School Profile',
            'School Profile',
            'manage_options',
            'cmn-school-profile',
            [$this, 'render_school_profile_page']
        );

        add_submenu_page(
            null,
            'Candidate Profile',
            'Candidate Profile',
            'manage_options',
            'cmn-candidate-profile',
            [$this, 'render_candidate_profile_page']
        );

        add_submenu_page(
            'cmn-dashboard',
            'Bookings',
            'Bookings',
            'manage_options',
            'cmn-bookings',
            [$this, 'render_bookings_page']
        );

        add_submenu_page(
            'cmn-dashboard',
            'Marketing Hub',
            'Marketing Hub',
            'manage_options',
            'cmn-marketing',
            [$this, 'render_marketing_page']
        );

        add_submenu_page(
            'cmn-dashboard',
            'Finance Hub',
            'Finance Hub',
            'manage_options',
            'cmn-finance',
            [$this, 'render_finance_page']
        );

        add_submenu_page(
            'cmn-dashboard',
            'Import',
            'Import',
            'manage_options',
            'cmn-import',
            [$this, 'render_import_page']
        );
    }

    public function render_dashboard_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>CoverMeNow ONE Dashboard</h1>';
        echo '<div class="cmn-grid">';
        echo '<div class="cmn-card"><h2>Pending Approvals</h2><p>Schools and candidates awaiting review.</p></div>';
        echo '<div class="cmn-card"><h2>Bookings Today</h2><p>Urgent booking requests to action.</p></div>';
        echo '<div class="cmn-card"><h2>Available Tomorrow</h2><p>Candidates toggled on between 7pm and 8am.</p></div>';
        echo '<div class="cmn-card"><h2>Marketing Performance</h2><p>Campaign reach and conversions.</p></div>';
        echo '<div class="cmn-card"><h2>Finance Snapshot</h2><p>Monthly revenue and outstanding invoices.</p></div>';
        echo '</div>';
        echo '<p>We will wire these panels to live data next.</p>';
        echo '</div>';
    }

    public function render_schools_crm_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $status = isset($_GET['cmn_status']) ? sanitize_text_field($_GET['cmn_status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $args = [
            'post_type' => 'cmn_school',
            'posts_per_page' => 50,
            's' => $search,
        ];

        if ($status !== '') {
            $args['meta_query'] = [
                [
                    'key' => 'cmn_status',
                    'value' => $status,
                ],
            ];
        }

        $query = new WP_Query($args);

        echo '<div class="wrap">';
        echo '<h1>Schools CRM</h1>';
        echo '<form method="get" class="cmn-filters">';
        echo '<input type="hidden" name="page" value="cmn-schools-crm" />';
        echo '<input type="search" name="s" placeholder="Search schools..." value="' . esc_attr($search) . '" />';
        echo '<select name="cmn_status">';
        echo '<option value="">All Statuses</option>';
        foreach (['lead', 'pending', 'approved', 'rejected'] as $opt) {
            $selected = $status === $opt ? ' selected' : '';
            echo '<option value="' . esc_attr($opt) . '"' . $selected . '>' . ucfirst($opt) . '</option>';
        }
        echo '</select>';
        echo '<button class="button">Filter</button>';
        echo '</form>';

        echo '<table class="widefat striped cmn-table">';
        echo '<thead><tr>';
        echo '<th>School</th>';
        echo '<th>Location</th>';
        echo '<th>Phone</th>';
        echo '<th>Email</th>';
        echo '<th>Contact 1</th>';
        echo '<th>Status</th>';
        echo '<th>Actions</th>';
        echo '</tr></thead><tbody>';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $edit_link = get_edit_post_link($post_id);
                $profile_link = admin_url('admin.php?page=cmn-school-profile&school_id=' . $post_id);
                echo '<tr>';
                echo '<td><a href="' . esc_url($edit_link) . '">' . esc_html(get_the_title()) . '</a></td>';
                echo '<td>' . esc_html(get_post_meta($post_id, 'cmn_location', true)) . '</td>';
                echo '<td>' . esc_html(get_post_meta($post_id, 'cmn_phone', true)) . '</td>';
                echo '<td>' . esc_html(get_post_meta($post_id, 'cmn_email', true)) . '</td>';
                echo '<td>' . esc_html(get_post_meta($post_id, 'cmn_contact1', true)) . '</td>';
                echo '<td>' . esc_html(get_post_meta($post_id, 'cmn_status', true)) . '</td>';
                echo '<td><a class="button button-secondary" href="' . esc_url($profile_link) . '">View Profile</a></td>';
                echo '</tr>';
            }
            wp_reset_postdata();
        } else {
            echo '<tr><td colspan="7">No schools found.</td></tr>';
        }

        echo '</tbody></table>';
        echo '<p>Tip: Use Tools → CMN Import to load your spreadsheet CSV.</p>';
        echo '</div>';
    }

    public function render_candidates_crm_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $status = isset($_GET['cmn_status']) ? sanitize_text_field($_GET['cmn_status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $args = [
            'post_type' => 'cmn_candidate',
            'posts_per_page' => 50,
            's' => $search,
        ];

        if ($status !== '') {
            $args['meta_query'] = [
                [
                    'key' => 'cmn_status',
                    'value' => $status,
                ],
            ];
        }

        $query = new WP_Query($args);

        echo '<div class="wrap">';
        echo '<h1>Candidates CRM</h1>';
        echo '<form method="get" class="cmn-filters">';
        echo '<input type="hidden" name="page" value="cmn-candidates-crm" />';
        echo '<input type="search" name="s" placeholder="Search candidates..." value="' . esc_attr($search) . '" />';
        echo '<select name="cmn_status">';
        echo '<option value="">All Statuses</option>';
        foreach (['pending', 'approved', 'rejected'] as $opt) {
            $selected = $status === $opt ? ' selected' : '';
            echo '<option value="' . esc_attr($opt) . '"' . $selected . '>' . ucfirst($opt) . '</option>';
        }
        echo '</select>';
        echo '<button class="button">Filter</button>';
        echo '</form>';

        echo '<table class="widefat striped cmn-table">';
        echo '<thead><tr>';
        echo '<th>Candidate</th>';
        echo '<th>Location</th>';
        echo '<th>Phone</th>';
        echo '<th>Email</th>';
        echo '<th>Status</th>';
        echo '<th>Actions</th>';
        echo '</tr></thead><tbody>';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $edit_link = get_edit_post_link($post_id);
                $profile_link = admin_url('admin.php?page=cmn-candidate-profile&candidate_id=' . $post_id);
                echo '<tr>';
                echo '<td><a href="' . esc_url($edit_link) . '">' . esc_html(get_the_title()) . '</a></td>';
                echo '<td>' . esc_html(get_post_meta($post_id, 'cmn_location', true)) . '</td>';
                echo '<td>' . esc_html(get_post_meta($post_id, 'cmn_phone', true)) . '</td>';
                echo '<td>' . esc_html(get_post_meta($post_id, 'cmn_email', true)) . '</td>';
                echo '<td>' . esc_html(get_post_meta($post_id, 'cmn_status', true)) . '</td>';
                echo '<td><a class="button button-secondary" href="' . esc_url($profile_link) . '">View Profile</a></td>';
                echo '</tr>';
            }
            wp_reset_postdata();
        } else {
            echo '<tr><td colspan="6">No candidates found.</td></tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
    }

    public function render_school_profile_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;
        $school = $school_id ? get_post($school_id) : null;
        if (!$school || $school->post_type !== 'cmn_school') {
            echo '<div class="wrap"><h1>School Profile</h1><p>School not found.</p></div>';
            return;
        }

        $meta = function ($key) use ($school_id) {
            return get_post_meta($school_id, $key, true);
        };

        $activities = get_posts([
            'post_type' => 'cmn_activity',
            'posts_per_page' => 50,
            'meta_query' => [
                [
                    'key' => 'cmn_related_type',
                    'value' => 'school',
                ],
                [
                    'key' => 'cmn_related_id',
                    'value' => $school_id,
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        echo '<div class="wrap cmn-profile">';
        echo '<h1>School Profile: ' . esc_html($school->post_title) . '</h1>';

        echo '<div class="cmn-profile-grid">';
        echo '<div class="cmn-card">';
        echo '<h2>School Details</h2>';
        echo '<div class="cmn-meta-grid">';
        echo '<div><strong>School ID:</strong> ' . esc_html($meta('cmn_school_id')) . '</div>';
        echo '<div><strong>Status:</strong> ' . esc_html($meta('cmn_status')) . '</div>';
        echo '<div><strong>Location:</strong> ' . esc_html($meta('cmn_location')) . '</div>';
        echo '<div><strong>Phone:</strong> ' . esc_html($meta('cmn_phone')) . '</div>';
        echo '<div><strong>Switchboard:</strong> ' . esc_html($meta('cmn_switchboard')) . '</div>';
        echo '<div><strong>Website:</strong> ' . esc_html($meta('cmn_website')) . '</div>';
        echo '<div><strong>Account Manager:</strong> ' . esc_html($meta('cmn_account_manager')) . '</div>';
        echo '<div><strong>Cover Manager:</strong> ' . esc_html($meta('cmn_cover_manager')) . '</div>';
        echo '<div><strong>Email:</strong> ' . esc_html($meta('cmn_email')) . '</div>';
        echo '<div><strong>Spoke to CM:</strong> ' . esc_html($meta('cmn_spoke_to_cm')) . '</div>';
        echo '</div>';
        echo '<div class="cmn-actions">';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('cmn_update_status', 'cmn_update_status_nonce');
        echo '<input type="hidden" name="action" value="cmn_update_status">';
        echo '<input type="hidden" name="cmn_entity_type" value="school">';
        echo '<input type="hidden" name="cmn_entity_id" value="' . esc_attr($school_id) . '">';
        echo '<select name="cmn_status">';
        foreach (['lead', 'pending', 'approved', 'rejected'] as $opt) {
            $selected = $meta('cmn_status') === $opt ? ' selected' : '';
            echo '<option value="' . esc_attr($opt) . '"' . $selected . '>' . ucfirst($opt) . '</option>';
        }
        echo '</select>';
        echo '<button class="button">Update Status</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';

        echo '<div class="cmn-card">';
        echo '<h2>Contacts</h2>';
        echo '<div class="cmn-meta-grid">';
        echo '<div><strong>Contact 1:</strong> ' . esc_html($meta('cmn_contact1')) . ' (' . esc_html($meta('cmn_contact1_email')) . ')</div>';
        echo '<div><strong>Contact 2:</strong> ' . esc_html($meta('cmn_contact2')) . ' (' . esc_html($meta('cmn_contact2_email')) . ')</div>';
        echo '<div><strong>Contact 3:</strong> ' . esc_html($meta('cmn_contact3')) . ' (' . esc_html($meta('cmn_contact3_email')) . ')</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="cmn-card">';
        echo '<h2>Add Note / To-Do / Call Log</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="cmn_add_activity" />';
        echo '<input type="hidden" name="cmn_school_id" value="' . esc_attr($school_id) . '" />';
        wp_nonce_field('cmn_add_activity', 'cmn_add_activity_nonce');
        echo '<p><label>Type</label><br>';
        echo '<select name="cmn_activity_type">';
        echo '<option value="note">Note</option>';
        echo '<option value="todo">To-Do</option>';
        echo '<option value="call">Call Log</option>';
        echo '</select></p>';
        echo '<p><label>Title</label><br><input type="text" name="cmn_activity_title" class="regular-text" required></p>';
        echo '<p><label>Details</label><br><textarea name="cmn_activity_content" rows="4" class="large-text"></textarea></p>';
        echo '<p><label>Date</label><br><input type="date" name="cmn_activity_date" /></p>';
        echo '<p><label>Duration (mins, for calls)</label><br><input type="number" name="cmn_activity_duration" /></p>';
        echo '<p><label>Status (for todos)</label><br><select name="cmn_activity_status"><option value="open">Open</option><option value="done">Done</option></select></p>';
        echo '<p><button class="button button-primary">Add Activity</button></p>';
        echo '</form>';
        echo '</div>';

        echo '<div class="cmn-card">';
        echo '<h2>Activity Timeline</h2>';
        if ($activities) {
            echo '<ul class="cmn-activity-list">';
            foreach ($activities as $activity) {
                $type = get_post_meta($activity->ID, 'cmn_activity_type', true);
                $date = get_post_meta($activity->ID, 'cmn_activity_date', true);
                $content = get_post_meta($activity->ID, 'cmn_activity_content', true);
                $status = get_post_meta($activity->ID, 'cmn_activity_status', true);
                $duration = get_post_meta($activity->ID, 'cmn_activity_duration', true);
                echo '<li>';
                echo '<strong>' . esc_html(ucfirst($type)) . ':</strong> ' . esc_html($activity->post_title);
                if ($date) {
                    echo ' <span class="cmn-muted">(' . esc_html($date) . ')</span>';
                }
                if ($status) {
                    echo ' <span class="cmn-pill">' . esc_html($status) . '</span>';
                }
                if ($duration) {
                    echo ' <span class="cmn-muted">Duration: ' . esc_html($duration) . 'm</span>';
                }
                if ($content) {
                    echo '<div class="cmn-activity-content">' . esc_html($content) . '</div>';
                }
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No activity yet.</p>';
        }
        echo '</div>';

        echo '<p><a class="button" href="' . esc_url(admin_url('admin.php?page=cmn-schools-crm')) . '">Back to Schools CRM</a></p>';
        echo '</div>';
    }

    public function render_candidate_profile_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $candidate_id = isset($_GET['candidate_id']) ? intval($_GET['candidate_id']) : 0;
        $candidate = $candidate_id ? get_post($candidate_id) : null;
        if (!$candidate || $candidate->post_type !== 'cmn_candidate') {
            echo '<div class="wrap"><h1>Candidate Profile</h1><p>Candidate not found.</p></div>';
            return;
        }

        $meta = function ($key) use ($candidate_id) {
            return get_post_meta($candidate_id, $key, true);
        };

        $activities = get_posts([
            'post_type' => 'cmn_activity',
            'posts_per_page' => 50,
            'meta_query' => [
                [
                    'key' => 'cmn_related_type',
                    'value' => 'candidate',
                ],
                [
                    'key' => 'cmn_related_id',
                    'value' => $candidate_id,
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        echo '<div class="wrap cmn-profile">';
        echo '<h1>Candidate Profile: ' . esc_html($candidate->post_title) . '</h1>';

        echo '<div class="cmn-profile-grid">';
        echo '<div class="cmn-card">';
        echo '<h2>Candidate Details</h2>';
        echo '<div class="cmn-meta-grid">';
        echo '<div><strong>Status:</strong> ' . esc_html($meta('cmn_status')) . '</div>';
        echo '<div><strong>Location:</strong> ' . esc_html($meta('cmn_location')) . '</div>';
        echo '<div><strong>Phone:</strong> ' . esc_html($meta('cmn_phone')) . '</div>';
        echo '<div><strong>Email:</strong> ' . esc_html($meta('cmn_email')) . '</div>';
        echo '<div><strong>Available Tomorrow:</strong> ' . esc_html($meta('cmn_available_tomorrow')) . '</div>';
        echo '</div>';
        echo '<div class="cmn-actions">';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('cmn_update_status', 'cmn_update_status_nonce');
        echo '<input type="hidden" name="action" value="cmn_update_status">';
        echo '<input type="hidden" name="cmn_entity_type" value="candidate">';
        echo '<input type="hidden" name="cmn_entity_id" value="' . esc_attr($candidate_id) . '">';
        echo '<select name="cmn_status">';
        foreach (['pending', 'approved', 'rejected'] as $opt) {
            $selected = $meta('cmn_status') === $opt ? ' selected' : '';
            echo '<option value="' . esc_attr($opt) . '"' . $selected . '>' . ucfirst($opt) . '</option>';
        }
        echo '</select>';
        echo '<button class="button">Update Status</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';

        echo '<div class="cmn-card">';
        echo '<h2>Documents</h2>';
        $cv = $meta('cmn_cv_file');
        $dbs = $meta('cmn_dbs_file');
        echo '<div class="cmn-meta-grid">';
        echo '<div><strong>CV:</strong> ' . ($cv ? '<a href="' . esc_url($cv) . '" target="_blank">View</a>' : 'Not uploaded') . '</div>';
        echo '<div><strong>DBS:</strong> ' . ($dbs ? '<a href="' . esc_url($dbs) . '" target="_blank">View</a>' : 'Not uploaded') . '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="cmn-card">';
        echo '<h2>Availability Calendar</h2>';
        $days = array_filter(explode(',', (string) $meta('cmn_calendar_days')));
        if ($days) {
            echo '<p>Available on: ' . esc_html(implode(', ', $days)) . '</p>';
        } else {
            echo '<p>No calendar availability saved.</p>';
        }
        echo '</div>';
        echo '</div>';

        echo '<div class="cmn-card">';
        echo '<h2>Add Note / To-Do / Call Log</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="cmn_add_activity" />';
        echo '<input type="hidden" name="cmn_candidate_id" value="' . esc_attr($candidate_id) . '" />';
        wp_nonce_field('cmn_add_activity', 'cmn_add_activity_nonce');
        echo '<p><label>Type</label><br>';
        echo '<select name="cmn_activity_type">';
        echo '<option value="note">Note</option>';
        echo '<option value="todo">To-Do</option>';
        echo '<option value="call">Call Log</option>';
        echo '</select></p>';
        echo '<p><label>Title</label><br><input type="text" name="cmn_activity_title" class="regular-text" required></p>';
        echo '<p><label>Details</label><br><textarea name="cmn_activity_content" rows="4" class="large-text"></textarea></p>';
        echo '<p><label>Date</label><br><input type="date" name="cmn_activity_date" /></p>';
        echo '<p><label>Duration (mins, for calls)</label><br><input type="number" name="cmn_activity_duration" /></p>';
        echo '<p><label>Status (for todos)</label><br><select name="cmn_activity_status"><option value="open">Open</option><option value="done">Done</option></select></p>';
        echo '<p><button class="button button-primary">Add Activity</button></p>';
        echo '</form>';
        echo '</div>';

        echo '<div class="cmn-card">';
        echo '<h2>Activity Timeline</h2>';
        if ($activities) {
            echo '<ul class="cmn-activity-list">';
            foreach ($activities as $activity) {
                $type = get_post_meta($activity->ID, 'cmn_activity_type', true);
                $date = get_post_meta($activity->ID, 'cmn_activity_date', true);
                $content = get_post_meta($activity->ID, 'cmn_activity_content', true);
                $status = get_post_meta($activity->ID, 'cmn_activity_status', true);
                $duration = get_post_meta($activity->ID, 'cmn_activity_duration', true);
                echo '<li>';
                echo '<strong>' . esc_html(ucfirst($type)) . ':</strong> ' . esc_html($activity->post_title);
                if ($date) {
                    echo ' <span class="cmn-muted">(' . esc_html($date) . ')</span>';
                }
                if ($status) {
                    echo ' <span class="cmn-pill">' . esc_html($status) . '</span>';
                }
                if ($duration) {
                    echo ' <span class="cmn-muted">Duration: ' . esc_html($duration) . 'm</span>';
                }
                if ($content) {
                    echo '<div class="cmn-activity-content">' . esc_html($content) . '</div>';
                }
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No activity yet.</p>';
        }
        echo '</div>';

        echo '<p><a class="button" href="' . esc_url(admin_url('admin.php?page=cmn-candidates-crm')) . '">Back to Candidates CRM</a></p>';
        echo '</div>';
    }

    public function render_bookings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $status = isset($_GET['cmn_status']) ? sanitize_text_field($_GET['cmn_status']) : '';
        $args = [
            'post_type' => 'cmn_booking',
            'posts_per_page' => 50,
        ];
        if ($status !== '') {
            $args['meta_query'] = [
                [
                    'key' => 'cmn_status',
                    'value' => $status,
                ],
            ];
        }
        $query = new WP_Query($args);

        echo '<div class="wrap">';
        echo '<h1>Bookings</h1>';
        echo '<form method="get" class="cmn-filters">';
        echo '<input type="hidden" name="page" value="cmn-bookings" />';
        echo '<select name="cmn_status">';
        echo '<option value="">All Statuses</option>';
        foreach (['pending', 'approved', 'declined'] as $opt) {
            $selected = $status === $opt ? ' selected' : '';
            echo '<option value="' . esc_attr($opt) . '"' . $selected . '>' . ucfirst($opt) . '</option>';
        }
        echo '</select>';
        echo '<button class="button">Filter</button>';
        echo '</form>';

        echo '<table class="widefat striped cmn-table">';
        echo '<thead><tr>';
        echo '<th>Booking</th>';
        echo '<th>Date</th>';
        echo '<th>Role</th>';
        echo '<th>Status</th>';
        echo '<th>School</th>';
        echo '<th>Candidate</th>';
        echo '<th>Actions</th>';
        echo '</tr></thead><tbody>';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $date = get_post_meta($post_id, 'cmn_date', true);
                $role = get_post_meta($post_id, 'cmn_role', true);
                $status_val = get_post_meta($post_id, 'cmn_status', true);
                $school_id = get_post_meta($post_id, 'cmn_school_id', true);
                $candidate_id = get_post_meta($post_id, 'cmn_candidate_id', true);
                $school_name = $school_id ? get_the_title($school_id) : '';
                $candidate_name = $candidate_id ? get_the_title($candidate_id) : '';
                echo '<tr>';
                echo '<td>' . esc_html(get_the_title()) . '</td>';
                echo '<td>' . esc_html($date) . '</td>';
                echo '<td>' . esc_html($role) . '</td>';
                echo '<td>' . esc_html($status_val) . '</td>';
                echo '<td>' . esc_html($school_name) . '</td>';
                echo '<td>' . esc_html($candidate_name) . '</td>';
                echo '<td>';
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="cmn-inline">';
                wp_nonce_field('cmn_update_status', 'cmn_update_status_nonce');
                echo '<input type="hidden" name="action" value="cmn_update_status">';
                echo '<input type="hidden" name="cmn_entity_type" value="booking">';
                echo '<input type="hidden" name="cmn_entity_id" value="' . esc_attr($post_id) . '">';
                echo '<input type="hidden" name="cmn_status" value="approved">';
                echo '<button class="button button-primary">Approve</button>';
                echo '</form> ';
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="cmn-inline">';
                wp_nonce_field('cmn_update_status', 'cmn_update_status_nonce');
                echo '<input type="hidden" name="action" value="cmn_update_status">';
                echo '<input type="hidden" name="cmn_entity_type" value="booking">';
                echo '<input type="hidden" name="cmn_entity_id" value="' . esc_attr($post_id) . '">';
                echo '<input type="hidden" name="cmn_status" value="declined">';
                echo '<button class="button">Decline</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
            wp_reset_postdata();
        } else {
            echo '<tr><td colspan="7">No bookings found.</td></tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
    }

    public function handle_add_activity() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_add_activity_nonce']) || !wp_verify_nonce($_POST['cmn_add_activity_nonce'], 'cmn_add_activity')) {
            wp_die('Invalid nonce');
        }
        $school_domain = sanitize_text_field($_POST['cmn_school_domain'] ?? '');
        $school_id = intval($_POST['cmn_school_id'] ?? 0);
        $candidate_id = intval($_POST['cmn_candidate_id'] ?? 0);
        $title = sanitize_text_field($_POST['cmn_activity_title'] ?? '');
        $type = sanitize_text_field($_POST['cmn_activity_type'] ?? 'note');
        $content = sanitize_textarea_field($_POST['cmn_activity_content'] ?? '');
        $date = sanitize_text_field($_POST['cmn_activity_date'] ?? '');
        $duration = sanitize_text_field($_POST['cmn_activity_duration'] ?? '');

        if ($school_domain === '' && $school_id) {
            $school_domain = get_post_meta($school_id, 'cmn_school_email_domain', true);
        }
        if ($school_domain === '' && $school_id) {
            $school_email = get_post_meta($school_id, 'cmn_email', true);
            $school_domain = $this->get_email_domain($school_email);
        }
        if ($school_domain === '' && $school_id === 0 && $candidate_id) {
            $school_domain = '';
        }

        if ($school_domain) {
            if (!$this->user_can_access_school($school_domain)) {
                wp_die('Unauthorized');
            }
            $this->insert_activity_row([
                'entity_type' => 'school',
                'entity_ref' => $school_domain,
                'activity_type' => $type,
                'subject' => $title ?: ucfirst($type),
                'notes' => $content,
                'due_date' => $date,
                'duration_minutes' => $duration,
                'created_by' => get_current_user_id(),
                'assigned_to_user_id' => get_current_user_id(),
                'assigned_to_school_domain' => $school_domain,
            ]);
        } elseif ($candidate_id) {
            $this->insert_activity_row([
                'entity_type' => 'contact',
                'entity_ref' => (string) $candidate_id,
                'activity_type' => $type,
                'subject' => $title ?: ucfirst($type),
                'notes' => $content,
                'due_date' => $date,
                'duration_minutes' => $duration,
                'created_by' => get_current_user_id(),
            ]);
        }

        $redirect = esc_url_raw($_POST['cmn_redirect'] ?? '');
        if ($redirect) {
            wp_redirect($redirect);
        } elseif ($school_id) {
            wp_redirect(admin_url('admin.php?page=cmn-school-profile&school_id=' . $school_id));
        } else {
            wp_redirect(admin_url('admin.php?page=cmn-candidate-profile&candidate_id=' . $candidate_id));
        }
        exit;
    }

    public function handle_complete_activity() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_complete_activity_nonce']) || !wp_verify_nonce($_POST['cmn_complete_activity_nonce'], 'cmn_complete_activity')) {
            wp_die('Invalid request');
        }
        $activity_id = intval($_POST['cmn_activity_id'] ?? 0);
        if ($activity_id) {
            global $wpdb;
            $table = $this->get_activity_table();
            $wpdb->update($table, [
                'completed_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ], [
                'id' => $activity_id,
            ], ['%s', '%s'], ['%d']);
        }
        $redirect = esc_url_raw($_POST['cmn_redirect'] ?? '');
        if ($redirect) {
            wp_redirect($redirect);
        } else {
            $portal_page = get_page_by_title('Portal');
            $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
            wp_redirect(add_query_arg(['view' => 'schools'], $portal_url));
        }
        exit;
    }

    public function handle_convert_client() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_convert_client_nonce']) || !wp_verify_nonce($_POST['cmn_convert_client_nonce'], 'cmn_convert_client')) {
            wp_die('Invalid request');
        }
        $school_domain = sanitize_text_field($_POST['cmn_school_domain'] ?? '');
        if ($school_domain === '') {
            wp_die('Missing school domain.');
        }
        $school_post_id = $this->get_school_post_id_by_domain($school_domain);
        if (!$school_post_id) {
            wp_die('School not found.');
        }
        if (!$this->user_can_access_school($school_post_id)) {
            wp_die('Unauthorized');
        }
        $primary_email = get_post_meta($school_post_id, 'cmn_primary_contact_email', true);
        if (!$primary_email) {
            $primary_email = get_post_meta($school_post_id, 'cmn_contact1_email', true);
        }
        if (!$primary_email) {
            $primary_email = get_post_meta($school_post_id, 'cmn_email', true);
        }
        if (!$primary_email || !is_email($primary_email)) {
            $redirect = esc_url_raw($_POST['cmn_redirect'] ?? '');
            if ($redirect) {
                wp_redirect(add_query_arg(['cmn_convert_msg' => rawurlencode('Primary contact email is missing.')], $redirect));
            } else {
                wp_die('Primary contact email is required.');
            }
            exit;
        }

        update_post_meta($school_post_id, 'cmn_status', 'client');
        $current_stage = get_post_meta($school_post_id, 'cmn_pipeline_stage', true);
        if ($current_stage !== 'lost') {
            update_post_meta($school_post_id, 'cmn_pipeline_stage', 'won');
        }
        $this->upsert_client_profile($school_domain, [
            'completion_status' => 'incomplete',
        ]);
        $token = $this->create_client_token($school_domain);
        $link = add_query_arg(['token' => $token], home_url('/school-registration/'));
        $subject = 'Complete your CoverMeNow profile';
        $message = "Please complete your CoverMeNow ONE profile using the secure link below:\n\n{$link}\n\nThis link expires in 7 days.";
        $previous_log_type = $GLOBALS['cmn_school_mail_log_type'] ?? null;
        $GLOBALS['cmn_school_mail_log_type'] = 'school_profile_invite';
        $this->send_school_email($primary_email, $subject, $message);
        if ($previous_log_type !== null) {
            $GLOBALS['cmn_school_mail_log_type'] = $previous_log_type;
        } else {
            unset($GLOBALS['cmn_school_mail_log_type']);
        }

        $redirect = esc_url_raw($_POST['cmn_redirect'] ?? '');
        if ($redirect) {
            wp_redirect(add_query_arg(['cmn_convert_msg' => rawurlencode('Client invite sent.')], $redirect));
        } else {
            wp_redirect(home_url('/portal'));
        }
        exit;
    }

    public function handle_register_school() {
        if (!isset($_POST['cmn_register_school_nonce']) || !wp_verify_nonce($_POST['cmn_register_school_nonce'], 'cmn_register_school')) {
            wp_die('Invalid request');
        }

        $client_token = isset($_POST['cmn_client_token']) ? sanitize_text_field($_POST['cmn_client_token']) : '';
        $school_name = sanitize_text_field($_POST['cmn_school_name'] ?? '');
        if ($school_name === '') {
            wp_die('School name is required.');
        }

        $email = sanitize_email($_POST['cmn_email'] ?? '');
        $email_domain = $this->get_email_domain($email);
        if ($email_domain === '') {
            wp_die('Valid email is required.');
        }

        if ($client_token) {
            $token_data = $this->validate_client_token($client_token);
            if (!$token_data || empty($token_data['school_email_domain']) || $token_data['school_email_domain'] !== $email_domain) {
                wp_die('Invalid or expired token.');
            }
            $post_id = $this->get_school_post_id_by_domain($email_domain);
            if (!$post_id) {
                wp_die('School not found.');
            }
        } else {
            $existing_id = $this->get_school_post_id_by_domain($email_domain);
            if ($existing_id) {
                $post_id = $existing_id;
                wp_update_post([
                    'ID' => $post_id,
                    'post_title' => $school_name,
                ]);
            } else {
                $post_id = wp_insert_post([
                    'post_type' => 'cmn_school',
                    'post_title' => $school_name,
                    'post_status' => 'publish',
                ]);
            }
        }

        if (!is_wp_error($post_id) && $post_id) {
            update_post_meta($post_id, 'cmn_location', sanitize_text_field($_POST['cmn_location'] ?? ''));
            update_post_meta($post_id, 'cmn_email', $email);
            update_post_meta($post_id, 'cmn_school_email_domain', $email_domain);
            update_post_meta($post_id, 'cmn_phone', sanitize_text_field($_POST['cmn_phone'] ?? ''));
            update_post_meta($post_id, 'cmn_website', esc_url_raw($_POST['cmn_website'] ?? ''));
            update_post_meta($post_id, 'cmn_contact1', sanitize_text_field($_POST['cmn_contact1'] ?? ''));
            update_post_meta($post_id, 'cmn_contact_role', sanitize_text_field($_POST['cmn_contact_role'] ?? ''));
            update_post_meta($post_id, 'cmn_primary_contact_name', sanitize_text_field($_POST['cmn_contact1'] ?? ''));
            update_post_meta($post_id, 'cmn_primary_contact_role', sanitize_text_field($_POST['cmn_contact_role'] ?? ''));
            update_post_meta($post_id, 'cmn_primary_contact_email', $email);
            update_post_meta($post_id, 'cmn_primary_contact_phone', sanitize_text_field($_POST['cmn_phone'] ?? ''));
            update_post_meta($post_id, 'cmn_address_line1', sanitize_text_field($_POST['cmn_address_line1'] ?? ''));
            update_post_meta($post_id, 'cmn_address_line2', sanitize_text_field($_POST['cmn_address_line2'] ?? ''));
            update_post_meta($post_id, 'cmn_town', sanitize_text_field($_POST['cmn_town'] ?? ''));
            update_post_meta($post_id, 'cmn_county', sanitize_text_field($_POST['cmn_county'] ?? ''));
            update_post_meta($post_id, 'cmn_postcode', sanitize_text_field($_POST['cmn_postcode'] ?? ''));
            update_post_meta($post_id, 'cmn_school_type', sanitize_text_field($_POST['cmn_school_type'] ?? ''));
            update_post_meta($post_id, 'cmn_pupil_count', sanitize_text_field($_POST['cmn_pupil_count'] ?? ''));
            update_post_meta($post_id, 'cmn_supply_frequency', sanitize_text_field($_POST['cmn_supply_frequency'] ?? ''));
            update_post_meta($post_id, 'cmn_use_agencies', sanitize_text_field($_POST['cmn_use_agencies'] ?? ''));
            update_post_meta($post_id, 'cmn_agency_count', sanitize_text_field($_POST['cmn_agency_count'] ?? ''));
            update_post_meta($post_id, 'cmn_notes', sanitize_textarea_field($_POST['cmn_notes'] ?? ''));
            if (!get_post_meta($post_id, 'cmn_school_id', true)) {
                update_post_meta($post_id, 'cmn_school_id', $this->generate_school_id());
            }
            $this->upsert_school_index($post_id);

            if ($client_token) {
                $this->upsert_client_profile($email_domain, [
                    'completion_status' => 'complete',
                    'school_type' => sanitize_text_field($_POST['cmn_school_type'] ?? ''),
                    'pupil_count' => sanitize_text_field($_POST['cmn_pupil_count'] ?? ''),
                    'supply_frequency' => sanitize_text_field($_POST['cmn_supply_frequency'] ?? ''),
                    'uses_agencies' => sanitize_text_field($_POST['cmn_use_agencies'] ?? ''),
                    'agency_count' => sanitize_text_field($_POST['cmn_agency_count'] ?? ''),
                    'completed_at' => current_time('mysql'),
                ]);
                $this->mark_client_token_used($client_token);
                wp_redirect(add_query_arg([
                    'cmn_submitted' => '1',
                    'cmn_client' => '1',
                ], wp_get_referer() ?: home_url()));
                exit;
            }

            update_post_meta($post_id, 'cmn_status', 'pending');
            if (!get_post_meta($post_id, 'cmn_pipeline_stage', true)) {
                update_post_meta($post_id, 'cmn_pipeline_stage', 'new_lead');
            }

            $admin_email = get_option('admin_email');
            $subject = 'New School Registration Request';
            $message = "A new school registration request was submitted.\n\nSchool: {$school_name}\nEmail: {$email}\nLocation: " . sanitize_text_field($_POST['cmn_location'] ?? '') . "\n\nReview in the CRM.";
            wp_mail($admin_email, $subject, $message);
        }

        wp_redirect(add_query_arg('cmn_submitted', '1', wp_get_referer() ?: home_url()));
        exit;
    }

    public function handle_register_candidate() {
        if (!isset($_POST['cmn_register_candidate_nonce']) || !wp_verify_nonce($_POST['cmn_register_candidate_nonce'], 'cmn_register_candidate')) {
            wp_die('Invalid request');
        }

        $candidate_name = sanitize_text_field($_POST['cmn_candidate_name'] ?? '');
        $candidate_email = sanitize_email($_POST['cmn_email'] ?? '');
        if ($candidate_name === '') {
            wp_die('Candidate name is required.');
        }

        $post_id = wp_insert_post([
            'post_type' => 'cmn_candidate',
            'post_title' => $candidate_name,
            'post_status' => 'publish',
        ]);

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, 'cmn_email', $candidate_email);
            update_post_meta($post_id, 'cmn_phone', sanitize_text_field($_POST['cmn_phone'] ?? ''));
            update_post_meta($post_id, 'cmn_location', sanitize_text_field($_POST['cmn_location'] ?? ''));
            update_post_meta($post_id, 'cmn_status', 'approved');
            update_post_meta($post_id, 'cmn_notes', sanitize_textarea_field($_POST['cmn_notes'] ?? ''));
            update_post_meta($post_id, 'cmn_house_number', sanitize_text_field($_POST['cmn_house_number'] ?? ''));
            update_post_meta($post_id, 'cmn_address_line1', sanitize_text_field($_POST['cmn_address_line1'] ?? ''));
            update_post_meta($post_id, 'cmn_address_line2', sanitize_text_field($_POST['cmn_address_line2'] ?? ''));
            update_post_meta($post_id, 'cmn_address_line3', sanitize_text_field($_POST['cmn_address_line3'] ?? ''));
            update_post_meta($post_id, 'cmn_town', sanitize_text_field($_POST['cmn_town'] ?? ''));
            update_post_meta($post_id, 'cmn_county', sanitize_text_field($_POST['cmn_county'] ?? ''));
            update_post_meta($post_id, 'cmn_postcode', sanitize_text_field($_POST['cmn_postcode'] ?? ''));
            $roles = isset($_POST['cmn_roles']) && is_array($_POST['cmn_roles']) ? array_map('sanitize_text_field', $_POST['cmn_roles']) : [];
            update_post_meta($post_id, 'cmn_roles', $roles);
            update_post_meta($post_id, 'cmn_no_dbs', isset($_POST['cmn_no_dbs']) ? '1' : '0');
            update_post_meta($post_id, 'cmn_roles_other', sanitize_text_field($_POST['cmn_roles_other'] ?? ''));
            update_post_meta($post_id, 'cmn_driving_licence', sanitize_text_field($_POST['cmn_driving_licence'] ?? ''));
            update_post_meta($post_id, 'cmn_car_owner', sanitize_text_field($_POST['cmn_car_owner'] ?? ''));
            update_post_meta($post_id, 'cmn_travel_distance', sanitize_text_field($_POST['cmn_travel_distance'] ?? ''));
            update_post_meta($post_id, 'cmn_dbs_update_service', sanitize_text_field($_POST['cmn_dbs_update_service'] ?? ''));
            $days = isset($_POST['cmn_availability_days']) && is_array($_POST['cmn_availability_days']) ? array_map('sanitize_text_field', $_POST['cmn_availability_days']) : [];
            update_post_meta($post_id, 'cmn_availability_days', $days);
            if (!get_post_meta($post_id, 'cmn_default_rate', true)) {
                update_post_meta($post_id, 'cmn_default_rate', '100');
            }
            $candidate_user_id = 0;
            $user_id = $this->ensure_candidate_user($candidate_name, $candidate_email);
            if ($user_id && !is_wp_error($user_id)) {
                $candidate_user_id = (int) $user_id;
                update_post_meta($post_id, 'cmn_user_id', $candidate_user_id);
                $this->send_candidate_verification_email((int) $user_id, $candidate_name, $candidate_email);
            }
            $cv = $this->handle_file_upload_with_attachment('cmn_cv_file');
            $dbs = $this->handle_file_upload_with_attachment('cmn_dbs_file');
            $id_doc = $this->handle_file_upload_with_attachment('cmn_id_file');

            $this->sync_registered_candidate_doc_upload($post_id, $candidate_user_id, 'cv', $cv);
            $this->sync_registered_candidate_doc_upload($post_id, $candidate_user_id, 'dbs', $dbs);
            $this->sync_registered_candidate_doc_upload($post_id, $candidate_user_id, 'id', $id_doc);

            if (!empty($cv['attachment_id'])) {
                $uploaded_at = (string) ($cv['uploaded_at'] ?? current_time('mysql'));
                update_post_meta($post_id, 'cmn_cv_original_attachment_id', (int) $cv['attachment_id']);
                update_post_meta($post_id, 'cmn_cv_original_uploaded_at', $uploaded_at);
                $this->add_candidate_cv_audit_entry(
                    $post_id,
                    'Candidate CV upload',
                    'Candidate uploaded original CV at ' . $uploaded_at . '.',
                    $candidate_user_id > 0 ? $candidate_user_id : get_current_user_id()
                );
                $this->mark_candidate_formatted_cv_outdated($post_id, true);
            }

            $admin_email = get_option('admin_email');
            $subject = 'New Candidate Registration Request';
            $message = "A new candidate registration request was submitted.\n\nCandidate: {$candidate_name}\nEmail: {$candidate_email}\nLocation: " . sanitize_text_field($_POST['cmn_location'] ?? '') . "\n\nReview in the CRM.";
            $this->send_candidate_email($admin_email, $subject, $message, [
                'type' => 'candidate_registration_admin',
                'related_candidate_id' => $post_id,
            ]);
        }

        wp_redirect(add_query_arg([
            'cmn_submitted' => '1',
            'cmn_email' => $candidate_email,
        ], wp_get_referer() ?: home_url()));
        exit;
    }

    private function ensure_candidate_user($candidate_name, $candidate_email) {
        if ($candidate_email === '') {
            return 0;
        }

        $existing = get_user_by('email', $candidate_email);
        if ($existing) {
            $user = new WP_User($existing->ID);
            if (!in_array('cmn_candidate', (array) $user->roles, true) || in_array('cmn_candidate_pending', (array) $user->roles, true)) {
                $user->set_role('cmn_candidate');
            }
            $this->ensure_candidate_notification_defaults((int) $existing->ID);
            return $existing->ID;
        }

        $base = sanitize_user(strstr($candidate_email, '@', true), true);
        if ($base === '') {
            $base = 'candidate';
        }
        $user_login = $base;
        $suffix = 1;
        while (username_exists($user_login)) {
            $user_login = $base . $suffix;
            $suffix++;
        }

        $password = wp_generate_password(12, false);
        $user_id = wp_insert_user([
            'user_login' => $user_login,
            'user_email' => $candidate_email,
            'display_name' => $candidate_name,
            'role' => 'cmn_candidate',
            'user_pass' => $password,
        ]);

        if ($user_id && !is_wp_error($user_id)) {
            $this->ensure_candidate_notification_defaults((int) $user_id);
        }

        return $user_id;
    }

    private function is_staff_user() {
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return false;
        }
        $roles = (array) $user->roles;
        return in_array('administrator', $roles, true)
            || in_array('cmn_admin', $roles, true)
            || in_array('cmn_staff', $roles, true)
            || in_array('cmn_account_manager', $roles, true);
    }

    private function is_admin_user($user_id = 0) {
        $user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();
        if (!$user || !$user->ID) {
            return false;
        }
        $roles = (array) $user->roles;
        return in_array('administrator', $roles, true) || in_array('cmn_admin', $roles, true);
    }

    private function is_wordpress_admin_user($user_id = 0) {
        $user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();
        if (!$user || !$user->ID) {
            return false;
        }
        return in_array('administrator', (array) $user->roles, true);
    }

    private function is_account_manager_user($user_id = 0) {
        $user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();
        if (!$user || !$user->ID) {
            return false;
        }
        return in_array('cmn_account_manager', (array) $user->roles, true);
    }

    private function is_staff_role($user_id = 0) {
        $user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();
        if (!$user || !$user->ID) {
            return false;
        }
        return in_array('cmn_staff', (array) $user->roles, true);
    }

    private function is_candidate_user($user_id = 0) {
        $user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();
        if (!$user || !$user->ID) {
            return false;
        }
        $roles = (array) $user->roles;
        return in_array('cmn_candidate', $roles, true)
            || in_array('cmn_candidate_pending', $roles, true)
            || in_array('candidate', $roles, true);
    }

    private function is_school_user($user_id = 0) {
        $user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();
        if (!$user || !$user->ID) {
            return false;
        }
        $roles = (array) $user->roles;
        return in_array('cmn_school_manager', $roles, true) || in_array('cmn_school_staff', $roles, true);
    }

    public function get_current_user_role() {
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return 'guest';
        }
        if ($this->is_admin_user($user->ID)) {
            return 'admin';
        }
        if ($this->is_account_manager_user($user->ID)) {
            return 'account_manager';
        }
        if ($this->is_staff_role($user->ID)) {
            return 'staff';
        }
        if ($this->is_candidate_user($user->ID)) {
            return 'candidate';
        }
        if ($this->is_school_user($user->ID)) {
            return 'school_user';
        }
        return 'guest';
    }

    private function get_assigned_school_ids_for_account_manager($user_id) {
        if (!$user_id) {
            return [];
        }
        $schools = get_posts([
            'post_type' => 'cmn_school',
            'posts_per_page' => 200,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'cmn_account_manager_user',
                    'value' => (string) $user_id,
                ],
            ],
        ]);
        return array_map('intval', (array) $schools);
    }

    private function get_assigned_candidate_ids_for_account_manager($user_id) {
        $school_ids = $this->get_assigned_school_ids_for_account_manager($user_id);
        if (!$school_ids) {
            return [];
        }
        $candidate_ids = [];
        foreach ($school_ids as $school_id) {
            $assigned = $this->get_assigned_candidates($school_id);
            foreach ($assigned as $candidate_id) {
                $candidate_ids[] = (int) $candidate_id;
            }
        }
        return array_values(array_unique(array_filter($candidate_ids)));
    }

    private function get_assigned_school_domains_for_account_manager($user_id) {
        $school_ids = $this->get_assigned_school_ids_for_account_manager($user_id);
        if (!$school_ids) {
            return [];
        }
        $domains = [];
        foreach ($school_ids as $school_id) {
            $domain = get_post_meta($school_id, 'cmn_school_email_domain', true);
            if (!$domain) {
                $email = get_post_meta($school_id, 'cmn_email', true);
                $domain = $this->get_email_domain($email);
            }
            if ($domain) {
                $domains[] = strtolower(trim($domain));
            }
        }
        return array_values(array_unique($domains));
    }

    public function user_can_access_school($school_identifier, $user_id = 0) {
        $user_id = $user_id ?: get_current_user_id();
        if (!$user_id) {
            return false;
        }
        if ($this->is_admin_user($user_id) || $this->is_staff_role($user_id)) {
            return true;
        }
        $school_post_id = $this->resolve_school_identifier($school_identifier);
        if (!$school_post_id) {
            return false;
        }
        if ($this->is_account_manager_user($user_id)) {
            $assigned = (int) get_post_meta($school_post_id, 'cmn_account_manager_user', true);
            return $assigned && $assigned === (int) $user_id;
        }
        if ($this->is_school_user($user_id)) {
            $user_school_id = $this->resolve_school_id_for_user($user_id);
            return $user_school_id && (int) $user_school_id === (int) $school_post_id;
        }
        return false;
    }

    public function user_can_view_candidate($candidate_id, $user_id = 0) {
        $user_id = $user_id ?: get_current_user_id();
        if (!$user_id || !$candidate_id) {
            return false;
        }
        if ($this->is_admin_user($user_id) || $this->is_staff_role($user_id)) {
            return true;
        }
        if ($this->is_account_manager_user($user_id)) {
            $assigned_candidates = $this->get_assigned_candidate_ids_for_account_manager($user_id);
            return in_array((int) $candidate_id, $assigned_candidates, true);
        }
        if ($this->is_candidate_user($user_id)) {
            $user = get_user_by('id', $user_id);
            $candidate_email = get_post_meta($candidate_id, 'cmn_email', true);
            return $user && $candidate_email && strtolower($candidate_email) === strtolower($user->user_email);
        }
        return false;
    }

    private static function generate_school_id_static() {
        global $wpdb;
        $meta_key = 'cmn_school_id';
        $school_index = $wpdb->prefix . 'cmn_school_index';
        $values = [];
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $school_index)) === $school_index) {
            $values = $wpdb->get_col("SELECT school_id FROM {$school_index} WHERE school_id LIKE 'CMN%'");
        } else {
            $values = $wpdb->get_col($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
                $meta_key,
                'CMN%'
            ));
        }
        $max = 0;
        foreach ($values as $value) {
            if (preg_match('/^CMN(\\d+)$/i', $value, $match)) {
                $num = (int) $match[1];
                if ($num > $max) {
                    $max = $num;
                }
            }
        }
        $next = $max + 1;
        for ($i = 0; $i < 50; $i++) {
            $candidate = 'CMN' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT 1 FROM {$school_index} WHERE school_id = %s",
                $candidate
            ));
            if (!$exists) {
                return $candidate;
            }
            $next++;
        }
        return 'CMN' . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    private function generate_school_id() {
        return self::generate_school_id_static();
    }

    private function is_super_admin_user() {
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            return false;
        }
        return strtolower($user->user_email) === 'jaynorton17@gmail.com';
    }

    private function redirect_with_message($message) {
        $referer = wp_get_referer() ?: home_url('/portal');
        wp_redirect(add_query_arg([
            'view' => 'schools',
            'cmn_imported' => '1',
            'cmn_import_msg' => rawurlencode($message),
        ], $referer));
        exit;
    }

    private function get_account_manager_users() {
        return get_users([
            'role__in' => ['cmn_account_manager'],
        ]);
    }

    private function get_school_options() {
        $args = [
            'post_type' => 'cmn_school',
            'posts_per_page' => 200,
            'orderby' => 'title',
            'order' => 'ASC',
        ];
        $user_id = get_current_user_id();
        if ($this->is_account_manager_user($user_id) && !$this->is_admin_user($user_id) && !$this->is_staff_role($user_id)) {
            $assigned_ids = $this->get_assigned_school_ids_for_account_manager($user_id);
            if (!$assigned_ids) {
                $assigned_ids = [0];
            }
            $args['post__in'] = $assigned_ids;
        }
        return get_posts($args);
    }

    private function get_school_contacts($school_id) {
        if (!$school_id) {
            return [];
        }
        return get_posts([
            'post_type' => 'cmn_contact',
            'posts_per_page' => 50,
            'meta_query' => [
                [
                    'key' => 'cmn_contact_school_id',
                    'value' => $school_id,
                ],
            ],
        ]);
    }

    private function get_school_contacts_by_domain($school_domain) {
        $school_domain = strtolower(trim((string) $school_domain));
        if ($school_domain === '') {
            return [];
        }
        $contact_ids = $this->get_school_contact_ids($school_domain);
        if (!$contact_ids) {
            return [];
        }
        return get_posts([
            'post_type' => 'cmn_contact',
            'posts_per_page' => 200,
            'post__in' => $contact_ids,
            'orderby' => 'post__in',
        ]);
    }

    private function get_unassigned_contacts() {
        return get_posts([
            'post_type' => 'cmn_contact',
            'posts_per_page' => 200,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'cmn_contact_school_id',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => 'cmn_contact_school_id',
                    'value' => '',
                    'compare' => '=',
                ],
                [
                    'key' => 'cmn_contact_school_id',
                    'value' => '0',
                    'compare' => '=',
                ],
            ],
        ]);
    }

    private function normalize_school_id($school_id) {
        $school_id = strtoupper(trim((string) $school_id));
        return $school_id;
    }

    private function get_email_domain($email) {
        $email = strtolower(trim((string) $email));
        if ($email === '' || strpos($email, '@') === false) {
            return '';
        }
        $parts = explode('@', $email);
        $domain = strtolower(trim(end($parts)));
        return $domain;
    }

    private function get_school_index_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_school_index';
    }

    private function upsert_school_index($post_id) {
        global $wpdb;
        $school_id = get_post_meta($post_id, 'cmn_school_id', true);
        if (!$school_id) {
            $school_id = $this->generate_school_id();
            update_post_meta($post_id, 'cmn_school_id', $school_id);
        }
        $school_id = $this->normalize_school_id($school_id);
        $name = get_the_title($post_id);
        $email = get_post_meta($post_id, 'cmn_email', true);
        $domain = $this->get_email_domain($email);
        if ($domain) {
            $existing = $this->get_school_post_id_by_domain($domain);
            if ($existing && (int) $existing !== (int) $post_id) {
                return $school_id;
            }
            update_post_meta($post_id, 'cmn_school_email_domain', $domain);
        }
        $table = $this->get_school_index_table();
        $wpdb->replace($table, [
            'school_id' => $school_id,
            'post_id' => (int) $post_id,
            'name' => $name,
            'school_email' => $email,
            'school_email_domain' => $domain ?: null,
            'updated_at' => current_time('mysql'),
        ], ['%s', '%d', '%s', '%s', '%s', '%s']);
        return $school_id;
    }

    private function get_school_post_id_by_school_id($school_id) {
        global $wpdb;
        $school_id = $this->normalize_school_id($school_id);
        if ($school_id === '') {
            return 0;
        }
        $table = $this->get_school_index_table();
        $post_id = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$table} WHERE school_id = %s LIMIT 1",
            $school_id
        ));
        if ($post_id) {
            return $post_id;
        }
        $existing = get_posts([
            'post_type' => 'cmn_school',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'cmn_school_id',
                    'value' => $school_id,
                ],
            ],
        ]);
        if ($existing) {
            $post_id = (int) $existing[0];
            $this->upsert_school_index($post_id);
            return $post_id;
        }
        return 0;
    }

    private function get_school_post_id_by_domain($domain) {
        global $wpdb;
        $domain = strtolower(trim((string) $domain));
        if ($domain === '') {
            return 0;
        }
        $table = $this->get_school_index_table();
        $post_id = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$table} WHERE school_email_domain = %s LIMIT 1",
            $domain
        ));
        if ($post_id) {
            return $post_id;
        }
        $existing = get_posts([
            'post_type' => 'cmn_school',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'cmn_school_email_domain',
                    'value' => $domain,
                ],
            ],
        ]);
        if ($existing) {
            $post_id = (int) $existing[0];
            $this->upsert_school_index($post_id);
            return $post_id;
        }
        return 0;
    }

    private function resolve_school_identifier($identifier) {
        if ($identifier === '' || $identifier === null) {
            return 0;
        }
        $identifier = trim((string) $identifier);
        if ($identifier === '') {
            return 0;
        }
        if (is_numeric($identifier)) {
            $post_id = (int) $identifier;
            $post = get_post($post_id);
            if ($post && $post->post_type === 'cmn_school') {
                return $post_id;
            }
        }
        if (strpos($identifier, '@') !== false) {
            return $this->get_school_post_id_by_domain($this->get_email_domain($identifier));
        }
        if (preg_match('/^CMN\\d+$/i', $identifier)) {
            return $this->get_school_post_id_by_school_id($identifier);
        }
        $by_domain = $this->get_school_post_id_by_domain($identifier);
        if ($by_domain) {
            return $by_domain;
        }
        return $this->find_school_by_name($identifier);
    }

    private function get_school_id_by_name($name) {
        $post_id = $this->find_school_by_name($name);
        if (!$post_id) {
            return '';
        }
        return (string) get_post_meta($post_id, 'cmn_school_id', true);
    }

    private function get_school_name_by_school_id($school_id) {
        $post_id = $this->get_school_post_id_by_school_id($school_id);
        if (!$post_id) {
            return '';
        }
        return get_the_title($post_id);
    }

    private function get_school_name_by_domain($domain) {
        $post_id = $this->get_school_post_id_by_domain($domain);
        if (!$post_id) {
            return '';
        }
        return get_the_title($post_id);
    }

    private function find_school_by_email_or_name($email, $name) {
        $email = sanitize_email($email);
        $domain = $this->get_email_domain($email);
        if ($domain) {
            $existing = $this->get_school_post_id_by_domain($domain);
            if ($existing) {
                return (int) $existing;
            }
        }
        $name = sanitize_text_field($name);
        if ($name !== '') {
            $by_title = get_page_by_title($name, OBJECT, 'cmn_school');
            if ($by_title) {
                return (int) $by_title->ID;
            }
        }
        return 0;
    }

    private function find_school_by_name($name) {
        $name = sanitize_text_field($name);
        if ($name === '') {
            return 0;
        }
        $by_title = get_page_by_title($name, OBJECT, 'cmn_school');
        if ($by_title) {
            return (int) $by_title->ID;
        }
        return 0;
    }

    private function find_school_domain_by_name($name, &$ambiguous = false) {
        $ambiguous = false;
        $name = sanitize_text_field($name);
        if ($name === '') {
            return '';
        }
        global $wpdb;
        $post_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title = %s LIMIT 2",
            'cmn_school',
            $name
        ));
        if (!$post_ids) {
            return '';
        }
        if (count($post_ids) > 1) {
            $ambiguous = true;
        }
        $post_id = (int) $post_ids[0];
        $domain = get_post_meta($post_id, 'cmn_school_email_domain', true);
        return $domain ? strtolower(trim($domain)) : '';
    }

    private function get_contact_school_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_contact_school';
    }

    private function resolve_school_domain_from_identifier($identifier) {
        if ($identifier === '' || $identifier === null) {
            return '';
        }
        $identifier = trim((string) $identifier);
        if ($identifier === '') {
            return '';
        }
        if (strpos($identifier, '@') !== false) {
            return $this->get_email_domain($identifier);
        }
        if (preg_match('/^CMN\\d+$/i', $identifier)) {
            $post_id = $this->get_school_post_id_by_school_id($identifier);
            if ($post_id) {
                return (string) get_post_meta($post_id, 'cmn_school_email_domain', true);
            }
        }
        if (strpos($identifier, '.') !== false && strpos($identifier, ' ') === false) {
            return strtolower($identifier);
        }
        $post_id = $this->find_school_by_name($identifier);
        if ($post_id) {
            return (string) get_post_meta($post_id, 'cmn_school_email_domain', true);
        }
        return '';
    }

    private function link_contact_to_school($contact_id, $school_domain, $is_primary = false) {
        global $wpdb;
        $contact_id = (int) $contact_id;
        $school_domain = strtolower(trim((string) $school_domain));
        if (!$contact_id || $school_domain === '') {
            return;
        }
        $school_post_id = $this->get_school_post_id_by_domain($school_domain);
        $school_id = $school_post_id ? (string) get_post_meta($school_post_id, 'cmn_school_id', true) : '';
        $table = $this->get_contact_school_table();
        if ($is_primary) {
            $wpdb->update($table, ['is_primary' => 0], ['contact_id' => $contact_id], ['%d'], ['%d']);
        }
        $wpdb->replace($table, [
            'school_id' => $school_id,
            'school_email_domain' => $school_domain,
            'contact_id' => $contact_id,
            'is_primary' => $is_primary ? 1 : 0,
            'created_at' => current_time('mysql'),
        ], ['%s', '%s', '%d', '%d', '%s']);
    }

    private function unlink_contact_from_school($contact_id, $school_domain) {
        global $wpdb;
        $contact_id = (int) $contact_id;
        $school_domain = strtolower(trim((string) $school_domain));
        if (!$contact_id || $school_domain === '') {
            return;
        }
        $table = $this->get_contact_school_table();
        $wpdb->delete($table, [
            'contact_id' => $contact_id,
            'school_email_domain' => $school_domain,
        ], ['%d', '%s']);
    }

    private function get_contact_school_links($contact_id) {
        global $wpdb;
        $contact_id = (int) $contact_id;
        if (!$contact_id) {
            return [];
        }
        $table = $this->get_contact_school_table();
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT school_email_domain, is_primary FROM {$table} WHERE contact_id = %d ORDER BY is_primary DESC",
            $contact_id
        ), ARRAY_A);
        if (!$rows) {
            $legacy_school_post = (int) get_post_meta($contact_id, 'cmn_contact_school_id', true);
            if ($legacy_school_post) {
                $legacy_school_id = get_post_meta($legacy_school_post, 'cmn_school_id', true);
                if ($legacy_school_id) {
                    $legacy_domain = get_post_meta($legacy_school_post, 'cmn_school_email_domain', true);
                    if ($legacy_domain) {
                        $this->link_contact_to_school($contact_id, $legacy_domain, true);
                        $rows = [
                            ['school_email_domain' => strtolower(trim($legacy_domain)), 'is_primary' => 1],
                        ];
                    }
                }
            }
        }
        return $rows ?: [];
    }

    private function clear_contact_links($contact_id) {
        global $wpdb;
        $contact_id = (int) $contact_id;
        if (!$contact_id) {
            return;
        }
        $table = $this->get_contact_school_table();
        $wpdb->delete($table, ['contact_id' => $contact_id], ['%d']);
    }

    private function get_contact_primary_school_id($contact_id) {
        $links = $this->get_contact_school_links($contact_id);
        foreach ($links as $link) {
            if (!empty($link['is_primary'])) {
                return $link['school_email_domain'];
            }
        }
        return $links ? $links[0]['school_email_domain'] : '';
    }

    private function get_school_contact_ids($school_id) {
        global $wpdb;
        $school_domain = strtolower(trim((string) $school_id));
        if ($school_domain === '') {
            return [];
        }
        $table = $this->get_contact_school_table();
        $rows = $wpdb->get_col($wpdb->prepare(
            "SELECT contact_id FROM {$table} WHERE school_email_domain = %s",
            $school_domain
        ));
        return array_map('intval', $rows ?: []);
    }

    private function get_contacts_not_linked_to_school($school_id) {
        $school_domain = strtolower(trim((string) $school_id));
        if ($school_domain === '') {
            return [];
        }
        $linked_ids = $this->get_school_contact_ids($school_domain);
        $args = [
            'post_type' => 'cmn_contact',
            'posts_per_page' => 200,
            'fields' => 'ids',
        ];
        if ($linked_ids) {
            $args['post__not_in'] = $linked_ids;
        }
        $contacts = get_posts($args);
        if (!$contacts) {
            return [];
        }
        return array_map('get_post', $contacts);
    }

    private function get_activity_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_activities';
    }

    private function get_client_profile_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_client_profiles';
    }

    private function get_client_token_table() {
        global $wpdb;
        return $wpdb->prefix . 'cmn_client_tokens';
    }

    private function upsert_client_profile($domain, $data) {
        global $wpdb;
        $table = $this->get_client_profile_table();
        $domain = strtolower(trim((string) $domain));
        if ($domain === '') {
            return;
        }
        $row = [
            'school_email_domain' => $domain,
            'completion_status' => $data['completion_status'] ?? 'incomplete',
            'school_type' => $data['school_type'] ?? null,
            'pupil_count' => $data['pupil_count'] ?? null,
            'supply_frequency' => $data['supply_frequency'] ?? null,
            'uses_agencies' => $data['uses_agencies'] ?? null,
            'agency_count' => $data['agency_count'] ?? null,
            'notes' => $data['notes'] ?? null,
            'completed_at' => $data['completed_at'] ?? null,
            'updated_at' => current_time('mysql'),
        ];
        $wpdb->replace($table, $row, ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);
    }

    private function create_client_token($domain) {
        global $wpdb;
        $domain = strtolower(trim((string) $domain));
        if ($domain === '') {
            return '';
        }
        $token = wp_generate_password(32, false, false);
        $hash = hash('sha256', $token);
        $table = $this->get_client_token_table();
        $wpdb->insert($table, [
            'school_email_domain' => $domain,
            'token_hash' => $hash,
            'expires_at' => gmdate('Y-m-d H:i:s', strtotime('+7 days')),
            'used_at' => null,
            'created_at' => current_time('mysql'),
        ], ['%s', '%s', '%s', '%s', '%s']);
        return $token;
    }

    private function validate_client_token($token) {
        global $wpdb;
        $token = trim((string) $token);
        if ($token === '') {
            return null;
        }
        $hash = hash('sha256', $token);
        $table = $this->get_client_token_table();
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE token_hash = %s LIMIT 1",
            $hash
        ), ARRAY_A);
        if (!$row) {
            return null;
        }
        if (!empty($row['used_at'])) {
            return null;
        }
        if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
            return null;
        }
        return $row;
    }

    private function mark_client_token_used($token) {
        global $wpdb;
        $token = trim((string) $token);
        if ($token === '') {
            return;
        }
        $hash = hash('sha256', $token);
        $table = $this->get_client_token_table();
        $wpdb->update($table, [
            'used_at' => current_time('mysql'),
        ], [
            'token_hash' => $hash,
        ], ['%s'], ['%s']);
    }

    private function normalize_activity_type($type) {
        $type = strtolower(trim((string) $type));
        if ($type === 'todo') {
            $type = 'task';
        }
        if (!in_array($type, ['call', 'email', 'note', 'task'], true)) {
            $type = 'note';
        }
        return $type;
    }

    private function insert_activity_row($data) {
        global $wpdb;
        $table = $this->get_activity_table();
        $entity_type = sanitize_text_field($data['entity_type'] ?? '');
        $entity_ref = sanitize_text_field($data['entity_ref'] ?? '');
        $activity_type = $this->normalize_activity_type($data['activity_type'] ?? 'note');
        $subject = sanitize_text_field($data['subject'] ?? '');
        $notes = sanitize_textarea_field($data['notes'] ?? '');
        $due_date = sanitize_text_field($data['due_date'] ?? '');
        $duration = isset($data['duration_minutes']) ? (int) $data['duration_minutes'] : null;
        $assigned_to = isset($data['assigned_to_user_id']) ? (int) $data['assigned_to_user_id'] : null;
        $assigned_school = sanitize_text_field($data['assigned_to_school_domain'] ?? '');
        $completed_at = sanitize_text_field($data['completed_at'] ?? '');
        $created_by = isset($data['created_by']) ? (int) $data['created_by'] : null;
        if ($entity_type === '' || $entity_ref === '' || $subject === '') {
            return 0;
        }
        $wpdb->insert($table, [
            'entity_type' => $entity_type,
            'entity_ref' => $entity_ref,
            'activity_type' => $activity_type,
            'subject' => $subject,
            'notes' => $notes,
            'due_date' => $due_date ?: null,
            'duration_minutes' => $duration ?: null,
            'assigned_to_user_id' => $assigned_to ?: null,
            'assigned_to_school_domain' => $assigned_school ?: null,
            'completed_at' => $completed_at ?: null,
            'created_by' => $created_by ?: null,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ], ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%s', '%s']);
        return (int) $wpdb->insert_id;
    }

    private function get_school_activities($school_domain, $limit = 20) {
        global $wpdb;
        $table = $this->get_activity_table();
        $school_domain = strtolower(trim((string) $school_domain));
        if ($school_domain === '') {
            return [];
        }
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE entity_type = 'school' AND entity_ref = %s ORDER BY created_at DESC LIMIT %d",
            $school_domain,
            (int) $limit
        ), ARRAY_A);
        if ($rows) {
            return $rows;
        }
        $school_post_id = $this->get_school_post_id_by_domain($school_domain);
        if (!$school_post_id) {
            return [];
        }
        return $this->get_legacy_school_activity_rows($school_post_id, $limit);
    }

    private function get_school_tasks($school_domain, $limit = 10) {
        global $wpdb;
        $table = $this->get_activity_table();
        $school_domain = strtolower(trim((string) $school_domain));
        if ($school_domain === '') {
            return [];
        }
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE entity_type = 'school' AND entity_ref = %s AND activity_type = 'task' AND completed_at IS NULL ORDER BY due_date ASC, created_at DESC LIMIT %d",
            $school_domain,
            (int) $limit
        ), ARRAY_A);
        if ($rows) {
            return $rows;
        }
        $school_post_id = $this->get_school_post_id_by_domain($school_domain);
        if (!$school_post_id) {
            return [];
        }
        return $this->get_legacy_school_task_rows($school_post_id, $limit);
    }

    private function get_legacy_school_activity_rows($school_post_id, $limit) {
        $activities = get_posts([
            'post_type' => 'cmn_activity',
            'posts_per_page' => $limit,
            'meta_query' => [
                [
                    'key' => 'cmn_related_type',
                    'value' => 'school',
                ],
                [
                    'key' => 'cmn_related_id',
                    'value' => $school_post_id,
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        $rows = [];
        foreach ($activities as $activity) {
            $rows[] = [
                'id' => $activity->ID,
                'activity_type' => $this->normalize_activity_type(get_post_meta($activity->ID, 'cmn_activity_type', true)),
                'subject' => $activity->post_title,
                'notes' => get_post_meta($activity->ID, 'cmn_activity_content', true),
                'due_date' => get_post_meta($activity->ID, 'cmn_activity_date', true),
                'duration_minutes' => get_post_meta($activity->ID, 'cmn_activity_duration', true),
                'completed_at' => get_post_meta($activity->ID, 'cmn_activity_status', true) === 'done' ? get_post_time('Y-m-d H:i:s', true, $activity) : null,
            ];
        }
        return $rows;
    }

    private function get_legacy_school_task_rows($school_post_id, $limit) {
        $activities = get_posts([
            'post_type' => 'cmn_activity',
            'posts_per_page' => $limit,
            'meta_query' => [
                [
                    'key' => 'cmn_related_type',
                    'value' => 'school',
                ],
                [
                    'key' => 'cmn_related_id',
                    'value' => $school_post_id,
                ],
                [
                    'key' => 'cmn_activity_type',
                    'value' => 'todo',
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        $rows = [];
        foreach ($activities as $activity) {
            $status = get_post_meta($activity->ID, 'cmn_activity_status', true);
            if ($status === 'done') {
                continue;
            }
            $rows[] = [
                'id' => $activity->ID,
                'activity_type' => 'task',
                'subject' => $activity->post_title,
                'notes' => get_post_meta($activity->ID, 'cmn_activity_content', true),
                'due_date' => get_post_meta($activity->ID, 'cmn_activity_date', true),
                'duration_minutes' => get_post_meta($activity->ID, 'cmn_activity_duration', true),
                'completed_at' => null,
            ];
        }
        return $rows;
    }

    private function create_or_update_contact($data) {
        $name = sanitize_text_field($data['name'] ?? '');
        $email = sanitize_email($data['email'] ?? '');
        $phone = sanitize_text_field($data['phone'] ?? '');
        $role = sanitize_text_field($data['role'] ?? '');
        $school_id = isset($data['school_id']) ? (int) $data['school_id'] : 0;
        $school_domain = isset($data['school_domain']) ? sanitize_text_field($data['school_domain']) : '';
        $is_primary = !empty($data['is_primary']);
        if ($name === '') {
            return 0;
        }
        if ($email && !is_email($email)) {
            return 0;
        }
        $contact_id = 0;
        if ($email) {
            $existing = get_posts([
                'post_type' => 'cmn_contact',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'meta_query' => [
                    [
                        'key' => 'cmn_contact_email',
                        'value' => $email,
                    ],
                ],
            ]);
            if ($existing) {
                $contact_id = (int) $existing[0];
            }
        }
        if ($contact_id) {
            wp_update_post([
                'ID' => $contact_id,
                'post_title' => $name,
            ]);
        } else {
            $contact_id = wp_insert_post([
                'post_type' => 'cmn_contact',
                'post_title' => $name,
                'post_status' => 'publish',
            ]);
        }
        if (is_wp_error($contact_id) || !$contact_id) {
            return 0;
        }
        update_post_meta($contact_id, 'cmn_contact_name', $name);
        update_post_meta($contact_id, 'cmn_contact_email', $email);
        update_post_meta($contact_id, 'cmn_contact_phone', $phone);
        update_post_meta($contact_id, 'cmn_contact_role', $role);
        if ($school_id) {
            update_post_meta($contact_id, 'cmn_contact_school_id', $school_id);
        }
        if ($school_domain) {
            $this->link_contact_to_school($contact_id, $school_domain, $is_primary);
        }
        return $contact_id;
    }

    private function store_cover_manager_split($post_id, $full_name) {
        $full_name = trim((string) $full_name);
        if ($full_name === '') {
            update_post_meta($post_id, 'cmn_cover_manager_first_name', '');
            update_post_meta($post_id, 'cmn_cover_manager_last_name', '');
            return;
        }
        $parts = preg_split('/\s+/', $full_name);
        $first = $parts ? $parts[0] : '';
        $last = '';
        if (count($parts) > 1) {
            $last = $parts[count($parts) - 1];
        }
        update_post_meta($post_id, 'cmn_cover_manager_first_name', $first);
        update_post_meta($post_id, 'cmn_cover_manager_last_name', $last);
    }

    private function build_password_reset_url($user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return wp_lostpassword_url();
        }
        $key = get_password_reset_key($user);
        if (is_wp_error($key)) {
            return wp_lostpassword_url();
        }
        return $this->get_portal_reset_url($user->user_login, $key);
    }

    private function verify_candidate_email_token($user_id, $token) {
        if (!$user_id || $token === '') {
            return ['success' => false, 'message' => 'This verification link is invalid or has expired.'];
        }
        $stored = get_user_meta($user_id, 'cmn_email_verify_hash', true);
        $hash = hash('sha256', $token);
        if (!$stored || !hash_equals($stored, $hash)) {
            return ['success' => false, 'message' => 'This verification link is invalid or has expired.'];
        }

        update_user_meta($user_id, 'cmn_email_verified', '1');
        update_user_meta($user_id, 'cmn_email_verified_at', current_time('mysql'));
        delete_user_meta($user_id, 'cmn_email_verify_hash');

        $user = get_user_by('id', $user_id);
        if ($user instanceof WP_User) {
            if (in_array('cmn_candidate_pending', (array) $user->roles, true) && !in_array('cmn_candidate', (array) $user->roles, true)) {
                $user->remove_role('cmn_candidate_pending');
                $user->add_role('cmn_candidate');
            }
            $candidate = get_posts([
                'post_type' => 'cmn_candidate',
                'posts_per_page' => 1,
                'meta_query' => [
                    [
                        'key' => 'cmn_email',
                        'value' => $user->user_email,
                    ],
                ],
            ]);
            if (!empty($candidate)) {
                update_post_meta($candidate[0]->ID, 'cmn_email_verified', '1');
            }
        }

        return ['success' => true, 'message' => 'Thank you — your email is now verified.'];
    }

    private function send_candidate_verification_email($user_id, $candidate_name, $candidate_email) {
        if (!$user_id || $candidate_email === '') {
            return;
        }
        $verified = get_user_meta($user_id, 'cmn_email_verified', true);
        if ($verified === '1') {
            return;
        }
        $token = wp_generate_password(32, false);
        $hash = hash('sha256', $token);
        update_user_meta($user_id, 'cmn_email_verify_hash', $hash);
        update_user_meta($user_id, 'cmn_email_verified', '0');
        update_user_meta($user_id, 'cmn_email_verify_sent', time());

        $user = get_user_by('id', $user_id);
        $login = ($user instanceof WP_User) ? $user->user_login : '';
        $key = ($user instanceof WP_User) ? get_password_reset_key($user) : '';
        if (is_wp_error($key)) {
            $key = '';
        }
        $verify_url = $this->get_candidate_verify_url($user_id, $token);
        $reset_url = ($login && $key) ? $this->get_portal_reset_url($login, $key) : $this->build_password_reset_url($user_id);

        $safe_name = $candidate_name ? $candidate_name : 'there';
        $subject = 'Verify your email for CoverMeNow ONE';
        $message = "Hi {$safe_name},\n\nThanks for registering with CoverMeNow ONE.\n\nPlease verify your email to continue:\n{$verify_url}\n\nOnce verified, you can complete the rest of your profile.\n\nSet your password here:\n{$reset_url}\n\nIf you did not request this, please ignore this email.";
        $this->send_candidate_email($candidate_email, $subject, $message, [
            'type' => 'candidate_verification',
            'related_candidate_id' => $this->get_candidate_id_for_user($user_id),
        ]);
    }

    public function handle_verify_candidate_email() {
        $user_id = isset($_GET['uid']) ? intval($_GET['uid']) : 0;
        $token = isset($_GET['token']) ? sanitize_text_field(wp_unslash($_GET['token'])) : '';
        $portal_verify_url = add_query_arg([
            'view' => 'candidate-verify',
            'uid' => $user_id,
            'token' => rawurlencode($token),
        ], $this->get_portal_base_url());
        wp_redirect($portal_verify_url);
        exit;
    }

    public function handle_resend_candidate_verification() {
        if (!isset($_POST['cmn_resend_candidate_verification_nonce']) || !wp_verify_nonce($_POST['cmn_resend_candidate_verification_nonce'], 'cmn_resend_candidate_verification')) {
            wp_redirect(add_query_arg([
                'view' => 'candidate-verify',
                'cmn_error' => rawurlencode('Invalid request.'),
            ], $this->get_portal_base_url()));
            exit;
        }
        $user_id = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
        $redirect = add_query_arg(['view' => 'candidate-verify'], $this->get_portal_base_url());
        if ($user_id) {
            $user = get_user_by('id', $user_id);
            if ($user instanceof WP_User) {
                $this->send_candidate_verification_email($user_id, $user->display_name, $user->user_email);
            }
            $redirect = add_query_arg(['view' => 'candidate-verify', 'uid' => $user_id], $this->get_portal_base_url());
        }
        wp_redirect(add_query_arg(['cmn_notice' => rawurlencode('Verification email sent. Please check your inbox.')], $redirect));
        exit;
    }

    public function handle_toggle_availability() {
        if (!isset($_POST['cmn_toggle_availability_nonce']) || !wp_verify_nonce($_POST['cmn_toggle_availability_nonce'], 'cmn_toggle_availability')) {
            wp_die('Invalid request');
        }
        if (!$this->is_candidate_user()) {
            wp_die('Unauthorized');
        }

        // For now, map to the current logged-in user candidate record by email if exists.
        $user = wp_get_current_user();
        if (!$user || !$user->user_email) {
            wp_redirect(add_query_arg('cmn_error', 'login', wp_get_referer() ?: home_url()));
            exit;
        }

        $candidate = get_posts([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'cmn_email',
                    'value' => $user->user_email,
                ],
            ],
        ]);

        if (!empty($candidate)) {
            $candidate_id = $candidate[0]->ID;
            $value = isset($_POST['cmn_available_tomorrow']) ? sanitize_text_field($_POST['cmn_available_tomorrow']) : '0';
            $value = $value === '1' ? '1' : '0';
            update_post_meta($candidate_id, 'cmn_available_tomorrow', $value);

            $tz = wp_timezone();
            $now = new DateTime('now', $tz);
            $hour = (int) $now->format('G');
            $window_open = ($hour >= 19 || $hour < 8);
            if (!$window_open) {
                wp_redirect(add_query_arg('cmn_error', 'window', wp_get_referer() ?: home_url()));
                exit;
            }
            $target_date = $hour >= 19 ? (clone $now)->modify('+1 day')->format('Y-m-d') : $now->format('Y-m-d');
            update_post_meta($candidate_id, 'cmn_available_date', $value === '1' ? $target_date : '');

            if ($value === '1') {
                $admin_email = get_option('admin_email');
                $subject = 'Candidate Available Tomorrow';
                $message = "Candidate {$candidate[0]->post_title} has marked available tomorrow.\n\nReview in the CRM.";
                wp_mail($admin_email, $subject, $message);
            }
        }

        wp_redirect(add_query_arg('cmn_updated', '1', wp_get_referer() ?: home_url()));
        exit;
    }

    public function handle_save_calendar() {
        if (!isset($_POST['cmn_save_calendar_nonce']) || !wp_verify_nonce($_POST['cmn_save_calendar_nonce'], 'cmn_save_calendar')) {
            wp_die('Invalid request');
        }
        if (!$this->is_candidate_user()) {
            wp_die('Unauthorized');
        }

        $user = wp_get_current_user();
        if (!$user || !$user->user_email) {
            wp_redirect(add_query_arg('cmn_error', 'login', wp_get_referer() ?: home_url()));
            exit;
        }

        $candidate = get_posts([
            'post_type' => 'cmn_candidate',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'cmn_email',
                    'value' => $user->user_email,
                ],
            ],
        ]);

        if (!empty($candidate)) {
            $candidate_id = $candidate[0]->ID;
            $raw = wp_unslash($_POST['cmn_calendar_data'] ?? '{}');
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                $decoded = [];
            }
            $clean = [];
            foreach ($decoded as $date => $status) {
                if (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $date)) {
                    continue;
                }
                $status = sanitize_text_field($status);
                if (!in_array($status, ['available', 'unavailable'], true)) {
                    continue;
                }
                $clean[$date] = $status;
            }
            update_post_meta($candidate_id, 'cmn_calendar_data', wp_json_encode($clean));
        }

        wp_redirect(add_query_arg('cmn_saved', '1', wp_get_referer() ?: home_url()));
        exit;
    }

    public function handle_mark_available() {
        if (!check_ajax_referer('cmn_mark_available', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }

        $candidate_id = $this->get_candidate_id_for_user();
        if (!$candidate_id) {
            wp_send_json_error(['message' => 'Candidate profile not found.'], 404);
        }

        $tz = wp_timezone();
        try {
            $now = new DateTime('now', $tz);
        } catch (Exception $e) {
            $now = new DateTime('@' . current_time('timestamp'));
            $now->setTimezone($tz);
            error_log('CMN availability fallback DateTime used in AJAX handler: ' . $e->getMessage());
        }
        $availability_window = $this->get_candidate_availability_window($now);
        $allowed = !empty($availability_window['is_open']);
        $target_date = (string) ($availability_window['target_date'] ?? '');
        if (!$target_date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $target_date)) {
            $target_date = date_i18n('Y-m-d', strtotime('+1 day', current_time('timestamp')));
            error_log('CMN availability AJAX fallback target_date used.');
        }
        $already_marked = $this->has_candidate_availability($candidate_id, $target_date);
        if (!$allowed) {
            wp_send_json_error([
                'message' => 'You can confirm availability from 7pm until 8am.',
                'available' => $already_marked,
                'button_text' => $already_marked ? 'I’m NOT available tomorrow morning' : 'I’m available tomorrow morning',
                'button_enabled' => false,
                'status_text' => $already_marked ? 'Confirmed available.' : 'Not confirmed yet.',
            ], 400);
        }

        if ($this->is_candidate_unavailable($candidate_id, $target_date)) {
            wp_send_json_error([
                'message' => 'You’ve marked yourself unavailable for tomorrow in your calendar.',
                'available' => false,
                'button_text' => 'I’m available tomorrow morning',
                'button_enabled' => true,
                'status_text' => 'Confirmed unavailable.',
            ], 400);
        }
        global $wpdb;
        $table = $this->get_candidate_availability_table();
        if ($already_marked) {
            $wpdb->delete($table, [
                'candidate_id' => $candidate_id,
                'available_date' => $target_date,
            ], ['%d', '%s']);
            wp_send_json_success([
                'message' => 'You’re now marked as unavailable for tomorrow morning.',
                'available' => false,
                'button_enabled' => true,
                'status_text' => 'Not confirmed yet.',
                'button_text' => 'I’m available tomorrow morning',
            ]);
        }

        $inserted = $wpdb->insert($table, [
            'candidate_id' => $candidate_id,
            'available_date' => $target_date,
            'available_type' => 'morning',
            'created_at' => current_time('mysql'),
        ], ['%d', '%s', '%s', '%s']);

        if (!$inserted) {
            wp_send_json_error(['message' => 'Unable to save availability.'], 500);
        }

        wp_send_json_success([
            'message' => 'You’re marked as available for tomorrow morning.',
            'date' => $target_date,
            'available' => true,
            'button_enabled' => true,
            'status_text' => 'Confirmed available.',
            'button_text' => 'I’m NOT available tomorrow morning',
        ]);
    }

    public function handle_update_calendar_day() {
        if (!check_ajax_referer('cmn_update_calendar_day', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $candidate_id = $this->get_candidate_id_for_user();
        if (!$candidate_id) {
            wp_send_json_error(['message' => 'Candidate profile not found.'], 404);
        }
        $date = sanitize_text_field($_POST['date'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? 'neutral');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            wp_send_json_error(['message' => 'Invalid date.'], 400);
        }
        if (!in_array($status, ['available', 'unavailable', 'neutral'], true)) {
            wp_send_json_error(['message' => 'Invalid status.'], 400);
        }
        [$today, $limit] = $this->get_calendar_limit_dates();
        if ($date < $today) {
            wp_send_json_error(['message' => 'Past dates cannot be updated.'], 400);
        }
        if ($date > $limit) {
            wp_send_json_error(['message' => 'Date is out of range.'], 400);
        }
        if (!$this->is_weekday_date($date)) {
            wp_send_json_error(['message' => 'Only Monday to Friday can be updated.'], 400);
        }

        global $wpdb;
        $table = $this->get_candidate_calendar_table();
        if ($status === 'neutral') {
            $wpdb->delete($table, [
                'candidate_id' => $candidate_id,
                'date' => $date,
            ], ['%d', '%s']);
        } else {
            $wpdb->replace($table, [
                'candidate_id' => $candidate_id,
                'date' => $date,
                'status' => $status,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ], ['%d', '%s', '%s', '%s', '%s']);
        }
        $summary = $this->get_candidate_calendar_summary($candidate_id);

        wp_send_json_success([
            'date' => $date,
            'status' => $status,
            'summary' => $summary,
        ]);
    }

    public function handle_get_calendar_availability() {
        if (!check_ajax_referer('cmn_update_calendar_day', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $candidate_id = $this->get_candidate_id_for_user();
        if (!$candidate_id) {
            wp_send_json_error(['message' => 'Candidate profile not found.'], 404);
        }
        [$today, $limit] = $this->get_calendar_limit_dates();
        $map = $this->get_candidate_calendar_map($candidate_id, $today, $limit);
        $summary = $this->get_candidate_calendar_summary($candidate_id);
        wp_send_json_success([
            'calendar' => $map,
            'summary' => $summary,
        ]);
    }

    public function handle_bulk_update_calendar() {
        if (!check_ajax_referer('cmn_bulk_update_calendar', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $candidate_id = $this->get_candidate_id_for_user();
        if (!$candidate_id) {
            wp_send_json_error(['message' => 'Candidate profile not found.'], 404);
        }
        $start = sanitize_text_field($_POST['start_date'] ?? '');
        $end = sanitize_text_field($_POST['end_date'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            wp_send_json_error(['message' => 'Select a valid date range.'], 400);
        }
        if (!in_array($status, ['available', 'unavailable'], true)) {
            wp_send_json_error(['message' => 'Invalid bulk status.'], 400);
        }
        if ($end < $start) {
            wp_send_json_error(['message' => 'End date must be on or after start date.'], 400);
        }
        [$today, $limit] = $this->get_calendar_limit_dates();
        if ($start < $today || $end > $limit) {
            wp_send_json_error(['message' => 'Range must be within the next 30 days.'], 400);
        }
        $start_dt = DateTime::createFromFormat('Y-m-d', $start, wp_timezone());
        $end_dt = DateTime::createFromFormat('Y-m-d', $end, wp_timezone());
        if (!$start_dt || !$end_dt) {
            wp_send_json_error(['message' => 'Invalid date range.'], 400);
        }

        global $wpdb;
        $table = $this->get_candidate_calendar_table();
        $count = 0;
        $cursor = clone $start_dt;
        while ($cursor <= $end_dt) {
            $date = $cursor->format('Y-m-d');
            if ($this->is_weekday_date($date)) {
                $wpdb->replace($table, [
                    'candidate_id' => $candidate_id,
                    'date' => $date,
                    'status' => $status,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ], ['%d', '%s', '%s', '%s', '%s']);
                $count++;
            }
            $cursor->modify('+1 day');
        }
        $summary = $this->get_candidate_calendar_summary($candidate_id);
        wp_send_json_success([
            'message' => $count ? 'Availability updated.' : 'No weekday dates in selected range.',
            'updated' => $count,
            'summary' => $summary,
            'calendar' => $this->get_candidate_calendar_map($candidate_id, $today, $limit),
        ]);
    }

    public function handle_clear_calendar() {
        if (!check_ajax_referer('cmn_clear_calendar', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $candidate_id = $this->get_candidate_id_for_user();
        if (!$candidate_id) {
            wp_send_json_error(['message' => 'Candidate profile not found.'], 404);
        }
        $mode = sanitize_text_field($_POST['mode'] ?? 'next30');
        [$today, $limit] = $this->get_calendar_limit_dates();
        $start = $today;
        $end = $limit;
        if ($mode === 'range') {
            $start = sanitize_text_field($_POST['start_date'] ?? '');
            $end = sanitize_text_field($_POST['end_date'] ?? '');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
                wp_send_json_error(['message' => 'Select a valid date range to clear.'], 400);
            }
            if ($end < $start || $start < $today || $end > $limit) {
                wp_send_json_error(['message' => 'Range must be within the next 30 days.'], 400);
            }
        }

        global $wpdb;
        $table = $this->get_candidate_calendar_table();
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table} WHERE candidate_id = %d AND date BETWEEN %s AND %s",
            $candidate_id,
            $start,
            $end
        ));
        $summary = $this->get_candidate_calendar_summary($candidate_id);
        wp_send_json_success([
            'message' => 'Availability cleared.',
            'summary' => $summary,
            'calendar' => $this->get_candidate_calendar_map($candidate_id, $today, $limit),
        ]);
    }

    public function handle_dismiss_candidate_tour() {
        if (!check_ajax_referer('cmn_dismiss_candidate_tour', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        update_user_meta(get_current_user_id(), 'cmn_candidate_tour_dismissed', '1');
        wp_send_json_success(['message' => 'Tour dismissed.']);
    }

    public function handle_get_candidate_settings() {
        if (!check_ajax_referer('cmn_candidate_settings', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        $this->ensure_candidate_notification_defaults($user_id);
        $theme = $this->get_user_theme_scheme($user_id);
        $prefs = [];
        foreach ($this->get_candidate_notification_pref_defaults() as $meta_key => $default) {
            $value = get_user_meta($user_id, $meta_key, true);
            if ($value === '') {
                $value = $default;
            }
            $prefs[$meta_key] = $value === '0' ? 0 : 1;
        }
        wp_send_json_success([
            'theme' => $theme,
            'preferences' => $prefs,
            'deletion_requested' => get_user_meta($user_id, 'cmn_deletion_requested', true) ? 1 : 0,
            'deletion_requested_at' => (string) get_user_meta($user_id, 'cmn_deletion_requested_at', true),
        ]);
    }

    public function handle_save_candidate_settings() {
        if (!check_ajax_referer('cmn_candidate_settings', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        $theme = $this->normalize_theme_scheme($_POST['theme'] ?? 'default');
        $this->update_user_theme_scheme($user_id, $theme);
        $raw_prefs = isset($_POST['preferences']) ? (array) $_POST['preferences'] : [];
        foreach ($this->get_candidate_notification_pref_defaults() as $meta_key => $default) {
            if (array_key_exists($meta_key, $raw_prefs)) {
                $value = (string) $raw_prefs[$meta_key];
            } else {
                $value = (string) ($_POST[$meta_key] ?? $default);
            }
            update_user_meta($user_id, $meta_key, $value === '0' ? '0' : '1');
        }
        wp_send_json_success([
            'message' => 'Settings saved.',
            'theme' => $theme,
        ]);
    }

    public function handle_get_theme_settings() {
        if (!check_ajax_referer('cmn_theme_settings', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        wp_send_json_success([
            'theme' => $this->get_user_theme_scheme($user_id),
            'options' => $this->get_theme_scheme_choices(),
        ]);
    }

    public function handle_save_theme_settings() {
        if (!check_ajax_referer('cmn_theme_settings', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        $theme = $this->normalize_theme_scheme($_POST['theme'] ?? 'default');
        $this->update_user_theme_scheme($user_id, $theme);
        wp_send_json_success([
            'message' => 'Colour scheme saved.',
            'theme' => $theme,
        ]);
    }

    public function handle_candidate_request_delete_account() {
        if (!check_ajax_referer('cmn_candidate_settings', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user = wp_get_current_user();
        if (!$user || !$user->ID) {
            wp_send_json_error(['message' => 'User not found.'], 404);
        }
        if (get_user_meta($user->ID, 'cmn_deletion_requested', true)) {
            wp_send_json_success([
                'message' => 'Deletion request is already pending admin action.',
                'pending' => 1,
            ]);
        }

        $typed_email = strtolower(trim((string) ($_POST['typed_email'] ?? '')));
        $actual_email = strtolower(trim((string) $user->user_email));
        if ($typed_email === '' || $typed_email !== $actual_email) {
            wp_send_json_error(['message' => 'Email does not match your account email.'], 400);
        }

        $requested_at = current_time('mysql');
        update_user_meta($user->ID, 'cmn_deletion_requested', '1');
        update_user_meta($user->ID, 'cmn_deletion_requested_at', $requested_at);

        $portal_url = $this->get_portal_base_url();
        $link = add_query_arg([
            'view' => 'candidates',
            'cmn_status' => 'deletion_requested',
            'candidate_id' => (int) $this->get_candidate_id_for_user($user->ID),
        ], $portal_url);
        $display_name = trim((string) $user->display_name) ?: $user->user_login;
        $message = sprintf('%s (%s) requested deletion. User ID %d.', $display_name, $user->user_email, (int) $user->ID);
        foreach ($this->get_admin_users_for_support() as $admin_id) {
            $this->add_notification((int) $admin_id, 'candidate_delete_request', 'Account deletion request', $message, $link);
        }

        wp_send_json_success([
            'message' => 'Request sent to admin.',
            'pending' => 1,
            'requested_at' => $requested_at,
        ]);
    }

    public function handle_admin_delete_candidate_account() {
        if (!check_ajax_referer('cmn_staff_manage', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->can_manage_staff_users()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $confirm_text = strtoupper(trim((string) ($_POST['confirm_text'] ?? '')));
        if ($confirm_text !== 'DELETE') {
            wp_send_json_error(['message' => 'Type DELETE to confirm account deletion.'], 400);
        }
        $candidate_id = (int) ($_POST['candidate_id'] ?? 0);
        if (!$candidate_id || get_post_type($candidate_id) !== 'cmn_candidate') {
            wp_send_json_error(['message' => 'Candidate not found.'], 404);
        }
        $candidate_user_id = (int) get_post_meta($candidate_id, 'cmn_user_id', true);
        if (!$candidate_user_id) {
            wp_send_json_error(['message' => 'Candidate account is not linked to a user.'], 400);
        }
        if (!get_user_meta($candidate_user_id, 'cmn_deletion_requested', true)) {
            wp_send_json_error(['message' => 'No deletion request is pending for this candidate.'], 400);
        }

        global $wpdb;
        $wpdb->delete($this->get_candidate_availability_table(), ['candidate_id' => $candidate_id], ['%d']);
        $wpdb->delete($this->get_candidate_calendar_table(), ['candidate_id' => $candidate_id], ['%d']);
        $wpdb->delete($this->get_candidate_requests_table(), ['candidate_id' => $candidate_id], ['%d']);
        $wpdb->delete($wpdb->prefix . 'cmn_support_feedback', ['user_id' => $candidate_user_id], ['%d']);

        wp_delete_post($candidate_id, true);
        if (!function_exists('wp_delete_user')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
        wp_delete_user($candidate_user_id);

        wp_send_json_success([
            'message' => 'Candidate account deleted.',
            'candidate_id' => $candidate_id,
            'user_id' => $candidate_user_id,
        ]);
    }

    private function create_candidate_cv_attachment_from_upload($file_field, $candidate_id, $candidate_user_id, $doc_type, $allowed_mimes, $title_stub = '') {
        $candidate_id = (int) $candidate_id;
        $candidate_user_id = (int) $candidate_user_id;
        if (empty($_FILES[$file_field]) || !is_array($_FILES[$file_field])) {
            return new WP_Error('cmn_missing_file', 'Missing uploaded file: ' . $file_field);
        }
        $file = $_FILES[$file_field];
        $ext = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($ext === '' || !isset($allowed_mimes[$ext])) {
            return new WP_Error('cmn_invalid_file_type', 'File type not allowed for ' . $file_field . '.');
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $uploaded = wp_handle_upload($file, [
            'test_form' => false,
            'mimes' => $allowed_mimes,
        ]);
        if (!empty($uploaded['error'])) {
            return new WP_Error('cmn_upload_failed', sanitize_text_field((string) $uploaded['error']));
        }
        $title_stub = $title_stub !== '' ? $title_stub : ('candidate-' . $candidate_id . '-' . $doc_type);
        $attachment_id = wp_insert_attachment([
            'post_author' => get_current_user_id(),
            'post_mime_type' => (string) ($uploaded['type'] ?? ''),
            'post_title' => sanitize_file_name($title_stub),
            'post_status' => 'inherit',
            'guid' => (string) ($uploaded['url'] ?? ''),
        ], (string) ($uploaded['file'] ?? ''));
        if (is_wp_error($attachment_id) || !$attachment_id) {
            return new WP_Error('cmn_attachment_failed', 'Unable to save uploaded file.');
        }
        $meta = wp_generate_attachment_metadata($attachment_id, (string) ($uploaded['file'] ?? ''));
        if (!is_wp_error($meta)) {
            wp_update_attachment_metadata($attachment_id, $meta);
        }
        if ($candidate_user_id > 0) {
            update_post_meta((int) $attachment_id, 'cmn_owner_user_id', $candidate_user_id);
        }
        update_post_meta((int) $attachment_id, 'cmn_doc_type', sanitize_key((string) $doc_type));
        update_post_meta((int) $attachment_id, 'cmn_candidate_id', $candidate_id);
        return [
            'attachment_id' => (int) $attachment_id,
            'url' => (string) wp_get_attachment_url((int) $attachment_id),
        ];
    }

    public function handle_generate_cv_converter_token() {
        if (!check_ajax_referer('cmn_staff_manage', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        if (!$this->user_can_manage_cv_converter($user_id)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $candidate_id = (int) ($_POST['candidate_id'] ?? 0);
        if (!$candidate_id || get_post_type($candidate_id) !== 'cmn_candidate') {
            wp_send_json_error(['message' => 'Candidate not found.'], 404);
        }
        if (!$this->user_can_view_candidate($candidate_id, $user_id)) {
            wp_send_json_error(['message' => 'Access restricted for this candidate.'], 403);
        }
        $token = $this->create_cv_converter_token($candidate_id, $user_id, 600);
        if ($token === '') {
            wp_send_json_error(['message' => 'Unable to create converter token.'], 500);
        }
        $converter_url = add_query_arg([
            'candidate_id' => $candidate_id,
            'portal_token' => $token,
        ], $this->get_cv_converter_base_url());
        $original_cv_url = add_query_arg([
            'action' => 'cmn_get_candidate_original_cv',
            'candidate_id' => $candidate_id,
            'portal_token' => $token,
            'download' => 1,
        ], admin_url('admin-ajax.php'));
        wp_send_json_success([
            'candidate_id' => $candidate_id,
            'token' => $token,
            'expires_in' => 600,
            'converter_url' => esc_url_raw($converter_url),
            'original_cv_url' => esc_url_raw($original_cv_url),
            'save_endpoint' => esc_url_raw(add_query_arg(['action' => 'cmn_save_candidate_formatted_cv'], admin_url('admin-ajax.php'))),
            'portal_ajax_url' => esc_url_raw(admin_url('admin-ajax.php')),
        ]);
    }

    public function handle_get_candidate_original_cv() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        if (!$this->user_can_manage_cv_converter($user_id)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $candidate_id = (int) ($_REQUEST['candidate_id'] ?? 0);
        if (!$candidate_id || get_post_type($candidate_id) !== 'cmn_candidate') {
            wp_send_json_error(['message' => 'Candidate not found.'], 404);
        }
        if (!$this->user_can_view_candidate($candidate_id, $user_id)) {
            wp_send_json_error(['message' => 'Access restricted for this candidate.'], 403);
        }
        $token = sanitize_text_field((string) ($_REQUEST['portal_token'] ?? ''));
        $token_check = $this->validate_cv_converter_token($token, $candidate_id, $user_id);
        if (is_wp_error($token_check)) {
            wp_send_json_error(['message' => $token_check->get_error_message()], 403);
        }
        $candidate_user_id = (int) $this->get_candidate_user_id($candidate_id);
        $attachment_id = $this->get_candidate_cv_original_attachment_id($candidate_id, $candidate_user_id);
        if ($attachment_id < 1) {
            wp_send_json_error(['message' => 'Original CV is not uploaded yet.'], 404);
        }
        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            wp_send_json_error(['message' => 'Original CV file not found.'], 404);
        }
        $as_meta = isset($_REQUEST['meta']) && (string) $_REQUEST['meta'] === '1';
        $filename = basename((string) $file);
        if ($as_meta) {
            wp_send_json_success([
                'candidate_id' => $candidate_id,
                'filename' => $filename,
                'mime' => (string) get_post_mime_type($attachment_id),
                'filesize' => filesize($file),
            ]);
        }
        nocache_headers();
        header('Content-Type: ' . (string) (get_post_mime_type($attachment_id) ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($filename) . '"');
        header('Content-Length: ' . (string) filesize($file));
        header('X-Content-Type-Options: nosniff');
        readfile($file);
        exit;
    }

    public function handle_save_candidate_formatted_cv() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        if (!$this->user_can_manage_cv_converter($user_id)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $candidate_id = (int) ($_POST['candidate_id'] ?? 0);
        if (!$candidate_id || get_post_type($candidate_id) !== 'cmn_candidate') {
            wp_send_json_error(['message' => 'Candidate not found.'], 404);
        }
        if (!$this->user_can_view_candidate($candidate_id, $user_id)) {
            wp_send_json_error(['message' => 'Access restricted for this candidate.'], 403);
        }
        $token = sanitize_text_field((string) ($_POST['portal_token'] ?? ''));
        $token_check = $this->validate_cv_converter_token($token, $candidate_id, $user_id);
        if (is_wp_error($token_check)) {
            wp_send_json_error(['message' => $token_check->get_error_message()], 403);
        }
        $candidate_user_id = (int) $this->get_candidate_user_id($candidate_id);
        if ($candidate_user_id < 1) {
            wp_send_json_error(['message' => 'Candidate account is not linked to a user.'], 400);
        }
        if (empty($_FILES['pdf_file']) || !is_array($_FILES['pdf_file'])) {
            wp_send_json_error(['message' => 'A formatted PDF file is required.'], 400);
        }
        $pdf_upload = $this->create_candidate_cv_attachment_from_upload(
            'pdf_file',
            $candidate_id,
            $candidate_user_id,
            'cv_formatted',
            ['pdf' => 'application/pdf'],
            'candidate-' . $candidate_id . '-formatted-cv'
        );
        if (is_wp_error($pdf_upload)) {
            wp_send_json_error(['message' => $pdf_upload->get_error_message()], 400);
        }
        $html_attachment_id = 0;
        if (!empty($_FILES['html_file']) && is_array($_FILES['html_file']) && (int) ($_FILES['html_file']['size'] ?? 0) > 0) {
            $html_upload = $this->create_candidate_cv_attachment_from_upload(
                'html_file',
                $candidate_id,
                $candidate_user_id,
                'cv_formatted_html',
                ['html' => 'text/html', 'htm' => 'text/html'],
                'candidate-' . $candidate_id . '-formatted-cv-html'
            );
            if (is_wp_error($html_upload)) {
                wp_send_json_error(['message' => $html_upload->get_error_message()], 400);
            }
            $html_attachment_id = (int) ($html_upload['attachment_id'] ?? 0);
        }
        $formatted_version = sanitize_text_field((string) ($_POST['formatted_version'] ?? self::VERSION));
        if ($formatted_version === '') {
            $formatted_version = self::VERSION;
        }
        $generated_at = current_time('mysql');
        update_post_meta($candidate_id, 'cmn_cv_formatted_attachment_id', (int) ($pdf_upload['attachment_id'] ?? 0));
        update_post_meta($candidate_id, 'cmn_cv_formatted_generated_at', $generated_at);
        update_post_meta($candidate_id, 'cmn_cv_formatted_version', $formatted_version);
        update_post_meta($candidate_id, 'cmn_cv_formatted_generated_by', $user_id);
        update_post_meta($candidate_id, 'cmn_cv_formatted_outdated', '0');
        if ($html_attachment_id > 0) {
            update_post_meta($candidate_id, 'cmn_cv_formatted_html_attachment_id', $html_attachment_id);
        } else {
            delete_post_meta($candidate_id, 'cmn_cv_formatted_html_attachment_id');
        }
        $actor = wp_get_current_user();
        $this->add_candidate_cv_audit_entry(
            $candidate_id,
            'Formatted CV saved to profile',
            'Formatted CV saved by ' . ($actor && $actor->display_name ? $actor->display_name : ('User #' . $user_id)) . ' at ' . $generated_at . '.',
            $user_id
        );
        $profile_url = add_query_arg([
            'view' => 'candidates',
            'candidate_id' => $candidate_id,
        ], $this->get_portal_base_url());
        wp_send_json_success([
            'candidate_id' => $candidate_id,
            'formatted_attachment_id' => (int) ($pdf_upload['attachment_id'] ?? 0),
            'formatted_html_attachment_id' => $html_attachment_id,
            'generated_at' => $generated_at,
            'formatted_version' => $formatted_version,
            'profile_url' => esc_url_raw($profile_url),
            'message' => 'Formatted CV placed on candidate profile.',
        ]);
    }

    public function handle_candidate_learning_opt_in() {
        if (!check_ajax_referer('cmn_candidate_learning', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $enabled = !empty($_POST['enabled']) && (string) $_POST['enabled'] !== '0';
        $user_id = get_current_user_id();
        update_user_meta($user_id, 'cmn_learning_notify_opt_in', $enabled ? '1' : '0');
        if ($enabled) {
            update_user_meta($user_id, 'cmn_learning_notify_opt_in_date', current_time('mysql'));
        } else {
            delete_user_meta($user_id, 'cmn_learning_notify_opt_in_date');
        }
        wp_send_json_success([
            'enabled' => $enabled ? 1 : 0,
            'message' => $enabled ? 'You will be notified when courses go live.' : 'Learning notifications turned off.',
        ]);
    }

    public function handle_candidate_update_profile() {
        if (!check_ajax_referer('cmn_candidate_profile', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        $candidate_id = $this->get_candidate_id_for_user($user_id);
        if (!$candidate_id) {
            wp_send_json_error(['message' => 'Candidate profile not found.'], 404);
        }

        $first_name = sanitize_text_field((string) ($_POST['first_name'] ?? ''));
        $last_name = sanitize_text_field((string) ($_POST['last_name'] ?? ''));
        $phone = sanitize_text_field((string) ($_POST['phone'] ?? ''));
        $role_type = sanitize_text_field((string) ($_POST['role_type'] ?? ''));
        $travel_radius = sanitize_text_field((string) ($_POST['travel_radius'] ?? ''));

        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);
        update_user_meta($user_id, 'phone', $phone);
        update_user_meta($user_id, 'role_type', $role_type);
        update_user_meta($user_id, 'travel_radius', $travel_radius);

        wp_update_user([
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => trim($first_name . ' ' . $last_name) ?: wp_get_current_user()->display_name,
        ]);

        if ($phone !== '') {
            update_post_meta($candidate_id, 'cmn_phone', $phone);
        } else {
            delete_post_meta($candidate_id, 'cmn_phone');
        }
        if ($travel_radius !== '') {
            update_post_meta($candidate_id, 'cmn_travel_distance', $travel_radius);
        } else {
            delete_post_meta($candidate_id, 'cmn_travel_distance');
        }
        if ($role_type !== '') {
            update_post_meta($candidate_id, 'cmn_roles', [$role_type]);
        }

        $completion = $this->update_candidate_profile_completion($candidate_id, $user_id);
        wp_send_json_success([
            'profile' => [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'full_name' => trim($first_name . ' ' . $last_name),
                'email' => (string) get_post_meta($candidate_id, 'cmn_email', true),
                'phone' => $phone,
                'role_type' => $role_type,
                'travel_radius' => $travel_radius,
            ],
            'completion' => $completion,
            'message' => 'Profile updated.',
        ]);
    }

    public function handle_candidate_upload_doc() {
        if (!check_ajax_referer('cmn_candidate_doc', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $doc_type = sanitize_key((string) ($_POST['doc_type'] ?? ''));
        $keys = $this->get_candidate_doc_meta_keys($doc_type);
        if (!$keys) {
            wp_send_json_error(['message' => 'Invalid document type.'], 400);
        }
        if (empty($_FILES['cmn_doc_file']) || !is_array($_FILES['cmn_doc_file'])) {
            wp_send_json_error(['message' => 'No file selected.'], 400);
        }
        $file = $_FILES['cmn_doc_file'];
        $allowed = $this->get_candidate_doc_allowed_mimes($doc_type);
        $target_limit = (int) $this->get_candidate_doc_target_limit_bytes();
        $effective_limit = (int) wp_max_upload_size();
        $max_file_size = max(1, min($target_limit, $effective_limit > 0 ? $effective_limit : $target_limit));
        if (!empty($file['size']) && (int) $file['size'] > $max_file_size) {
            $message = 'File is too large. Maximum supported upload is ' . size_format($max_file_size) . '.';
            if ($effective_limit > 0 && $effective_limit < $target_limit) {
                $message .= ' Hosting limit is currently lower than the 20MB target.';
            }
            wp_send_json_error(['message' => $message], 400);
        }
        $ext = strtolower((string) pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($ext === '' || !isset($allowed[$ext])) {
            wp_send_json_error(['message' => 'File type not allowed.'], 400);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $overrides = [
            'test_form' => false,
            'mimes' => $allowed,
        ];
        $uploaded = wp_handle_upload($_FILES['cmn_doc_file'], $overrides);
        if (!empty($uploaded['error'])) {
            wp_send_json_error(['message' => sanitize_text_field((string) $uploaded['error'])], 500);
        }
        $attachment_id = wp_insert_attachment([
            'post_mime_type' => (string) ($uploaded['type'] ?? ''),
            'post_title' => sanitize_file_name(pathinfo((string) $file['name'], PATHINFO_FILENAME)),
            'post_status' => 'inherit',
            'guid' => (string) ($uploaded['url'] ?? ''),
        ], (string) ($uploaded['file'] ?? ''));
        if (is_wp_error($attachment_id) || !$attachment_id) {
            wp_send_json_error(['message' => 'Unable to save uploaded file.'], 500);
        }
        $meta = wp_generate_attachment_metadata($attachment_id, (string) ($uploaded['file'] ?? ''));
        if (!is_wp_error($meta)) {
            wp_update_attachment_metadata($attachment_id, $meta);
        }

        $user_id = get_current_user_id();
        $candidate_id = $this->get_candidate_id_for_user($user_id);
        if (!$candidate_id) {
            wp_delete_attachment((int) $attachment_id, true);
            wp_send_json_error(['message' => 'Candidate profile not found.'], 404);
        }
        $uploaded_at = gmdate('Y-m-d H:i:s');
        update_user_meta($user_id, $keys['attachment'], (int) $attachment_id);
        update_user_meta($user_id, $keys['uploaded_at'], $uploaded_at);
        if (!empty($keys['filename'])) {
            update_user_meta($user_id, $keys['filename'], sanitize_file_name((string) ($file['name'] ?? '')));
        }
        if (!empty($keys['filesize'])) {
            update_user_meta($user_id, $keys['filesize'], (string) (int) ($file['size'] ?? 0));
        }
        if (!empty($keys['mime'])) {
            update_user_meta($user_id, $keys['mime'], sanitize_text_field((string) ($uploaded['type'] ?? '')));
        }
        if (!empty($keys['review_status'])) {
            update_user_meta($user_id, $keys['review_status'], 'pending');
        }
        if (!empty($keys['review_reason'])) {
            delete_user_meta($user_id, $keys['review_reason']);
        }
        if (!empty($keys['reviewed_at'])) {
            delete_user_meta($user_id, $keys['reviewed_at']);
        }
        if (!empty($keys['reviewed_by'])) {
            delete_user_meta($user_id, $keys['reviewed_by']);
        }
        if (!empty($keys['legacy_attachment'])) {
            update_user_meta($user_id, $keys['legacy_attachment'], (int) $attachment_id);
        }
        if (!empty($keys['legacy_uploaded_at'])) {
            update_user_meta($user_id, $keys['legacy_uploaded_at'], $uploaded_at);
        }
        update_post_meta((int) $attachment_id, 'cmn_owner_user_id', (int) $user_id);
        update_post_meta((int) $attachment_id, 'cmn_doc_type', $doc_type);
        update_post_meta((int) $attachment_id, 'cmn_candidate_id', (int) $candidate_id);
        $url = wp_get_attachment_url((int) $attachment_id);
        if ($url) {
            update_post_meta($candidate_id, $keys['post_meta'], esc_url_raw($url));
        }
        if ($doc_type === 'cv') {
            update_post_meta($candidate_id, 'cmn_cv_original_attachment_id', (int) $attachment_id);
            update_post_meta($candidate_id, 'cmn_cv_original_uploaded_at', $uploaded_at);
            $this->add_candidate_cv_audit_entry(
                $candidate_id,
                'Candidate CV upload',
                'Candidate uploaded original CV at ' . $uploaded_at . '.',
                $user_id
            );
            $this->mark_candidate_formatted_cv_outdated($candidate_id, true);
        }
        $completion = $this->update_candidate_profile_completion($candidate_id, $user_id);
        $target_user_id = (int) $this->get_candidate_user_id($candidate_id);
        if (!$target_user_id) {
            $target_user_id = $user_id;
        }
        $status = $this->get_candidate_doc_status($candidate_id, $target_user_id, $doc_type);
        $docs_for_sync = [];
        foreach ($this->get_candidate_doc_types() as $sync_doc_type) {
            $docs_for_sync[$sync_doc_type] = $sync_doc_type === $doc_type
                ? $status
                : $this->get_candidate_doc_status($candidate_id, $target_user_id, $sync_doc_type);
        }
        $verification_status = $this->sync_candidate_admin_verification_status($candidate_id, $target_user_id, $docs_for_sync);
        $compliance = $this->get_candidate_compliance_payload($candidate_id, $target_user_id);
        wp_send_json_success([
            'doc_type' => $doc_type,
            'status' => $status,
            'completion' => $completion,
            'verification_status' => $verification_status,
            'compliance' => $compliance,
        ]);
    }

    public function handle_candidate_delete_doc() {
        if (!check_ajax_referer('cmn_candidate_doc', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $doc_type = sanitize_key((string) ($_POST['doc_type'] ?? ''));
        $keys = $this->get_candidate_doc_meta_keys($doc_type);
        if (!$keys) {
            wp_send_json_error(['message' => 'Invalid document type.'], 400);
        }
        $user_id = get_current_user_id();
        $candidate_id = $this->get_candidate_id_for_user($user_id);
        if (!$candidate_id) {
            wp_send_json_error(['message' => 'Candidate profile not found.'], 404);
        }
        $attachment_id = $this->get_candidate_doc_attachment_id($user_id, $doc_type);
        delete_user_meta($user_id, $keys['attachment']);
        delete_user_meta($user_id, $keys['uploaded_at']);
        if (!empty($keys['filename'])) {
            delete_user_meta($user_id, $keys['filename']);
        }
        if (!empty($keys['filesize'])) {
            delete_user_meta($user_id, $keys['filesize']);
        }
        if (!empty($keys['mime'])) {
            delete_user_meta($user_id, $keys['mime']);
        }
        if (!empty($keys['review_status'])) {
            delete_user_meta($user_id, $keys['review_status']);
        }
        if (!empty($keys['review_reason'])) {
            delete_user_meta($user_id, $keys['review_reason']);
        }
        if (!empty($keys['reviewed_at'])) {
            delete_user_meta($user_id, $keys['reviewed_at']);
        }
        if (!empty($keys['reviewed_by'])) {
            delete_user_meta($user_id, $keys['reviewed_by']);
        }
        if (!empty($keys['legacy_attachment'])) {
            delete_user_meta($user_id, $keys['legacy_attachment']);
        }
        if (!empty($keys['legacy_uploaded_at'])) {
            delete_user_meta($user_id, $keys['legacy_uploaded_at']);
        }
        delete_post_meta($candidate_id, $keys['post_meta']);
        if ($doc_type === 'cv') {
            delete_post_meta($candidate_id, 'cmn_cv_original_attachment_id');
            delete_post_meta($candidate_id, 'cmn_cv_original_uploaded_at');
            $this->mark_candidate_formatted_cv_outdated($candidate_id, false);
            $this->add_candidate_cv_audit_entry(
                $candidate_id,
                'Candidate CV removed',
                'Candidate removed original CV from profile.',
                $user_id
            );
        }
        if ($attachment_id && !$this->is_candidate_doc_attachment_still_linked($attachment_id)) {
            wp_delete_attachment((int) $attachment_id, true);
        }
        $completion = $this->update_candidate_profile_completion($candidate_id, $user_id);
        $status = $this->get_candidate_doc_status($candidate_id, $user_id, $doc_type);
        $docs_for_sync = [];
        foreach ($this->get_candidate_doc_types() as $sync_doc_type) {
            $docs_for_sync[$sync_doc_type] = $sync_doc_type === $doc_type
                ? $status
                : $this->get_candidate_doc_status($candidate_id, $user_id, $sync_doc_type);
        }
        $verification_status = $this->sync_candidate_admin_verification_status($candidate_id, $user_id, $docs_for_sync);
        $compliance = $this->get_candidate_compliance_payload($candidate_id, $user_id);
        wp_send_json_success([
            'doc_type' => $doc_type,
            'status' => $status,
            'completion' => $completion,
            'verification_status' => $verification_status,
            'compliance' => $compliance,
        ]);
    }

    public function handle_candidate_get_doc() {
        if (!check_ajax_referer('cmn_candidate_doc', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $doc_type = sanitize_key((string) ($_POST['doc_type'] ?? ''));
        $keys = $this->get_candidate_doc_meta_keys($doc_type);
        if (!$keys) {
            wp_send_json_error(['message' => 'Invalid document type.'], 400);
        }
        $user_id = get_current_user_id();
        $candidate_id = (int) ($_POST['candidate_id'] ?? 0);
        if (!$candidate_id) {
            $candidate_id = $this->get_candidate_id_for_user($user_id);
        }
        if (!$candidate_id) {
            wp_send_json_error(['message' => 'Candidate profile not found.'], 404);
        }
        $candidate_owner_user_id = (int) $this->get_candidate_user_id($candidate_id);
        if (!$candidate_owner_user_id) {
            wp_send_json_error(['message' => 'Candidate owner not found.'], 404);
        }
        if ($this->is_school_user($user_id)) {
            if ($doc_type !== 'cv') {
                wp_send_json_error(['message' => 'Document unavailable.'], 403);
            }
            if (!$this->cmn_school_can_access_candidate_docs($user_id, $candidate_owner_user_id)) {
                wp_send_json_error(['message' => 'Unauthorized.'], 403);
            }
            $formatted = $this->get_candidate_cv_formatted_status($candidate_id);
            if (empty($formatted['available'])) {
                wp_send_json_error(['message' => 'CV is being prepared by our team'], 404);
            }
            $download_url = add_query_arg([
                'action' => 'cmn_candidate_download_doc',
                'candidate_id' => $candidate_id,
                'doc_type' => 'cv',
                'cmn_nonce' => wp_create_nonce('cmn_candidate_download_doc_' . $candidate_id . '_cv_' . $user_id),
            ], admin_url('admin-post.php'));
            wp_send_json_success([
                'doc_type' => 'cv',
                'download_url' => esc_url_raw($download_url),
                'status' => [
                    'uploaded' => true,
                    'doc_status' => 'approved',
                    'status_label' => 'Formatted CV',
                    'filename' => (string) ($formatted['filename'] ?? ''),
                    'uploaded_at' => (string) ($formatted['generated_at'] ?? ''),
                    'uploaded_at_label' => (string) ($formatted['generated_at_label'] ?? ''),
                    'filesize_label' => (string) ($formatted['filesize_label'] ?? ''),
                    'attachment_id' => (int) ($formatted['attachment_id'] ?? 0),
                ],
            ]);
        }
        $status = $this->get_candidate_doc_status($candidate_id, $candidate_owner_user_id, $doc_type);
        if (empty($status['uploaded']) || empty($status['url']) || empty($status['attachment_id'])) {
            wp_send_json_error(['message' => 'Document not uploaded yet.'], 404);
        }
        if (!$this->cmn_candidate_can_access_doc($user_id, $candidate_owner_user_id, (int) $status['attachment_id'])) {
            $message = $this->is_school_user($user_id) ? 'Document not yet verified.' : 'Unauthorized.';
            wp_send_json_error(['message' => $message], 403);
        }
        $download_url = add_query_arg([
            'action' => 'cmn_candidate_download_doc',
            'candidate_id' => $candidate_id,
            'doc_type' => $doc_type,
            'cmn_nonce' => wp_create_nonce('cmn_candidate_download_doc_' . $candidate_id . '_' . $doc_type . '_' . $user_id),
        ], admin_url('admin-post.php'));
        wp_send_json_success([
            'doc_type' => $doc_type,
            'download_url' => esc_url_raw($download_url),
            'status' => $status,
        ]);
    }

    public function handle_get_compliance_status() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        if (!check_ajax_referer('cmn_candidate_doc', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }

        $requester_id = get_current_user_id();
        $candidate_id = (int) ($_POST['candidate_id'] ?? 0);
        if ($candidate_id < 1) {
            $candidate_id = (int) $this->get_candidate_id_for_user($requester_id);
        }
        if ($candidate_id < 1) {
            wp_send_json_error(['message' => 'Candidate not found.'], 404);
        }

        $candidate_user_id = (int) $this->get_candidate_user_id($candidate_id);
        if ($candidate_user_id < 1) {
            wp_send_json_error(['message' => 'Candidate account is not linked.'], 404);
        }

        $allowed = false;
        if ($requester_id === $candidate_user_id) {
            $allowed = true;
        } elseif ($this->is_admin_user($requester_id) || $this->is_staff_role($requester_id)) {
            $allowed = true;
        } elseif ($this->is_account_manager_user($requester_id) && $this->user_can_view_candidate($candidate_id, $requester_id)) {
            $allowed = true;
        }
        if (!$allowed) {
            wp_send_json_error(['message' => 'Access restricted.'], 403);
        }

        wp_send_json_success($this->get_candidate_compliance_payload($candidate_id, $candidate_user_id));
    }

    public function handle_candidate_download_doc() {
        if (!is_user_logged_in()) {
            wp_die('Unauthorized');
        }
        $candidate_id = (int) ($_GET['candidate_id'] ?? 0);
        $doc_type = sanitize_key((string) ($_GET['doc_type'] ?? ''));
        $nonce = sanitize_text_field((string) ($_GET['cmn_nonce'] ?? ''));
        $user_id = get_current_user_id();
        if (!$candidate_id || !$doc_type || !$nonce || !wp_verify_nonce($nonce, 'cmn_candidate_download_doc_' . $candidate_id . '_' . $doc_type . '_' . $user_id)) {
            wp_die('Invalid request');
        }
        $doc_user_id = (int) $this->get_candidate_user_id($candidate_id);
        if (!$doc_user_id) {
            wp_die('Document owner not found.');
        }
        $attachment_id = 0;
        if ($this->is_school_user($user_id)) {
            if ($doc_type !== 'cv') {
                wp_die('Document unavailable.');
            }
            if (!$this->cmn_school_can_access_candidate_docs($user_id, $doc_user_id)) {
                wp_die('Unauthorized');
            }
            $formatted = $this->get_candidate_cv_formatted_status($candidate_id);
            if (empty($formatted['available']) || empty($formatted['attachment_id'])) {
                wp_die('CV is being prepared by our team');
            }
            $attachment_id = (int) $formatted['attachment_id'];

            $school_id = (int) $this->resolve_school_id_for_user($user_id);
            $school_domain = $school_id ? (string) get_post_meta($school_id, 'cmn_school_email_domain', true) : '';
            if ($school_domain === '' && $school_id) {
                $school_email = (string) get_post_meta($school_id, 'cmn_email', true);
                $school_domain = $this->get_email_domain($school_email);
            }
            if ($school_domain !== '') {
                $this->insert_activity_row([
                    'entity_type' => 'school',
                    'entity_ref' => strtolower(trim($school_domain)),
                    'activity_type' => 'note',
                    'subject' => 'School downloaded formatted CV',
                    'notes' => ((string) get_the_title($candidate_id)) . ' formatted CV downloaded.',
                    'created_by' => $user_id,
                ]);
            }
            $this->add_candidate_cv_audit_entry(
                $candidate_id,
                'School downloaded formatted CV',
                'Formatted CV downloaded by school user #' . $user_id . '.',
                $user_id
            );
        } else {
            $status = $this->get_candidate_doc_status($candidate_id, $doc_user_id, $doc_type);
            if (empty($status['attachment_id'])) {
                wp_die('Document not found.');
            }
            if (!$this->cmn_candidate_can_access_doc($user_id, $doc_user_id, (int) $status['attachment_id'])) {
                wp_die('Unauthorized');
            }
            $attachment_id = (int) $status['attachment_id'];
        }
        if ($attachment_id < 1) {
            wp_die('Document not found.');
        }
        $file = get_attached_file($attachment_id);
        if (!$file || !file_exists($file)) {
            wp_die('Document not found.');
        }
        nocache_headers();
        header('Content-Description: File Transfer');
        header('Content-Type: ' . (string) get_post_mime_type($attachment_id));
        header('Content-Disposition: inline; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    public function handle_staff_review_candidate_doc() {
        if (!isset($_POST['cmn_staff_review_candidate_doc_nonce']) || !wp_verify_nonce($_POST['cmn_staff_review_candidate_doc_nonce'], 'cmn_staff_review_candidate_doc')) {
            wp_die('Invalid request');
        }
        if (!is_user_logged_in() || !$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        $candidate_id = (int) ($_POST['candidate_id'] ?? 0);
        $doc_type = sanitize_key((string) ($_POST['doc_type'] ?? ''));
        $review_action = sanitize_key((string) ($_POST['review_action'] ?? ''));
        $review_reason = sanitize_textarea_field((string) ($_POST['review_reason'] ?? ''));
        $portal_url = $this->get_portal_base_url();
        $redirect_url = add_query_arg([
            'view' => 'candidates',
            'candidate_id' => $candidate_id,
        ], $portal_url);
        $redirect_with_message = function ($message) use ($redirect_url) {
            wp_safe_redirect(add_query_arg(['cmn_doc_review_msg' => rawurlencode((string) $message)], $redirect_url));
            exit;
        };

        if (!$candidate_id || !$doc_type) {
            $redirect_with_message('Invalid candidate document request.');
        }
        if (!in_array($review_action, ['approved', 'rejected'], true)) {
            $redirect_with_message('Please choose approve or reject.');
        }
        $candidate_user_id = (int) $this->get_candidate_user_id($candidate_id);
        if (!$candidate_user_id) {
            $redirect_with_message('Candidate user not found.');
        }
        $keys = $this->get_candidate_doc_meta_keys($doc_type);
        if (!$keys) {
            $redirect_with_message('Invalid document type.');
        }
        $doc_status = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, $doc_type);
        if (empty($doc_status['uploaded']) || empty($doc_status['attachment_id'])) {
            $redirect_with_message('Document is not uploaded yet.');
        }

        if (!empty($keys['review_status'])) {
            update_user_meta($candidate_user_id, $keys['review_status'], $review_action);
        }
        if (!empty($keys['review_reason'])) {
            if ($review_action === 'rejected' && $review_reason !== '') {
                update_user_meta($candidate_user_id, $keys['review_reason'], $review_reason);
            } else {
                delete_user_meta($candidate_user_id, $keys['review_reason']);
            }
        }
        if (!empty($keys['reviewed_at'])) {
            update_user_meta($candidate_user_id, $keys['reviewed_at'], current_time('mysql'));
        }
        if (!empty($keys['reviewed_by'])) {
            update_user_meta($candidate_user_id, $keys['reviewed_by'], get_current_user_id());
        }

        $docs_for_sync = [];
        foreach ($this->get_candidate_doc_types() as $sync_doc_type) {
            $docs_for_sync[$sync_doc_type] = $this->get_candidate_doc_status($candidate_id, $candidate_user_id, $sync_doc_type);
        }
        $this->sync_candidate_admin_verification_status($candidate_id, $candidate_user_id, $docs_for_sync);

        $doc_label_map = [
            'dbs' => 'DBS',
            'id' => 'ID',
            'cv' => 'CV',
        ];
        $doc_label = $doc_label_map[$doc_type] ?? strtoupper($doc_type);
        $candidate_profile_url = add_query_arg(['candidate' => 'profile'], $portal_url);
        if ($review_action === 'approved') {
            $this->add_notification(
                $candidate_user_id,
                'candidate_doc_approved',
                'Document approved',
                'Your ' . $doc_label . ' has been approved.',
                $candidate_profile_url
            );
            $redirect_with_message($doc_label . ' approved.');
        }
        $message = 'Your ' . $doc_label . ' has been rejected.';
        if ($review_reason !== '') {
            $message .= ' Reason: ' . $review_reason;
        }
        $this->add_notification(
            $candidate_user_id,
            'candidate_doc_rejected',
            'Document rejected',
            $message,
            $candidate_profile_url
        );
        $redirect_with_message($doc_label . ' rejected.');
    }

    public function handle_ready_response_save() {
        if (!is_user_logged_in() || !$this->is_school_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_ready_response_nonce']) || !wp_verify_nonce($_POST['cmn_ready_response_nonce'], 'cmn_ready_response_manage')) {
            wp_die('Invalid request');
        }
        $school_user_id = get_current_user_id();
        $response_id = (int) ($_POST['cmn_ready_response_id'] ?? 0);
        $title = sanitize_text_field((string) ($_POST['cmn_ready_response_title'] ?? ''));
        $message_template = sanitize_textarea_field((string) ($_POST['cmn_ready_response_template'] ?? ''));
        $is_default = !empty($_POST['cmn_ready_response_is_default']) ? 1 : 0;
        $redirect_url = add_query_arg(['school' => 'settings'], $this->get_portal_base_url());
        if ($title === '' || $message_template === '') {
            wp_redirect(add_query_arg(['cmn_ready_response_msg' => rawurlencode('Title and message are required.')], $redirect_url));
            exit;
        }

        global $wpdb;
        $table = $this->get_ready_responses_table();
        $now = current_time('mysql');
        $target_id = 0;
        if ($response_id > 0) {
            $existing = $this->get_school_ready_response_by_id($response_id, $school_user_id);
            if ($existing) {
                $wpdb->update($table, [
                    'title' => $title,
                    'is_default' => $is_default,
                    'message_template' => $message_template,
                    'updated_at' => $now,
                ], [
                    'id' => $response_id,
                    'school_user_id' => $school_user_id,
                ], ['%s', '%d', '%s', '%s'], ['%d', '%d']);
                $target_id = $response_id;
            }
        } else {
            $wpdb->insert($table, [
                'school_user_id' => $school_user_id,
                'title' => $title,
                'is_default' => $is_default,
                'message_template' => $message_template,
                'created_at' => $now,
                'updated_at' => $now,
            ], ['%d', '%s', '%d', '%s', '%s', '%s']);
            $target_id = (int) $wpdb->insert_id;
        }

        if ($target_id > 0) {
            if ($is_default) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$table} SET is_default = 0 WHERE school_user_id = %d AND id <> %d",
                    $school_user_id,
                    $target_id
                ));
            } else {
                $default_exists = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(1) FROM {$table} WHERE school_user_id = %d AND is_default = 1",
                    $school_user_id
                ));
                if ($default_exists < 1) {
                    $wpdb->update($table, ['is_default' => 1], ['id' => $target_id], ['%d'], ['%d']);
                }
            }
            wp_redirect(add_query_arg(['cmn_ready_response_msg' => rawurlencode('Template saved.')], $redirect_url));
            exit;
        }

        wp_redirect(add_query_arg(['cmn_ready_response_msg' => rawurlencode('Unable to save template.')], $redirect_url));
        exit;
    }

    public function handle_ready_response_delete() {
        if (!is_user_logged_in() || !$this->is_school_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_ready_response_delete_nonce']) || !wp_verify_nonce($_POST['cmn_ready_response_delete_nonce'], 'cmn_ready_response_delete')) {
            wp_die('Invalid request');
        }
        $school_user_id = get_current_user_id();
        $response_id = (int) ($_POST['cmn_ready_response_id'] ?? 0);
        $redirect_url = add_query_arg(['school' => 'settings'], $this->get_portal_base_url());
        if (!$response_id) {
            wp_redirect(add_query_arg(['cmn_ready_response_msg' => rawurlencode('Template not found.')], $redirect_url));
            exit;
        }
        global $wpdb;
        $table = $this->get_ready_responses_table();
        $existing = $this->get_school_ready_response_by_id($response_id, $school_user_id);
        if (!$existing) {
            wp_redirect(add_query_arg(['cmn_ready_response_msg' => rawurlencode('Template not found.')], $redirect_url));
            exit;
        }
        $was_default = (int) ($existing['is_default'] ?? 0) === 1;
        $wpdb->delete($table, [
            'id' => $response_id,
            'school_user_id' => $school_user_id,
        ], ['%d', '%d']);
        if ($was_default) {
            $next_id = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table} WHERE school_user_id = %d ORDER BY updated_at DESC, id DESC LIMIT 1",
                $school_user_id
            ));
            if ($next_id > 0) {
                $wpdb->update($table, ['is_default' => 1], ['id' => $next_id], ['%d'], ['%d']);
            }
        }
        wp_redirect(add_query_arg(['cmn_ready_response_msg' => rawurlencode('Template deleted.')], $redirect_url));
        exit;
    }

    public function handle_request_candidate() {
        if (!check_ajax_referer('cmn_request_candidate', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_school_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }

        $school_id = $this->resolve_school_id_for_user();
        if (!$school_id) {
            wp_send_json_error(['message' => 'School profile not found.'], 404);
        }
        $status = get_post_meta($school_id, 'cmn_status', true);
        if ($status !== 'client') {
            wp_send_json_error(['message' => 'School access is pending approval.'], 403);
        }

        $candidate_id = intval($_POST['candidate_id'] ?? 0);
        if (!$candidate_id) {
            wp_send_json_error(['message' => 'Missing candidate.'], 400);
        }
        $candidate = get_post($candidate_id);
        if (!$candidate || $candidate->post_type !== 'cmn_candidate') {
            wp_send_json_error(['message' => 'Candidate not found.'], 404);
        }
        $candidate_status = get_post_meta($candidate_id, 'cmn_status', true);
        if ($candidate_status && $candidate_status !== 'approved') {
            wp_send_json_error(['message' => 'Candidate is not available.'], 400);
        }

        $target_date = $this->get_tomorrow_date();
        if ($this->is_candidate_unavailable($candidate_id, $target_date)) {
            wp_send_json_error(['message' => 'Candidate is marked unavailable for tomorrow.'], 400);
        }
        if (!$this->has_candidate_availability($candidate_id, $target_date)) {
            wp_send_json_error(['message' => 'Candidate is not marked available for tomorrow.'], 400);
        }

        $school_domain = get_post_meta($school_id, 'cmn_school_email_domain', true);
        if (!$school_domain) {
            $school_email = get_post_meta($school_id, 'cmn_email', true);
            $school_domain = $this->get_email_domain($school_email);
        }
        if (!$school_domain) {
            wp_send_json_error(['message' => 'School domain missing.'], 400);
        }

        global $wpdb;
        $table = $this->get_candidate_requests_table();
        $request_sent_at = current_time('mysql');
        $expires_at = gmdate('Y-m-d H:i:s', strtotime(gmdate('Y-m-d H:i:s') . ' +15 minutes'));
        $account_manager_user_id = $this->get_request_account_manager_user_id($school_id);
        $ready_response_id = $this->normalize_ready_response_selection_for_request($_POST['ready_response_id'] ?? '', get_current_user_id());
        $candidate_pay_rate = $this->get_request_candidate_pay_rate($candidate_id, $school_id);
        $school_charge_rate = $this->get_request_school_charge_rate($candidate_pay_rate);
        $inserted = $wpdb->insert($table, [
            'school_id' => $school_id,
            'school_email_domain' => $school_domain,
            'school_user_id' => get_current_user_id(),
            'candidate_id' => $candidate_id,
            'account_manager_user_id' => $account_manager_user_id ?: null,
            'ready_response_id' => $ready_response_id,
            'requested_date' => $target_date,
            'status' => 'requested',
            'request_sent_at' => $request_sent_at,
            'expires_at' => $expires_at,
            'candidate_pay_rate' => $candidate_pay_rate,
            'school_charge_rate' => $school_charge_rate,
            'requested_at' => $request_sent_at,
            'updated_at' => $request_sent_at,
        ], ['%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s']);

        if (!$inserted) {
            wp_send_json_success([
                'message' => 'Your request has already been sent.',
                'already' => true,
            ]);
        }

        $school_name = get_the_title($school_id);
        $candidate_name = $candidate ? $candidate->post_title : 'Candidate';
        $note = 'School requested ' . $candidate_name . ' for ' . $target_date . ' (morning).';
        $this->insert_activity_row([
            'entity_type' => 'school',
            'entity_ref' => $school_domain,
            'activity_type' => 'note',
            'subject' => 'School requested candidate',
            'notes' => $note,
            'created_by' => get_current_user_id(),
        ]);
        $this->insert_activity_row([
            'entity_type' => 'contact',
            'entity_ref' => (string) $candidate_id,
            'activity_type' => 'note',
            'subject' => 'School requested candidate',
            'notes' => ($school_name ? $school_name . ' requested availability.' : $note),
            'created_by' => get_current_user_id(),
        ]);
        $assigned_manager = (int) get_post_meta($school_id, 'cmn_account_manager_user', true);
        $this->insert_activity_row([
            'entity_type' => 'school',
            'entity_ref' => $school_domain,
            'activity_type' => 'task',
            'subject' => 'Confirm candidate availability',
            'notes' => $candidate_name . ' · ' . $target_date,
            'due_date' => $target_date,
            'assigned_to_user_id' => $assigned_manager ?: null,
            'assigned_to_school_domain' => $school_domain,
            'created_by' => get_current_user_id(),
        ]);

        $request_id = (int) $wpdb->insert_id;
        $staff_link = add_query_arg(['view' => 'requests'], $this->get_portal_base_url());
        $candidate_link = add_query_arg(['candidate' => 'bookings'], $this->get_portal_base_url());
        $candidate_user_id = $this->get_candidate_user_id($candidate_id);

        if ($account_manager_user_id) {
            $this->add_notification(
                $account_manager_user_id,
                'booking_request_new',
                'New booking request',
                $school_name . ' requested ' . $candidate_name . ' for ' . date_i18n('M j, Y', strtotime($target_date)),
                $staff_link
            );
        }
        foreach ($this->get_admin_users_for_support() as $admin_id) {
            if ($account_manager_user_id && (int) $admin_id === (int) $account_manager_user_id) {
                continue;
            }
            $this->add_notification(
                $admin_id,
                'booking_request_new',
                'New booking request',
                $school_name . ' requested ' . $candidate_name . ' for ' . date_i18n('M j, Y', strtotime($target_date)),
                $staff_link
            );
        }
        if ($candidate_user_id) {
            $this->add_notification(
                $candidate_user_id,
                'booking_request',
                'Booking request',
                'You have a booking request for ' . date_i18n('M j, Y', strtotime($target_date)),
                $candidate_link
            );
        }
        $candidate_email = sanitize_email(get_post_meta($candidate_id, 'cmn_email', true));
        if ($candidate_email) {
            $subject = 'You have a booking request';
            $message = "You have a booking request for " . date_i18n('l, F jS', strtotime($target_date)) . ".\n\nPlease log into your portal to respond:\n" . $candidate_link;
            $this->send_candidate_email($candidate_email, $subject, $message, [
                'type' => 'candidate_availability_request',
                'related_candidate_id' => $candidate_id,
                'related_request_id' => $request_id,
            ]);
        }

        wp_send_json_success([
            'message' => 'Your request has been sent. We’ll confirm availability shortly.',
            'date' => $target_date,
            'request_id' => $request_id,
        ]);
    }

    public function handle_candidate_request_action() {
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_candidate_request_action_nonce']) || !wp_verify_nonce($_POST['cmn_candidate_request_action_nonce'], 'cmn_candidate_request_action')) {
            wp_die('Invalid request');
        }
        $request_id = (int) ($_POST['cmn_request_id'] ?? 0);
        $action = sanitize_key((string) ($_POST['cmn_request_action'] ?? ''));
        $request = $this->get_candidate_request_by_id($request_id);
        if (!$request) {
            wp_die('Request not found');
        }
        $candidate_id = $this->get_candidate_id_for_user();
        if (!$candidate_id || (int) $request['candidate_id'] !== (int) $candidate_id) {
            wp_die('Unauthorized');
        }
        $this->maybe_mark_request_expired($request);
        $school_id = (int) ($request['school_id'] ?? 0);
        if (!$school_id) {
            $school_id = $this->get_school_post_id_by_domain($request['school_email_domain'] ?? '');
        }
        $am_user_id = $this->get_request_account_manager_user_id($school_id, $request);
        $portal_url = $this->get_portal_base_url();
        $candidate_bookings_url = add_query_arg(['candidate' => 'bookings'], $portal_url);
        $staff_requests_url = add_query_arg(['view' => 'requests'], $portal_url);
        $school_requests_url = add_query_arg(['school' => 'requests'], $portal_url);
        global $wpdb;
        $table = $this->get_candidate_requests_table();
        $now = current_time('mysql');
        $current_status = strtolower((string) ($request['status'] ?? 'requested'));

        if ($action === 'still_needed') {
            if ($current_status === 'requested') {
                $this->maybe_mark_request_expired($request);
                $current_status = strtolower((string) ($request['status'] ?? 'expired'));
            }
            if ($current_status !== 'expired') {
                wp_redirect(add_query_arg(['candidate' => 'bookings', 'cmn_notice' => rawurlencode('This request is still active.')], $this->get_portal_base_url()));
                exit;
            }
            if ($am_user_id) {
                $this->add_notification(
                    $am_user_id,
                    'booking_request_expired_ping',
                    'Expired booking - candidate asking if still needed',
                    get_the_title($candidate_id) . " clicked 'Is this still needed?'",
                    $staff_requests_url
                );
            }
            foreach ($this->get_admin_users_for_support() as $admin_id) {
                if ($am_user_id && (int) $admin_id === (int) $am_user_id) {
                    continue;
                }
                $this->add_notification(
                    (int) $admin_id,
                    'booking_request_expired_ping',
                    'Expired booking - candidate asking if still needed',
                    get_the_title($candidate_id) . " clicked 'Is this still needed?'",
                    $staff_requests_url
                );
            }
            wp_redirect(add_query_arg(['candidate' => 'bookings', 'cmn_notice' => rawurlencode('We’re checking with the school. We’ll update you.')], $this->get_portal_base_url()));
            exit;
        }

        if (!in_array($current_status, ['requested'], true)) {
            wp_redirect(add_query_arg(['candidate' => 'bookings', 'cmn_notice' => rawurlencode('This request can no longer be updated.')], $this->get_portal_base_url()));
            exit;
        }
        if ($this->is_request_expired($request)) {
            $this->maybe_mark_request_expired($request);
            wp_redirect(add_query_arg(['candidate' => 'bookings', 'cmn_notice' => rawurlencode('This request has expired.')], $this->get_portal_base_url()));
            exit;
        }

        if ($action === 'negotiate') {
            $booking_id = (int) get_post_meta($request_id, 'cmn_booking_id', true);
            if (!$booking_id) {
                $booking_id = $this->create_booking_from_request($request, $school_id, get_current_user_id());
                if ($booking_id) {
                    update_post_meta($booking_id, 'cmn_status', 'requested');
                    update_post_meta($booking_id, 'cmn_request_id', $request_id);
                    update_post_meta($request_id, 'cmn_booking_id', $booking_id);
                }
            }
            if ($booking_id) {
                $candidate_user_id = get_current_user_id();
                $school_user_id = $this->get_school_user_id_for_request($request, $school_id);
                $thread_id = $this->create_or_get_booking_thread($booking_id, 'pay_negotiation', [
                    'candidate_user_id' => $candidate_user_id,
                    'school_user_id' => $school_user_id,
                    'account_manager_user_id' => $am_user_id,
                    'status' => 'active',
                ]);
                if ($thread_id) {
                    $this->add_booking_thread_participant($thread_id, $candidate_user_id, 'candidate');
                    if ($am_user_id) {
                        $this->add_booking_thread_participant($thread_id, $am_user_id, 'account_manager');
                    }
                    $existing_messages = $this->get_booking_thread_messages($thread_id);
                    if (!$existing_messages) {
                        $this->add_booking_thread_message($thread_id, $am_user_id, 'account_manager', 'Pay negotiation only. This chat is between you and the account manager.');
                    }
                    update_post_meta($booking_id, 'cmn_active_thread', 'pay_negotiation');
                }
                $candidate_name = get_the_title($candidate_id);
                $requested_label = !empty($request['requested_date']) ? date_i18n('M j, Y', strtotime($request['requested_date'])) : 'tomorrow';
                if ($am_user_id) {
                    $this->add_notification(
                        $am_user_id,
                        'booking_pay_negotiation',
                        'Pay negotiation started',
                        $candidate_name . ' opened pay negotiation for ' . $requested_label . '.',
                        $staff_requests_url
                    );
                }
                foreach ($this->get_admin_users_for_support() as $admin_id) {
                    if ($am_user_id && (int) $admin_id === (int) $am_user_id) {
                        continue;
                    }
                    $this->add_notification(
                        (int) $admin_id,
                        'booking_pay_negotiation',
                        'Pay negotiation started',
                        $candidate_name . ' opened pay negotiation for ' . $requested_label . '.',
                        $staff_requests_url
                    );
                }
            }
            wp_redirect(add_query_arg(['candidate' => 'bookings', 'cmn_notice' => rawurlencode('Pay negotiation opened.')], $this->get_portal_base_url()));
            exit;
        }

        if ($action === 'decline') {
            $reason = sanitize_textarea_field($_POST['cmn_decline_reason'] ?? '');
            $wpdb->update($table, [
                'status' => 'declined',
                'internal_note' => $reason,
                'updated_at' => $now,
            ], ['id' => $request_id], ['%s', '%s', '%s'], ['%d']);
            $booking_id = (int) get_post_meta($request_id, 'cmn_booking_id', true);
            if (!$booking_id) {
                $booking_id = $this->create_booking_from_request($request, $school_id, get_current_user_id());
                if ($booking_id) {
                    update_post_meta($booking_id, 'cmn_request_id', $request_id);
                    update_post_meta($request_id, 'cmn_booking_id', $booking_id);
                }
            }
            if ($booking_id) {
                update_post_meta($booking_id, 'cmn_status', 'declined');
                $school_user_id = $this->get_school_user_id_for_request($request, $school_id);
                $thread_id = $this->create_or_get_booking_thread($booking_id, 'decline_followup', [
                    'candidate_user_id' => get_current_user_id(),
                    'school_user_id' => $school_user_id,
                    'account_manager_user_id' => $am_user_id,
                    'status' => 'active',
                ]);
                if ($thread_id) {
                    $this->add_booking_thread_participant($thread_id, get_current_user_id(), 'candidate');
                    if ($am_user_id) {
                        $this->add_booking_thread_participant($thread_id, $am_user_id, 'account_manager');
                    }
                    $this->add_booking_thread_message($thread_id, $am_user_id, 'account_manager', 'No problem - can you share why you declined? (e.g. distance, time, pay, already booked, other)');
                    if ($reason !== '') {
                        $this->add_booking_thread_message($thread_id, get_current_user_id(), 'candidate', 'Decline reason: ' . $reason);
                    }
                }
            }
            $school_name = $school_id ? get_the_title($school_id) : 'School';
            $requested_label = !empty($request['requested_date']) ? date_i18n('M j, Y', strtotime($request['requested_date'])) : 'tomorrow';
            if ($am_user_id) {
                $this->add_notification($am_user_id, 'booking_declined', 'Candidate declined booking', get_the_title($candidate_id) . ' declined ' . $school_name . ' / ' . $requested_label, $staff_requests_url);
            }
            foreach ($this->get_admin_users_for_support() as $admin_id) {
                if ($am_user_id && (int) $admin_id === (int) $am_user_id) {
                    continue;
                }
                $this->add_notification((int) $admin_id, 'booking_declined', 'Candidate declined booking', get_the_title($candidate_id) . ' declined ' . $school_name . ' / ' . $requested_label, $staff_requests_url);
            }
            wp_redirect(add_query_arg(['candidate' => 'bookings', 'cmn_notice' => rawurlencode('Request declined.')], $this->get_portal_base_url()));
            exit;
        }

        if ($action === 'accept') {
            $booking_id = (int) get_post_meta($request_id, 'cmn_booking_id', true);
            if (!$booking_id) {
                $booking_id = $this->create_booking_from_request($request, $school_id, get_current_user_id());
                if ($booking_id) {
                    update_post_meta($booking_id, 'cmn_request_id', $request_id);
                    update_post_meta($request_id, 'cmn_booking_id', $booking_id);
                }
            }
            if (!$booking_id) {
                wp_redirect(add_query_arg(['candidate' => 'bookings', 'cmn_notice' => rawurlencode('Unable to accept request.')], $this->get_portal_base_url()));
                exit;
            }
            update_post_meta($booking_id, 'cmn_status', 'accepted');
            update_post_meta($booking_id, 'cmn_candidate_pay_rate', $this->get_request_candidate_pay_rate($candidate_id, $school_id, $request));
            update_post_meta($booking_id, 'cmn_school_charge_rate', $this->get_request_school_charge_rate($this->get_request_candidate_pay_rate($candidate_id, $school_id, $request), $request));
            $wpdb->update($table, [
                'status' => 'accepted',
                'updated_at' => $now,
            ], ['id' => $request_id], ['%s', '%s'], ['%d']);
            $school_user_id = $this->get_school_user_id_for_request($request, $school_id);
            $thread_id = $this->create_or_get_booking_thread($booking_id, 'booking_details', [
                'candidate_user_id' => get_current_user_id(),
                'school_user_id' => $school_user_id,
                'account_manager_user_id' => $am_user_id,
                'status' => 'active',
            ]);
            if ($thread_id) {
                $this->add_booking_thread_participant($thread_id, get_current_user_id(), 'candidate');
                if ($am_user_id) {
                    $this->add_booking_thread_participant($thread_id, $am_user_id, 'account_manager');
                }
                if ($school_user_id) {
                    $this->add_booking_thread_participant($thread_id, $school_user_id, 'school');
                }
                $this->post_booking_acceptance_auto_messages($thread_id, $booking_id, $request, $school_id, $candidate_id, $school_user_id, $am_user_id);
            }
            $requested_label = !empty($request['requested_date']) ? date_i18n('M j, Y', strtotime($request['requested_date'])) : 'tomorrow';
            $candidate_name = get_the_title($candidate_id);
            if ($school_user_id) {
                $school_chat_url = add_query_arg([
                    'school' => 'requests',
                    'cmn_booking_chat' => $booking_id,
                    'cmn_thread_type' => 'booking_details',
                ], $portal_url);
                $this->add_notification(
                    $school_user_id,
                    'booking_accepted',
                    'Candidate accepted booking',
                    $candidate_name . ' accepted for ' . $requested_label . '.',
                    $school_chat_url
                );
            }
            if ($am_user_id) {
                $this->add_notification(
                    $am_user_id,
                    'booking_accepted',
                    'Candidate accepted booking',
                    $candidate_name . ' accepted for ' . $requested_label . '.',
                    $staff_requests_url
                );
            }
            foreach ($this->get_admin_users_for_support() as $admin_id) {
                if ($am_user_id && (int) $admin_id === (int) $am_user_id) {
                    continue;
                }
                $this->add_notification(
                    (int) $admin_id,
                    'booking_accepted',
                    'Candidate accepted booking',
                    $candidate_name . ' accepted for ' . $requested_label . '.',
                    $staff_requests_url
                );
            }
            wp_redirect(add_query_arg(['candidate' => 'bookings', 'cmn_notice' => rawurlencode('Booking accepted.')], $this->get_portal_base_url()));
            exit;
        }

        wp_redirect(add_query_arg(['candidate' => 'bookings', 'cmn_notice' => rawurlencode('Invalid request action.')], $this->get_portal_base_url()));
        exit;
    }

    public function handle_booking_chat_post() {
        if (!is_user_logged_in()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_booking_chat_nonce']) || !wp_verify_nonce($_POST['cmn_booking_chat_nonce'], 'cmn_booking_chat')) {
            wp_die('Invalid request');
        }
        $thread_id = (int) ($_POST['cmn_thread_id'] ?? 0);
        $message = sanitize_textarea_field($_POST['cmn_message'] ?? '');
        if (!$thread_id || $message === '') {
            wp_die('Invalid message');
        }
        if (!$this->user_can_access_booking_thread($thread_id, get_current_user_id())) {
            wp_die('Unauthorized');
        }
        $role_type = 'candidate';
        if ($this->is_school_user()) {
            $role_type = 'school';
        } elseif ($this->is_staff_user()) {
            $role_type = $this->is_admin_user() ? 'admin' : 'account_manager';
        }
        $uploaded = $this->handle_support_attachments_upload('cmn_attachments');
        $this->add_booking_thread_message($thread_id, get_current_user_id(), $role_type, $message, $uploaded['ids']);
        wp_redirect(wp_get_referer() ?: $this->get_portal_base_url());
        exit;
    }

    public function handle_booking_chat_fetch() {
        if (!check_ajax_referer('cmn_booking_chat_fetch', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $thread_id = (int) ($_POST['thread_id'] ?? 0);
        if (!$thread_id || !$this->user_can_access_booking_thread($thread_id, get_current_user_id())) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        global $wpdb;
        $thread_table = $this->get_booking_threads_table();
        $thread = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$thread_table} WHERE id = %d LIMIT 1",
            $thread_id
        ), ARRAY_A);
        if (!$thread) {
            wp_send_json_error(['message' => 'Thread not found.'], 404);
        }
        $this->maybe_request_booking_feedback_notifications((int) ($thread['booking_id'] ?? 0));
        $messages = $this->get_booking_thread_messages($thread_id);
        $payload = [];
        foreach ($messages as $message_row) {
            $sender_name = ucfirst(str_replace('_', ' ', (string) ($message_row['sender_role_type'] ?? 'system')));
            if (!empty($message_row['sender_user_id'])) {
                $sender_user = get_user_by('id', (int) $message_row['sender_user_id']);
                if ($sender_user) {
                    $sender_name = $sender_user->display_name ?: $sender_user->user_login;
                }
            }
            $payload[] = [
                'id' => (int) ($message_row['id'] ?? 0),
                'sender_user_id' => (int) ($message_row['sender_user_id'] ?? 0),
                'sender_role_type' => (string) ($message_row['sender_role_type'] ?? 'system'),
                'sender_name' => $sender_name,
                'message' => (string) ($message_row['message'] ?? ''),
                'created_at' => (string) ($message_row['created_at'] ?? ''),
                'attachments' => $this->get_booking_message_attachments($message_row),
            ];
        }
        wp_send_json_success([
            'thread' => [
                'id' => (int) ($thread['id'] ?? 0),
                'booking_id' => (int) ($thread['booking_id'] ?? 0),
                'thread_type' => (string) ($thread['thread_type'] ?? 'booking_details'),
                'status' => (string) ($thread['status'] ?? 'active'),
            ],
            'messages' => $payload,
        ]);
    }

    public function handle_booking_feedback_fetch() {
        if (!check_ajax_referer('cmn_booking_feedback', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $booking_id = (int) ($_POST['booking_id'] ?? 0);
        if (!$booking_id) {
            wp_send_json_error(['message' => 'Booking not found.'], 404);
        }
        $viewer_user_id = get_current_user_id();
        $viewer_role = $this->get_booking_feedback_role_for_user($viewer_user_id);
        if (!in_array($viewer_role, ['candidate', 'school', 'staff'], true)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        if (!$this->user_can_access_booking_feedback($booking_id, $viewer_user_id, $viewer_role)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $this->maybe_request_booking_feedback_notifications($booking_id);
        wp_send_json_success($this->get_booking_feedback_payload($booking_id, $viewer_user_id, $viewer_role));
    }

    public function handle_booking_feedback_submit() {
        if (!check_ajax_referer('cmn_booking_feedback', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $booking_id = (int) ($_POST['booking_id'] ?? 0);
        if (!$booking_id) {
            wp_send_json_error(['message' => 'Booking not found.'], 404);
        }
        $viewer_user_id = get_current_user_id();
        $viewer_role = $this->get_booking_feedback_role_for_user($viewer_user_id);
        if (!in_array($viewer_role, ['candidate', 'school'], true)) {
            wp_send_json_error(['message' => 'Only candidates and schools can submit booking feedback.'], 403);
        }
        if (!$this->user_can_access_booking_feedback($booking_id, $viewer_user_id, $viewer_role)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        if (!$this->is_booking_feedback_eligible($booking_id)) {
            wp_send_json_error(['message' => 'Feedback is available after a booking has completed.'], 400);
        }

        $stars_overall = (int) ($_POST['stars_overall'] ?? 0);
        $stars_1 = (int) ($_POST['stars_1'] ?? 0);
        $stars_2 = (int) ($_POST['stars_2'] ?? 0);
        $stars_3 = (int) ($_POST['stars_3'] ?? 0);
        foreach ([$stars_1, $stars_2, $stars_3, $stars_overall] as $stars_value) {
            if ($stars_value < 1 || $stars_value > 5) {
                wp_send_json_error(['message' => 'Please complete all star ratings.'], 400);
            }
        }

        $comment = sanitize_textarea_field((string) ($_POST['comment'] ?? ''));
        $tags = $this->sanitize_booking_feedback_tags($_POST['tags'] ?? [], $viewer_role);
        $would_rebook = null;
        if ($viewer_role === 'candidate') {
            $would_raw = (string) ($_POST['would_rebook'] ?? '');
            if ($would_raw === '1' || strtolower($would_raw) === 'yes') {
                $would_rebook = 1;
            } elseif ($would_raw === '0' || strtolower($would_raw) === 'no') {
                $would_rebook = 0;
            }
        }

        $rated_entity_type = $viewer_role === 'candidate' ? 'school' : 'candidate';
        $rated_entity_id = $rated_entity_type === 'school'
            ? (int) get_post_meta($booking_id, 'cmn_school_id', true)
            : (int) get_post_meta($booking_id, 'cmn_candidate_id', true);
        if (!$rated_entity_id) {
            wp_send_json_error(['message' => 'Unable to resolve booking feedback target.'], 400);
        }

        global $wpdb;
        $table = $this->get_booking_feedback_table();
        $wpdb->replace($table, [
            'booking_id' => $booking_id,
            'rater_user_id' => $viewer_user_id,
            'rated_entity_type' => $rated_entity_type,
            'rated_entity_id' => $rated_entity_id,
            'stars_overall' => $stars_overall,
            'stars_1' => $stars_1,
            'stars_2' => $stars_2,
            'stars_3' => $stars_3,
            'tags' => $tags ? wp_json_encode($tags) : null,
            'would_rebook' => $would_rebook,
            'comment' => $comment,
            'created_at' => current_time('mysql'),
        ], ['%d', '%d', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%s', '%s']);

        if (!empty($wpdb->last_error)) {
            wp_send_json_error(['message' => 'Unable to save feedback right now.'], 500);
        }

        $this->maybe_notify_low_booking_feedback($booking_id, $viewer_role, $stars_overall, $viewer_user_id);
        wp_send_json_success([
            'message' => 'Feedback submitted.',
            'feedback' => $this->get_booking_feedback_payload($booking_id, $viewer_user_id, $viewer_role),
        ]);
    }

    public function handle_booking_chat_ack() {
        if (!is_user_logged_in() || !$this->is_candidate_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_booking_chat_ack_nonce']) || !wp_verify_nonce($_POST['cmn_booking_chat_ack_nonce'], 'cmn_booking_chat_ack')) {
            wp_die('Invalid request');
        }
        $booking_id = (int) ($_POST['cmn_booking_id'] ?? 0);
        if ($booking_id) {
            update_user_meta(get_current_user_id(), 'cmn_booking_chat_ack_' . $booking_id, '1');
        }
        wp_redirect(wp_get_referer() ?: $this->get_portal_base_url());
        exit;
    }

    public function handle_staff_update_candidate_pay() {
        if (!is_user_logged_in() || !$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_staff_update_candidate_pay_nonce']) || !wp_verify_nonce($_POST['cmn_staff_update_candidate_pay_nonce'], 'cmn_staff_update_candidate_pay')) {
            wp_die('Invalid request');
        }
        $request_id = (int) ($_POST['cmn_request_id'] ?? 0);
        $pay_rate = (float) ($_POST['cmn_candidate_pay_rate'] ?? 0);
        if (!$request_id || $pay_rate <= 0) {
            wp_die('Invalid request');
        }
        $request = $this->get_candidate_request_by_id($request_id);
        if (!$request) {
            wp_die('Request not found');
        }
        if (!$this->user_can_access_school($request['school_email_domain'] ?? '')) {
            wp_die('Unauthorized');
        }
        global $wpdb;
        $table = $this->get_candidate_requests_table();
        $wpdb->update($table, [
            'candidate_pay_rate' => $pay_rate,
            'updated_at' => current_time('mysql'),
        ], ['id' => $request_id], ['%f', '%s'], ['%d']);
        $booking_id = (int) get_post_meta($request_id, 'cmn_booking_id', true);
        if ($booking_id) {
            update_post_meta($booking_id, 'cmn_candidate_pay_rate', $pay_rate);
        }
        if ($booking_id) {
            $school_id = (int) ($request['school_id'] ?? 0);
            if (!$school_id && !empty($request['school_email_domain'])) {
                $school_id = (int) $this->get_school_post_id_by_domain((string) $request['school_email_domain']);
            }
            $candidate_user_id = (int) $this->get_candidate_user_id((int) ($request['candidate_id'] ?? 0));
            $school_user_id = $this->get_school_user_id_for_request($request, $school_id);
            $thread_id = $this->create_or_get_booking_thread($booking_id, 'pay_negotiation', [
                'candidate_user_id' => $candidate_user_id,
                'school_user_id' => $school_user_id,
                'account_manager_user_id' => get_current_user_id(),
                'status' => 'active',
            ]);
            if ($thread_id) {
                $this->add_booking_thread_message($thread_id, get_current_user_id(), 'account_manager', 'Account manager updated candidate pay to £' . number_format($pay_rate, 2) . '.');
            }
        }
        wp_redirect(wp_get_referer() ?: add_query_arg(['view' => 'requests'], $this->get_portal_base_url()));
        exit;
    }

    public function handle_update_candidate_request() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_update_candidate_request_nonce']) || !wp_verify_nonce($_POST['cmn_update_candidate_request_nonce'], 'cmn_update_candidate_request')) {
            wp_die('Invalid request');
        }
        $request_id = intval($_POST['cmn_request_id'] ?? 0);
        if (!$request_id) {
            wp_die('Invalid request.');
        }
        $request = $this->get_candidate_request_by_id($request_id);
        if (!$request) {
            wp_die('Request not found.');
        }
        $school_domain = $request['school_email_domain'] ?? '';
        if (!$this->user_can_access_school($school_domain)) {
            wp_die('Unauthorized');
        }

        $action = sanitize_text_field($_POST['cmn_request_action'] ?? 'confirm');
        $confirmed = sanitize_text_field($_POST['cmn_confirmed'] ?? 'yes');
        $note = sanitize_textarea_field($_POST['cmn_internal_note'] ?? '');
        $candidate_pay_rate_input = isset($_POST['cmn_candidate_pay_rate']) ? (float) $_POST['cmn_candidate_pay_rate'] : 0;

        $candidate_id = (int) $request['candidate_id'];
        $requested_date = sanitize_text_field($request['requested_date'] ?? '');
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        if ($requested_date && strtotime($requested_date) < strtotime(date('Y-m-d'))) {
            $redirect = wp_get_referer() ?: home_url('/portal');
            wp_redirect(add_query_arg(['cmn_request_msg' => rawurlencode('Request date has passed.')], $redirect));
            exit;
        }

        $status = 'requested';
        if ($action === 'decline' || $confirmed === 'no') {
            $status = 'declined';
        } elseif ($action === 'no_longer_needed') {
            $status = 'cancelled';
        } elseif ($action === 'refresh') {
            $status = 'requested';
        } else {
            $status = 'confirmed';
        }

        if ($status === 'confirmed') {
            if (!$this->has_candidate_availability($candidate_id, $requested_date)) {
                $redirect = wp_get_referer() ?: home_url('/portal');
                wp_redirect(add_query_arg(['cmn_request_msg' => rawurlencode('Candidate is no longer marked available.')], $redirect));
                exit;
            }
            if ($this->is_candidate_unavailable($candidate_id, $requested_date)) {
                $redirect = wp_get_referer() ?: home_url('/portal');
                wp_redirect(add_query_arg(['cmn_request_msg' => rawurlencode('Candidate marked unavailable in calendar.')], $redirect));
                exit;
            }
            if ($this->has_booking_for_candidate_date($candidate_id, $requested_date)) {
                $redirect = wp_get_referer() ?: home_url('/portal');
                wp_redirect(add_query_arg(['cmn_request_msg' => rawurlencode('Candidate already booked for that date.')], $redirect));
                exit;
            }
        }

        if ($action === 'refresh') {
            $new_sent = current_time('mysql');
            $new_expires = gmdate('Y-m-d H:i:s', strtotime(gmdate('Y-m-d H:i:s') . ' +15 minutes'));
            global $wpdb;
            $table = $this->get_candidate_requests_table();
            $wpdb->update($table, [
                'status' => 'requested',
                'request_sent_at' => $new_sent,
                'expires_at' => $new_expires,
                'updated_at' => $new_sent,
            ], [
                'id' => $request_id,
            ], ['%s', '%s', '%s', '%s'], ['%d']);
            $candidate_user_id = $this->get_candidate_user_id($candidate_id);
            if ($candidate_user_id) {
                $candidate_bookings_url = add_query_arg(['candidate' => 'bookings'], $portal_url);
                $this->add_notification($candidate_user_id, 'booking_request', 'Booking request', 'A booking request has been refreshed for ' . date_i18n('M j, Y', strtotime($requested_date)), $candidate_bookings_url);
            }
            $candidate_email = $candidate_id ? get_post_meta($candidate_id, 'cmn_email', true) : '';
            if ($candidate_email) {
                $this->send_candidate_email($candidate_email, 'Booking request refreshed', "A booking request has been refreshed for " . date_i18n('l, F jS', strtotime($requested_date)) . ".\n\nPlease log in to respond.", [
                    'type' => 'candidate_availability_request',
                    'related_school_domain' => $school_domain,
                    'related_candidate_id' => $candidate_id,
                    'related_request_id' => $request_id,
                ]);
            }
            $redirect = wp_get_referer() ?: home_url('/portal');
            wp_redirect(add_query_arg(['cmn_request_msg' => rawurlencode('Request refreshed for 15 minutes.')], $redirect));
            exit;
        }

        $school_id = $this->get_school_post_id_by_domain($school_domain);
        $candidate = get_post($candidate_id);
        $candidate_name = $candidate ? $candidate->post_title : 'Candidate';
        $name_parts = preg_split('/\\s+/', trim((string) $candidate_name));
        $first_name = $name_parts ? $name_parts[0] : $candidate_name;
        $role_meta = (array) get_post_meta($candidate_id, 'cmn_roles', true);
        $role_label = $role_meta ? $role_meta[0] : 'Candidate';
        $school_name = $school_id ? get_the_title($school_id) : 'School';
        $requested_label = $requested_date ? date_i18n('l, F jS', strtotime($requested_date)) : 'tomorrow';
        $candidate_email = $candidate_id ? get_post_meta($candidate_id, 'cmn_email', true) : '';
        $candidate_user_id = $this->get_candidate_user_id($candidate_id);
        $school_user_id = $this->get_school_user_id_for_request($request, $school_id);
        $school_requests_url = add_query_arg(['school' => 'requests'], $portal_url);
        $candidate_bookings_url = add_query_arg(['candidate' => 'bookings'], $portal_url);

        if ($status === 'confirmed') {
            $booking_id = $this->create_booking_from_request($request, $school_id, get_current_user_id());
            if (!$booking_id) {
                $redirect = wp_get_referer() ?: home_url('/portal');
                wp_redirect(add_query_arg(['cmn_request_msg' => rawurlencode('Unable to create booking.')], $redirect));
                exit;
            }
        }

        global $wpdb;
        $table = $this->get_candidate_requests_table();
        $update_data = [
            'status' => $status,
            'internal_note' => $note,
            'updated_at' => current_time('mysql'),
        ];
        $update_format = ['%s', '%s', '%s'];
        if ($candidate_pay_rate_input > 0) {
            $update_data['candidate_pay_rate'] = $candidate_pay_rate_input;
            $update_format[] = '%f';
        }
        $wpdb->update($table, $update_data, [
            'id' => $request_id,
        ], $update_format, ['%d']);

        if ($status === 'confirmed') {
            $school_email = $this->get_school_primary_contact_email($school_id);
            if ($school_email) {
                $subject = 'Candidate confirmed for tomorrow';
                $message = "Good news!\n\n{$first_name} ({$role_label}) is confirmed for {$requested_label}.\n\nWe’ll be in touch if anything changes.";
                $previous_log_type = $GLOBALS['cmn_school_mail_log_type'] ?? null;
                $GLOBALS['cmn_school_mail_log_type'] = 'school_request_confirmed';
                $this->send_school_email($school_email, $subject, $message);
                if ($previous_log_type !== null) {
                    $GLOBALS['cmn_school_mail_log_type'] = $previous_log_type;
                } else {
                    unset($GLOBALS['cmn_school_mail_log_type']);
                }
            }
            if ($school_user_id) {
                $this->add_notification($school_user_id, 'request_confirmed', 'Request confirmed', $first_name . ' confirmed for ' . $requested_label . '.', $school_requests_url);
            }
            if ($candidate_email) {
                $subject = 'You’ve been booked for tomorrow';
                $message = "Hi {$first_name},\n\nYou’ve been booked for {$requested_label} at {$school_name}.\n\nPlease reply if you cannot attend.\n\nThank you,\nCoverMeNow ONE";
                $this->send_candidate_email($candidate_email, $subject, $message, [
                    'type' => 'candidate_booking_confirmed',
                    'related_school_domain' => $school_domain,
                    'related_candidate_id' => $candidate_id,
                    'related_request_id' => $request_id,
                ]);
            }
            if ($candidate_user_id) {
                $this->add_notification($candidate_user_id, 'booking_confirmed', 'Booking confirmed', 'You’re booked for ' . $requested_label . ' at ' . $school_name . '.', $candidate_bookings_url);
            }
            $this->insert_activity_row([
                'entity_type' => 'school',
                'entity_ref' => $school_domain,
                'activity_type' => 'note',
                'subject' => 'Candidate availability confirmed',
                'notes' => $candidate_name . ' confirmed for ' . $requested_date . '.',
                'created_by' => get_current_user_id(),
            ]);
            $this->insert_activity_row([
                'entity_type' => 'contact',
                'entity_ref' => (string) $candidate_id,
                'activity_type' => 'note',
                'subject' => 'Availability confirmed',
                'notes' => 'Confirmed for ' . $requested_date . '.',
                'created_by' => get_current_user_id(),
            ]);
        } elseif ($status === 'declined') {
            $school_email = $this->get_school_primary_contact_email($school_id);
            if ($school_email) {
                $subject = 'Candidate unavailable';
                $message = "Unfortunately {$first_name} ({$role_label}) is unavailable for {$requested_label}.\n\nPlease select another available candidate and we’ll confirm as quickly as possible.";
                $previous_log_type = $GLOBALS['cmn_school_mail_log_type'] ?? null;
                $GLOBALS['cmn_school_mail_log_type'] = 'school_request_declined';
                $this->send_school_email($school_email, $subject, $message);
                if ($previous_log_type !== null) {
                    $GLOBALS['cmn_school_mail_log_type'] = $previous_log_type;
                } else {
                    unset($GLOBALS['cmn_school_mail_log_type']);
                }
            }
            if ($school_user_id) {
                $this->add_notification($school_user_id, 'request_declined', 'Request declined', $first_name . ' is unavailable for ' . $requested_label . '.', $school_requests_url);
            }
            if (self::EMAIL_CANDIDATE_DECLINED && $candidate_email) {
                $subject = 'Update on your availability';
                $message = "Hi {$first_name},\n\nYou weren’t selected for {$requested_label}.\n\nThank you for confirming your availability.\n\nCoverMeNow ONE";
                $this->send_candidate_email($candidate_email, $subject, $message, [
                    'type' => 'candidate_booking_declined',
                    'related_school_domain' => $school_domain,
                    'related_candidate_id' => $candidate_id,
                    'related_request_id' => $request_id,
                ]);
            }
            $this->insert_activity_row([
                'entity_type' => 'school',
                'entity_ref' => $school_domain,
                'activity_type' => 'note',
                'subject' => 'Request declined',
                'notes' => $candidate_name . ' unavailable for ' . $requested_date . '.',
                'created_by' => get_current_user_id(),
            ]);
            $this->insert_activity_row([
                'entity_type' => 'contact',
                'entity_ref' => (string) $candidate_id,
                'activity_type' => 'note',
                'subject' => 'Request declined',
                'notes' => 'Unavailable for ' . $requested_date . '.',
                'created_by' => get_current_user_id(),
            ]);
        } else {
            $school_email = $this->get_school_primary_contact_email($school_id);
            if ($school_email) {
                $subject = 'Booking request no longer needed';
                $message = "This request is no longer needed for {$requested_label}.";
                $previous_log_type = $GLOBALS['cmn_school_mail_log_type'] ?? null;
                $GLOBALS['cmn_school_mail_log_type'] = 'school_request_cancelled';
                $this->send_school_email($school_email, $subject, $message);
                if ($previous_log_type !== null) {
                    $GLOBALS['cmn_school_mail_log_type'] = $previous_log_type;
                } else {
                    unset($GLOBALS['cmn_school_mail_log_type']);
                }
            }
            if ($candidate_email) {
                $this->send_candidate_email($candidate_email, 'Booking request no longer needed', "This request for {$requested_label} is no longer needed.", [
                    'type' => 'candidate_booking_declined',
                    'related_school_domain' => $school_domain,
                    'related_candidate_id' => $candidate_id,
                    'related_request_id' => $request_id,
                ]);
            }
        }

        $redirect = wp_get_referer() ?: home_url('/portal');
        wp_redirect(add_query_arg(['cmn_request_msg' => rawurlencode('Request updated.')], $redirect));
        exit;
    }

    public function handle_create_booking() {
        if (!isset($_POST['cmn_create_booking_nonce']) || !wp_verify_nonce($_POST['cmn_create_booking_nonce'], 'cmn_create_booking')) {
            wp_die('Invalid request');
        }

        $start_date = sanitize_text_field($_POST['cmn_start_date'] ?? $_POST['cmn_date'] ?? '');
        $end_date = sanitize_text_field($_POST['cmn_end_date'] ?? '');
        $until_further_notice = isset($_POST['cmn_until_further_notice']) ? '1' : '0';
        $start_time = sanitize_text_field($_POST['cmn_start_time'] ?? '');
        $end_time = sanitize_text_field($_POST['cmn_end_time'] ?? '');
        $role = sanitize_text_field($_POST['cmn_role'] ?? '');
        $notes = sanitize_textarea_field($_POST['cmn_notes'] ?? '');
        $school_id = intval($_POST['cmn_school_id'] ?? 0);
        $candidate_id = intval($_POST['cmn_candidate_id'] ?? 0);
        $location = sanitize_text_field($_POST['cmn_location'] ?? '');
        if (!$location && $school_id) {
            $location = get_post_meta($school_id, 'cmn_location', true);
        }
        $requested_rate = sanitize_text_field($_POST['cmn_requested_rate'] ?? '');

        $post_id = wp_insert_post([
            'post_type' => 'cmn_booking',
            'post_title' => $role && $start_date ? $role . ' - ' . $start_date : 'Booking Request',
            'post_status' => 'publish',
        ]);

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, 'cmn_date', $start_date);
            update_post_meta($post_id, 'cmn_start_date', $start_date);
            update_post_meta($post_id, 'cmn_end_date', $end_date);
            update_post_meta($post_id, 'cmn_until_further_notice', $until_further_notice);
            update_post_meta($post_id, 'cmn_start_time', $start_time);
            update_post_meta($post_id, 'cmn_end_time', $end_time);
            update_post_meta($post_id, 'cmn_role', $role);
            update_post_meta($post_id, 'cmn_notes', $notes);
            update_post_meta($post_id, 'cmn_status', 'requested');
            update_post_meta($post_id, 'cmn_location', $location);
            if ($requested_rate !== '') {
                update_post_meta($post_id, 'cmn_requested_rate', $requested_rate);
            }
            update_post_meta($post_id, 'cmn_created_at', time());
            if ($school_id) {
                update_post_meta($post_id, 'cmn_school_id', $school_id);
            }
            if ($candidate_id) {
                update_post_meta($post_id, 'cmn_candidate_id', $candidate_id);
            }

            $admin_email = get_option('admin_email');
            $subject = 'New Booking Request';
            $message = "A new booking request was submitted.\n\nStart: {$start_date}\nEnd: {$end_date}\nRole: {$role}\nNotes: {$notes}\n\nReview in the CRM.";
            wp_mail($admin_email, $subject, $message);
        }

        wp_redirect(add_query_arg('cmn_requested', '1', wp_get_referer() ?: home_url()));
        exit;
    }

    public function handle_update_status() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_update_status_nonce']) || !wp_verify_nonce($_POST['cmn_update_status_nonce'], 'cmn_update_status')) {
            wp_die('Invalid request');
        }
        $entity_type = sanitize_text_field($_POST['cmn_entity_type'] ?? '');
        $entity_id_raw = sanitize_text_field($_POST['cmn_entity_id'] ?? '');
        $status = sanitize_text_field($_POST['cmn_status'] ?? '');

        if ($entity_id_raw === '' || !$status) {
            wp_die('Invalid data');
        }
        $entity_id = intval($entity_id_raw);

        if ($entity_type === 'school') {
            $school_post_id = $this->resolve_school_identifier($entity_id_raw);
            if (!$school_post_id) {
                wp_die('Invalid school.');
            }
            if (!$this->user_can_access_school($school_post_id)) {
                wp_die('Unauthorized');
            }
            $previous_status = get_post_meta($school_post_id, 'cmn_status', true);
            update_post_meta($school_post_id, 'cmn_status', $status);
            $pipeline_stage = sanitize_text_field($_POST['cmn_pipeline_stage'] ?? '');
            if ($pipeline_stage) {
                if ($status === 'client' && $pipeline_stage !== 'lost') {
                    $pipeline_stage = 'won';
                }
                update_post_meta($school_post_id, 'cmn_pipeline_stage', $pipeline_stage);
            } elseif ($status === 'client') {
                $current_stage = get_post_meta($school_post_id, 'cmn_pipeline_stage', true);
                if ($current_stage !== 'lost') {
                    update_post_meta($school_post_id, 'cmn_pipeline_stage', 'won');
                }
            }
            if ($status !== $previous_status) {
                $this->send_school_status_email($school_post_id, $status);
            }
            $redirect = esc_url_raw($_POST['cmn_redirect'] ?? '');
            if ($redirect) {
                wp_redirect($redirect);
            } else {
                wp_redirect(admin_url('admin.php?page=cmn-school-profile&school_id=' . $school_post_id));
            }
            exit;
        }
        if ($entity_type === 'candidate') {
            if (!$this->user_can_view_candidate($entity_id)) {
                wp_die('Unauthorized');
            }
            update_post_meta($entity_id, 'cmn_status', $status);
            $email = get_post_meta($entity_id, 'cmn_email', true);
            if ($email) {
                $subject = 'Candidate Account Update';
                $message = "Your candidate account status is now: {$status}.";
                $this->send_candidate_email($email, $subject, $message, [
                    'type' => 'candidate_status_update',
                    'related_candidate_id' => $entity_id,
                ]);
            }
            $redirect = esc_url_raw($_POST['cmn_redirect'] ?? '');
            if ($redirect) {
                wp_redirect($redirect);
            } else {
                wp_redirect(admin_url('admin.php?page=cmn-candidate-profile&candidate_id=' . $entity_id));
            }
            exit;
        }
        if ($entity_type === 'booking') {
            update_post_meta($entity_id, 'cmn_status', $status);
            if (sanitize_key($status) === 'completed') {
                $this->maybe_request_booking_feedback_notifications((int) $entity_id, true);
            }
            $subject = 'Booking Update';
            $message = "A booking has been {$status}. Please log in for details.";
            wp_mail(get_option('admin_email'), $subject, $message);
            $school_id = get_post_meta($entity_id, 'cmn_school_id', true);
            $candidate_id = get_post_meta($entity_id, 'cmn_candidate_id', true);
            $school_email = $school_id ? get_post_meta($school_id, 'cmn_email', true) : '';
            $candidate_email = $candidate_id ? get_post_meta($candidate_id, 'cmn_email', true) : '';
            $role = get_post_meta($entity_id, 'cmn_role', true);
            $start_date = get_post_meta($entity_id, 'cmn_start_date', true);
            $end_date = get_post_meta($entity_id, 'cmn_end_date', true);
            $start_time = get_post_meta($entity_id, 'cmn_start_time', true);
            $end_time = get_post_meta($entity_id, 'cmn_end_time', true);
            $location = get_post_meta($entity_id, 'cmn_location', true);
            if ($school_email) {
                $school_message = "Booking status: {$status}.\n\nRole: {$role}\nDates: {$start_date}" . ($end_date ? " to {$end_date}" : '') . "\nTime: {$start_time} - {$end_time}\nLocation: {$location}\n\nWe will confirm full placement details once approved.";
                if ($status === 'approved') {
                    $school_message = "Booking confirmed.\n\nRole: {$role}\nDates: {$start_date}" . ($end_date ? " to {$end_date}" : '') . "\nTime: {$start_time} - {$end_time}\nLocation: {$location}\n\nYour account manager will send full placement details.";
                }
                $previous_log_type = $GLOBALS['cmn_school_mail_log_type'] ?? null;
                $GLOBALS['cmn_school_mail_log_type'] = 'school_booking_update';
                $this->send_school_email($school_email, $subject, $school_message);
                if ($previous_log_type !== null) {
                    $GLOBALS['cmn_school_mail_log_type'] = $previous_log_type;
                } else {
                    unset($GLOBALS['cmn_school_mail_log_type']);
                }
            }
            if ($candidate_email) {
                $candidate_message = "Booking status updated: {$status}. We will be in touch with full details once confirmed.";
                if ($status === 'approved') {
                    $candidate_message = "Booking confirmed. You will receive placement details shortly.";
                }
                $this->send_candidate_email($candidate_email, $subject, $candidate_message, [
                    'type' => 'candidate_booking_update',
                    'related_candidate_id' => (int) $candidate_id,
                ]);
            }
            $redirect = esc_url_raw($_POST['cmn_redirect'] ?? '');
            if ($redirect) {
                wp_redirect($redirect);
            } else {
                wp_redirect(admin_url('admin.php?page=cmn-bookings'));
            }
            exit;
        }

        wp_die('Unknown entity type');
    }

    public function handle_send_candidate_invite() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_send_candidate_invite_nonce']) || !wp_verify_nonce($_POST['cmn_send_candidate_invite_nonce'], 'cmn_send_candidate_invite')) {
            wp_die('Invalid request');
        }
        $booking_id = intval($_POST['cmn_booking_id'] ?? 0);
        $candidate_id = intval($_POST['cmn_candidate_id'] ?? 0);
        if (!$booking_id || !$candidate_id) {
            wp_die('Missing booking or candidate.');
        }
        $school_id = get_post_meta($booking_id, 'cmn_school_id', true);
        update_post_meta($booking_id, 'cmn_candidate_id', $candidate_id);
        update_post_meta($booking_id, 'cmn_status', 'candidate_invited');
        update_post_meta($booking_id, 'cmn_candidate_invited_at', time());
        $deadline = time() + (15 * 60);
        update_post_meta($booking_id, 'cmn_candidate_deadline', $deadline);
        $token = wp_generate_password(20, false, false);
        update_post_meta($booking_id, 'cmn_candidate_token', $token);

        $candidate_email = get_post_meta($candidate_id, 'cmn_email', true);
        $role = get_post_meta($booking_id, 'cmn_role', true);
        $start_date = get_post_meta($booking_id, 'cmn_start_date', true);
        $end_date = get_post_meta($booking_id, 'cmn_end_date', true);
        $start_time = get_post_meta($booking_id, 'cmn_start_time', true);
        $end_time = get_post_meta($booking_id, 'cmn_end_time', true);
        $location = get_post_meta($booking_id, 'cmn_location', true);
        $rate = $this->get_candidate_rate($candidate_id, $school_id);
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        $accept_url = add_query_arg([
            'action' => 'cmn_candidate_response',
            'booking_id' => $booking_id,
            'response' => 'accept',
            'token' => $token,
        ], admin_url('admin-post.php'));
        $decline_url = add_query_arg([
            'action' => 'cmn_candidate_response',
            'booking_id' => $booking_id,
            'response' => 'decline',
            'token' => $token,
        ], admin_url('admin-post.php'));

        $subject = 'CoverMeNow ONE - Availability Request';
        $message = "We have a cover request:\n\nRole: {$role}\nDates: {$start_date}" . ($end_date ? " to {$end_date}" : '') . "\nTime: {$start_time} - {$end_time}\nLocation: Within {$location}\nRate: £{$rate}/day\n\nWould you like to proceed?\nAccept: {$accept_url}\nDecline: {$decline_url}\n\nPlease respond within 15 minutes.";
        $account = $school_id ? $this->get_account_manager($school_id) : ['email' => get_option('admin_email'), 'name' => 'CoverMeNow ONE'];
        $this->send_candidate_email($candidate_email, $subject, $message, [
            'type' => 'candidate_availability_request',
            'related_candidate_id' => $candidate_id,
        ]);

        wp_redirect(add_query_arg(['view' => 'bookings', 'cmn_sent' => '1'], home_url('/cmn-one')));
        exit;
    }

    public function handle_candidate_response() {
        $booking_id = intval($_REQUEST['booking_id'] ?? 0);
        $response = sanitize_text_field($_REQUEST['response'] ?? '');
        $token = sanitize_text_field($_REQUEST['token'] ?? '');
        if (!$booking_id || !$token) {
            wp_die('Invalid response.');
        }
        $saved_token = get_post_meta($booking_id, 'cmn_candidate_token', true);
        $deadline = (int) get_post_meta($booking_id, 'cmn_candidate_deadline', true);
        if ($saved_token !== $token) {
            wp_die('Invalid token.');
        }
        if ($deadline && time() > $deadline) {
            update_post_meta($booking_id, 'cmn_status', 'expired');
            wp_die('This request has expired.');
        }
        if ($response === 'accept') {
            update_post_meta($booking_id, 'cmn_status', 'candidate_accepted');
        } else {
            update_post_meta($booking_id, 'cmn_status', 'candidate_declined');
        }

        $school_id = get_post_meta($booking_id, 'cmn_school_id', true);
        $account = $school_id ? $this->get_account_manager($school_id) : ['email' => get_option('admin_email'), 'name' => 'CoverMeNow ONE'];
        $subject = 'Candidate Response';
        $message = "Candidate response received: {$response}. Booking ID: {$booking_id}.";
        $this->send_cmn_mail($account['email'], $subject, $message, 'candidate@covermenow.co.uk', 'CoverMeNow ONE');

        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        wp_redirect(add_query_arg('candidate_response', $response, $portal_url));
        exit;
    }

    public function expire_booking_requests() {
        $args = [
            'post_type' => 'cmn_booking',
            'posts_per_page' => 20,
            'meta_query' => [
                [
                    'key' => 'cmn_status',
                    'value' => 'candidate_invited',
                ],
                [
                    'key' => 'cmn_candidate_deadline',
                    'value' => time(),
                    'compare' => '<',
                    'type' => 'NUMERIC',
                ],
            ],
        ];
        $items = get_posts($args);
        foreach ($items as $booking) {
            update_post_meta($booking->ID, 'cmn_status', 'expired');
            $school_id = get_post_meta($booking->ID, 'cmn_school_id', true);
            $account = $school_id ? $this->get_account_manager($school_id) : ['email' => get_option('admin_email'), 'name' => 'CoverMeNow ONE'];
            $subject = 'Booking Invite Expired';
            $message = 'A candidate did not respond in time. Booking ID: ' . $booking->ID . '.';
            $this->send_cmn_mail($account['email'], $subject, $message, 'candidate@covermenow.co.uk', 'CoverMeNow ONE');
        }
        $this->maybe_request_feedback_for_past_bookings();
    }

    public function handle_update_school_assignments() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        $school_id = intval($_POST['cmn_school_id'] ?? 0);
        $assigned = isset($_POST['cmn_assigned_candidates']) ? sanitize_text_field($_POST['cmn_assigned_candidates']) : '';
        if ($school_id) {
            $ids = array_filter(array_map('intval', array_map('trim', explode(',', $assigned))));
            update_post_meta($school_id, 'cmn_assigned_candidates', $ids);
        }
        wp_redirect(wp_get_referer() ?: admin_url());
        exit;
    }

    public function handle_update_candidate_rate() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        $candidate_id = intval($_POST['cmn_candidate_id'] ?? 0);
        $rate = sanitize_text_field($_POST['cmn_default_rate'] ?? '');
        if ($candidate_id) {
            update_post_meta($candidate_id, 'cmn_default_rate', $rate);
        }
        wp_redirect(wp_get_referer() ?: admin_url());
        exit;
    }

    private function sync_registered_candidate_doc_upload($candidate_id, $candidate_user_id, $doc_type, $upload) {
        $candidate_id = (int) $candidate_id;
        $candidate_user_id = (int) $candidate_user_id;
        $doc_type = sanitize_key((string) $doc_type);
        if ($candidate_id < 1 || $doc_type === '' || !is_array($upload)) {
            return;
        }

        $attachment_id = (int) ($upload['attachment_id'] ?? 0);
        $url = esc_url_raw((string) ($upload['url'] ?? ''));
        if ($attachment_id < 1 || $url === '') {
            return;
        }

        $keys = $this->get_candidate_doc_meta_keys($doc_type);
        if (!$keys) {
            return;
        }

        $uploaded_at = sanitize_text_field((string) ($upload['uploaded_at'] ?? current_time('mysql')));
        $filename = sanitize_file_name((string) ($upload['filename'] ?? ''));
        $filesize = (int) ($upload['filesize'] ?? 0);
        $mime = sanitize_text_field((string) ($upload['mime'] ?? ''));

        if (!empty($keys['post_meta'])) {
            update_post_meta($candidate_id, (string) $keys['post_meta'], $url);
        }

        if ($candidate_user_id > 0) {
            update_user_meta($candidate_user_id, (string) $keys['attachment'], $attachment_id);
            update_user_meta($candidate_user_id, (string) $keys['uploaded_at'], $uploaded_at);
            if (!empty($keys['filename']) && $filename !== '') {
                update_user_meta($candidate_user_id, (string) $keys['filename'], $filename);
            }
            if (!empty($keys['filesize']) && $filesize > 0) {
                update_user_meta($candidate_user_id, (string) $keys['filesize'], (string) $filesize);
            }
            if (!empty($keys['mime']) && $mime !== '') {
                update_user_meta($candidate_user_id, (string) $keys['mime'], $mime);
            }
            if (!empty($keys['legacy_attachment'])) {
                update_user_meta($candidate_user_id, (string) $keys['legacy_attachment'], $attachment_id);
            }
            if (!empty($keys['legacy_uploaded_at'])) {
                update_user_meta($candidate_user_id, (string) $keys['legacy_uploaded_at'], $uploaded_at);
            }
            if (!empty($keys['review_status'])) {
                update_user_meta($candidate_user_id, (string) $keys['review_status'], 'pending');
            }
            if (!empty($keys['review_reason'])) {
                delete_user_meta($candidate_user_id, (string) $keys['review_reason']);
            }
            if (!empty($keys['reviewed_at'])) {
                delete_user_meta($candidate_user_id, (string) $keys['reviewed_at']);
            }
            if (!empty($keys['reviewed_by'])) {
                delete_user_meta($candidate_user_id, (string) $keys['reviewed_by']);
            }
        }

        update_post_meta($attachment_id, 'cmn_doc_type', $doc_type);
        update_post_meta($attachment_id, 'cmn_candidate_id', $candidate_id);
        if ($candidate_user_id > 0) {
            update_post_meta($attachment_id, 'cmn_owner_user_id', $candidate_user_id);
        }
    }

    private function handle_file_upload_with_attachment($field_name, $allowed_mimes = []) {
        if (empty($_FILES[$field_name]['name'])) {
            return [];
        }
        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $uploadedfile = $_FILES[$field_name];
        $upload_overrides = ['test_form' => false];
        if (is_array($allowed_mimes) && !empty($allowed_mimes)) {
            $upload_overrides['mimes'] = $allowed_mimes;
        }
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        if (isset($movefile['error'])) {
            return [];
        }

        $attachment_id = wp_insert_attachment([
            'post_mime_type' => (string) ($movefile['type'] ?? ''),
            'post_title' => sanitize_file_name(pathinfo((string) ($uploadedfile['name'] ?? ''), PATHINFO_FILENAME)),
            'post_status' => 'inherit',
            'guid' => (string) ($movefile['url'] ?? ''),
        ], (string) ($movefile['file'] ?? ''));
        if (is_wp_error($attachment_id) || !$attachment_id) {
            return [];
        }
        $meta = wp_generate_attachment_metadata($attachment_id, (string) ($movefile['file'] ?? ''));
        if (!is_wp_error($meta)) {
            wp_update_attachment_metadata($attachment_id, $meta);
        }

        return [
            'url' => (string) ($movefile['url'] ?? ''),
            'attachment_id' => (int) $attachment_id,
            'uploaded_at' => current_time('mysql'),
            'filename' => sanitize_file_name((string) ($uploadedfile['name'] ?? '')),
            'filesize' => (int) ($uploadedfile['size'] ?? 0),
            'mime' => sanitize_text_field((string) ($movefile['type'] ?? '')),
        ];
    }

    private function handle_file_upload($field_name) {
        $upload = $this->handle_file_upload_with_attachment($field_name);
        return is_array($upload) ? (string) ($upload['url'] ?? '') : '';
    }

    public function render_marketing_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>Marketing Hub</h1>';
        echo '<p>Campaigns, templates, outreach sequences, and lead segmentation will live here.</p>';
        echo '<ul>';
        echo '<li>Campaign builder + schedule</li>';
        echo '<li>Email template library</li>';
        echo '<li>Lead lists + segmentation</li>';
        echo '</ul>';
        echo '</div>';
    }

    public function render_finance_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>Finance Hub</h1>';
        echo '<p>Revenue tracking, invoices, and monthly summaries will live here.</p>';
        echo '<ul>';
        echo '<li>Booking revenue ledger</li>';
        echo '<li>Invoices + payment status</li>';
        echo '<li>Monthly summary + exports</li>';
        echo '</ul>';
        echo '</div>';
    }

    public function render_import_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $message = '';
        if (!empty($_FILES['cmn_csv']['tmp_name']) && check_admin_referer('cmn_import', 'cmn_import_nonce')) {
            $message = $this->import_schools_csv($_FILES['cmn_csv']['tmp_name']);
        }
        echo '<div class="wrap">';
        echo '<h1>CMN Import</h1>';
        if ($message) {
            echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
        }
        echo '<form method="post" enctype="multipart/form-data">';
        wp_nonce_field('cmn_import', 'cmn_import_nonce');
        echo '<p><input type="file" name="cmn_csv" accept=".csv" required></p>';
        echo '<p><button class="button button-primary" type="submit">Import Schools CSV</button></p>';
        echo '</form>';
        echo '<p>Expected headers (required): School, Location, Phone, Email, Cover Manager, Cover Manager Email, Email Name. Optional: Account Manager, Status, Pipeline Stage, School ID, Contact Name, Contact Email, Contact Phone, Contact Role, Switchboard, Website. Status defaults to lead when blank.</p>';
        echo '</div>';
    }

    private function import_schools_csv($file) {
        $handle = fopen($file, 'r');
        if (!$handle) {
            return 'Could not open file.';
        }
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return 'Empty CSV.';
        }
        $map = [];
        foreach ($header as $index => $name) {
            $map[trim($name)] = $index;
        }
        $required = ['School', 'Location', 'Phone', 'Email', 'Cover Manager', 'Cover Manager Email', 'Email Name'];
        foreach ($required as $req) {
            if (!array_key_exists($req, $map)) {
                fclose($handle);
                return 'Missing required column: ' . $req;
            }
        }

        $count = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $contact_warnings = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $school_name = trim($row[$map['School']] ?? '');
            if ($school_name === '') {
                $skipped++;
                continue;
            }
            $location = trim($row[$map['Location']] ?? '');
            $phone = trim($row[$map['Phone']] ?? '');
            $school_email = trim($row[$map['Email']] ?? '');
            $cover_manager_name = trim($row[$map['Cover Manager']] ?? '');
            $cover_manager_email = trim($row[$map['Cover Manager Email']] ?? '');
            $email_name = trim($row[$map['Email Name']] ?? '');
            if ($location === '' || $phone === '' || $school_email === '' || $cover_manager_name === '' || $cover_manager_email === '' || $email_name === '') {
                $skipped++;
                continue;
            }
            if ($school_email && !is_email($school_email)) {
                $skipped++;
                continue;
            }
            if ($cover_manager_email && !is_email($cover_manager_email)) {
                $skipped++;
                continue;
            }
            if (strlen(preg_replace('/\\D+/', '', $phone)) < 7) {
                $skipped++;
                continue;
            }
            $school_domain = $this->get_email_domain($school_email);
            if ($school_domain === '') {
                $skipped++;
                continue;
            }

            $school_code = isset($map['School ID']) ? trim($row[$map['School ID']] ?? '') : '';
            if (!preg_match('/^CMN\\d+$/i', $school_code)) {
                $school_code = '';
            }
            $existing_id = 0;
            if ($school_code !== '') {
                $existing_id = $this->get_school_post_id_by_school_id($school_code);
            }
            if (!$existing_id) {
                $existing_id = $this->get_school_post_id_by_domain($school_domain);
            }
            if ($existing_id) {
                $post_id = $existing_id;
                wp_update_post([
                    'ID' => $post_id,
                    'post_title' => $school_name,
                ]);
                $updated++;
            } else {
                $post_id = wp_insert_post([
                    'post_type' => 'cmn_school',
                    'post_title' => $school_name,
                    'post_status' => 'publish',
                ]);
                if (is_wp_error($post_id)) {
                    $skipped++;
                    continue;
                }
                $created++;
            }
            $domain_owner = $this->get_school_post_id_by_domain($school_domain);
            if ($domain_owner && (int) $domain_owner !== (int) $post_id) {
                $skipped++;
                continue;
            }
            if ($school_code === '') {
                $school_code = get_post_meta($post_id, 'cmn_school_id', true);
                if ($school_code === '') {
                    $school_code = $this->generate_school_id();
                }
            }
            $meta = [
                'cmn_location' => $location,
                'cmn_phone' => $phone,
                'cmn_account_manager' => isset($map['Account Manager']) ? ($row[$map['Account Manager']] ?? '') : '',
                'cmn_cover_manager' => $cover_manager_name,
                'cmn_cover_manager_email' => $cover_manager_email,
                'cmn_email' => $school_email,
                'cmn_email_name' => $email_name,
                'cmn_contact1' => isset($map['Contact Name']) ? ($row[$map['Contact Name']] ?? '') : '',
                'cmn_contact1_email' => isset($map['Contact Email']) ? ($row[$map['Contact Email']] ?? '') : '',
                'cmn_contact2' => isset($map['Contact 2']) ? ($row[$map['Contact 2']] ?? '') : '',
                'cmn_contact2_email' => isset($map['Email2']) ? ($row[$map['Email2']] ?? '') : '',
                'cmn_spoke_to_cm' => isset($map['spoken to cm']) ? ($row[$map['spoken to cm']] ?? '') : '',
                'cmn_switchboard' => isset($map['Switchboard']) ? ($row[$map['Switchboard']] ?? '') : '',
                'cmn_website' => isset($map['Website']) ? ($row[$map['Website']] ?? '') : '',
                'cmn_status' => isset($map['Status']) ? ($row[$map['Status']] ?? '') : '',
                'cmn_pipeline_stage' => isset($map['Pipeline Stage']) ? ($row[$map['Pipeline Stage']] ?? '') : 'new_lead',
                'cmn_school_id' => $school_code,
                'cmn_school_email_domain' => $school_domain,
            ];
            if (trim($meta['cmn_status']) === '') {
                $meta['cmn_status'] = 'lead';
            }
            if ($meta['cmn_status'] === 'client' && $meta['cmn_pipeline_stage'] !== 'lost') {
                $meta['cmn_pipeline_stage'] = $meta['cmn_pipeline_stage'] ?: 'won';
            }
            foreach ($meta as $key => $value) {
                update_post_meta($post_id, $key, sanitize_text_field($value));
            }
            $this->store_cover_manager_split($post_id, $cover_manager_name);
            $this->upsert_school_index($post_id);
            $contact_name = isset($map['Contact Name']) ? trim($row[$map['Contact Name']] ?? '') : '';
            $contact_email = isset($map['Contact Email']) ? trim($row[$map['Contact Email']] ?? '') : '';
            $contact_phone = isset($map['Contact Phone']) ? trim($row[$map['Contact Phone']] ?? '') : '';
            $contact_role = isset($map['Contact Role']) ? trim($row[$map['Contact Role']] ?? '') : '';
            if ($contact_name !== '') {
                $contact_id = $this->create_or_update_contact([
                    'name' => $contact_name,
                    'email' => $contact_email,
                    'phone' => $contact_phone,
                    'role' => $contact_role,
                    'school_id' => $post_id,
                    'school_domain' => $school_domain,
                    'is_primary' => true,
                ]);
                if (!$contact_id) {
                    $contact_warnings++;
                }
                update_post_meta($post_id, 'cmn_primary_contact_name', $contact_name);
                update_post_meta($post_id, 'cmn_primary_contact_email', $contact_email ?: $school_email);
                update_post_meta($post_id, 'cmn_primary_contact_phone', $contact_phone ?: $phone);
                update_post_meta($post_id, 'cmn_primary_contact_role', $contact_role);
            }
            $count += 1;
        }
        fclose($handle);
        $message = 'Imported ' . $count . ' schools (' . $created . ' new, ' . $updated . ' updated).';
        if ($skipped > 0) {
            $message .= ' Skipped ' . $skipped . ' rows with missing or invalid required fields.';
        }
        if ($contact_warnings > 0) {
            $message .= ' Contact warnings: ' . $contact_warnings . '.';
        }
        return $message;
    }

    public function handle_import_schools_portal() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_import_schools_nonce']) || !wp_verify_nonce($_POST['cmn_import_schools_nonce'], 'cmn_import_schools')) {
            wp_die('Invalid request');
        }
        $referer = wp_get_referer() ?: home_url('/portal');
        $step = sanitize_text_field($_POST['cmn_import_step'] ?? '');

        if ($step === 'upload') {
            if (empty($_FILES['cmn_csv']['name'])) {
                wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('No file selected.')], $referer));
                exit;
            }
            $ext = strtolower(pathinfo($_FILES['cmn_csv']['name'], PATHINFO_EXTENSION));
            if ($ext === 'xls') {
                wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('Please upload an .xlsx or .csv file. Save .xls as .xlsx first.')], $referer));
                exit;
            }
            if (!in_array($ext, ['csv', 'xlsx'], true)) {
                wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('Please upload a .csv or .xlsx file.')], $referer));
                exit;
            }
            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            $upload = wp_handle_upload($_FILES['cmn_csv'], ['test_form' => false]);
            if (isset($upload['error'])) {
                wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode($upload['error'])], $referer));
                exit;
            }
            $headers = $this->read_spreadsheet_headers($upload['file'], $ext);
            if (!$headers) {
                wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('Unable to read CSV headers.')], $referer));
                exit;
            }
            $token = wp_generate_password(12, false, false);
            set_transient('cmn_import_' . $token, [
                'user_id' => get_current_user_id(),
                'file' => $upload['file'],
                'headers' => $headers,
                'ext' => $ext,
            ], HOUR_IN_SECONDS);
            wp_redirect(add_query_arg(['view' => 'schools', 'cmn_import_token' => $token], $referer));
            exit;
        }

        if ($step === 'map') {
            $token = sanitize_text_field($_POST['cmn_import_token'] ?? '');
            $data = $token ? get_transient('cmn_import_' . $token) : null;
            if (!$data || (int) ($data['user_id'] ?? 0) !== get_current_user_id()) {
                wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('Import session expired. Please upload the file again.')], $referer));
                exit;
            }
            $map = $_POST['cmn_map'] ?? [];
            if (!is_array($map)) {
                wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('Invalid mapping data.')], $referer));
                exit;
            }
            $required = ['cmn_school_name', 'cmn_location', 'cmn_phone', 'cmn_email', 'cmn_cover_manager', 'cmn_cover_manager_email', 'cmn_email_name'];
            foreach ($required as $key) {
                if (!isset($map[$key]) || $map[$key] === '') {
                    wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('Please map all required fields before importing.')], $referer));
                    exit;
                }
            }
            $ext = $data['ext'] ?? 'csv';
            $message = $this->import_schools_spreadsheet_mapped($data['file'], $map, $ext);
            delete_transient('cmn_import_' . $token);
            if (is_string($data['file']) && file_exists($data['file'])) {
                @unlink($data['file']);
            }
            wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode($message)], $referer));
            exit;
        }

        wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('Invalid import request.')], $referer));
        exit;
    }

    public function handle_add_school_portal() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_add_school_nonce']) || !wp_verify_nonce($_POST['cmn_add_school_nonce'], 'cmn_add_school')) {
            wp_die('Invalid request');
        }
        $school_name = sanitize_text_field($_POST['cmn_school_name'] ?? '');
        $location = sanitize_text_field($_POST['cmn_location'] ?? '');
        $phone = sanitize_text_field($_POST['cmn_phone'] ?? '');
        $email = sanitize_email($_POST['cmn_email'] ?? '');
        $cover_manager_name = sanitize_text_field($_POST['cmn_cover_manager'] ?? '');
        $cover_manager_email = sanitize_email($_POST['cmn_cover_manager_email'] ?? '');
        $email_name = sanitize_text_field($_POST['cmn_email_name'] ?? '');
        if ($school_name === '' || $location === '' || $phone === '' || $email === '') {
            $this->redirect_with_message('Missing required fields. Please add School Name, Location, Contact Number, and School Email.');
        }
        if ($email && !is_email($email)) {
            $this->redirect_with_message('Please enter a valid School Email.');
        }
        if ($cover_manager_name === '' || $cover_manager_email === '' || $email_name === '') {
            $this->redirect_with_message('Cover Manager Name, Cover Manager Email, and Email Name are required.');
        }
        if ($cover_manager_email && !is_email($cover_manager_email)) {
            $this->redirect_with_message('Please enter a valid Cover Manager Email.');
        }
        if (strlen(preg_replace('/\\D+/', '', $phone)) < 7) {
            $this->redirect_with_message('Please enter a valid Contact Number.');
        }
        $school_domain = $this->get_email_domain($email);
        if ($school_domain === '') {
            $this->redirect_with_message('Please enter a valid School Email.');
        }

        $existing_id = $this->get_school_post_id_by_domain($school_domain);
        if ($existing_id) {
            $post_id = $existing_id;
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $school_name,
            ]);
        } else {
            $post_id = wp_insert_post([
                'post_type' => 'cmn_school',
                'post_title' => $school_name,
                'post_status' => 'publish',
            ]);
        }

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, 'cmn_location', $location);
            update_post_meta($post_id, 'cmn_phone', $phone);
            $cover_manager_name = sanitize_text_field($_POST['cmn_cover_manager'] ?? '');
            $cover_manager_email = sanitize_email($_POST['cmn_cover_manager_email'] ?? '');
            update_post_meta($post_id, 'cmn_cover_manager', $cover_manager_name);
            update_post_meta($post_id, 'cmn_cover_manager_email', $cover_manager_email);
            $this->store_cover_manager_split($post_id, $cover_manager_name);
            update_post_meta($post_id, 'cmn_account_manager', sanitize_text_field($_POST['cmn_account_manager'] ?? ''));
            update_post_meta($post_id, 'cmn_email', $email);
            update_post_meta($post_id, 'cmn_email_name', sanitize_text_field($_POST['cmn_email_name'] ?? ''));
            update_post_meta($post_id, 'cmn_spoke_to_cm', sanitize_text_field($_POST['cmn_spoke_to_cm'] ?? ''));
            update_post_meta($post_id, 'cmn_switchboard', sanitize_text_field($_POST['cmn_switchboard'] ?? ''));
            update_post_meta($post_id, 'cmn_website', esc_url_raw($_POST['cmn_website'] ?? ''));
            if (!get_post_meta($post_id, 'cmn_school_id', true)) {
                update_post_meta($post_id, 'cmn_school_id', $this->generate_school_id());
            }
            update_post_meta($post_id, 'cmn_status', 'lead');
            update_post_meta($post_id, 'cmn_pipeline_stage', 'new_lead');
            $school_domain = $this->get_email_domain($email);
            if ($school_domain) {
                update_post_meta($post_id, 'cmn_school_email_domain', $school_domain);
            }

            $contact_name = sanitize_text_field($_POST['cmn_contact_name'] ?? '');
            $contact_email = sanitize_email($_POST['cmn_contact_email'] ?? '');
            $contact_phone = sanitize_text_field($_POST['cmn_contact_phone'] ?? '');
            $contact_role = sanitize_text_field($_POST['cmn_contact_role'] ?? '');
            if ($contact_name !== '') {
                $contact_id = $this->create_or_update_contact([
                    'name' => $contact_name,
                    'email' => $contact_email,
                    'phone' => $contact_phone,
                    'role' => $contact_role,
                    'school_id' => $post_id,
                    'school_domain' => $school_domain,
                    'is_primary' => true,
                ]);
                if ($contact_id) {
                    update_post_meta($post_id, 'cmn_contact1', $contact_name);
                    update_post_meta($post_id, 'cmn_contact1_email', $contact_email);
                    update_post_meta($post_id, 'cmn_contact_role', $contact_role);
                    update_post_meta($post_id, 'cmn_primary_contact_name', $contact_name);
                    update_post_meta($post_id, 'cmn_primary_contact_email', $contact_email ?: $email);
                    update_post_meta($post_id, 'cmn_primary_contact_phone', $contact_phone ?: $phone);
                    update_post_meta($post_id, 'cmn_primary_contact_role', $contact_role);
                }
            }
            $this->upsert_school_index($post_id);
        }

        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        $school_code = get_post_meta($post_id, 'cmn_school_id', true);
        $redirect = add_query_arg(['view' => 'schools', 'school_id' => $school_code], $portal_url);
        wp_redirect($redirect);
        exit;
    }

    public function handle_add_staff_portal() {
        if (!$this->can_manage_staff_users()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_add_staff_nonce']) || !wp_verify_nonce($_POST['cmn_add_staff_nonce'], 'cmn_add_staff')) {
            wp_die('Invalid request');
        }
        $name = sanitize_text_field($_POST['cmn_staff_name'] ?? '');
        $email = sanitize_email($_POST['cmn_staff_email'] ?? '');
        $username_input = sanitize_text_field($_POST['cmn_staff_username'] ?? '');
        $role = sanitize_text_field($_POST['cmn_staff_role'] ?? 'cmn_staff');
        if ($name === '' || $email === '') {
            wp_die('Name and email are required.');
        }
        if (!in_array($role, ['cmn_staff', 'cmn_account_manager', 'cmn_admin'], true)) {
            $role = 'cmn_staff';
        }
        $existing = get_user_by('email', $email);
        if ($existing) {
            $user = new WP_User($existing->ID);
            $user->set_role($role);
            wp_update_user(['ID' => $existing->ID, 'display_name' => $name]);
        } else {
            $base_username = $username_input !== '' ? $username_input : strstr($email, '@', true);
            $username = $this->generate_unique_username($base_username ?: $email);
            $user_id = wp_insert_user([
                'user_login' => $username,
                'user_email' => $email,
                'display_name' => $name,
                'role' => $role,
                'user_pass' => wp_generate_password(12, false),
            ]);
            if (!is_wp_error($user_id)) {
                $user = get_user_by('id', $user_id);
                if ($user) {
                    update_user_meta($user_id, 'cmn_deactivated', 0);
                    $this->send_staff_welcome_email($user);
                }
            }
        }
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        wp_redirect(add_query_arg(['view' => 'staff'], $portal_url));
        exit;
    }

    public function handle_add_staff_user_ajax() {
        if (!check_ajax_referer('cmn_staff_manage', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!$this->can_manage_staff_users()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $username_input = sanitize_text_field($_POST['username'] ?? '');
        $role = sanitize_text_field($_POST['role'] ?? 'cmn_staff');
        if ($name === '' || $email === '') {
            wp_send_json_error(['message' => 'Name and email are required.'], 400);
        }
        if (!in_array($role, ['cmn_staff', 'cmn_account_manager', 'cmn_admin'], true)) {
            $role = 'cmn_staff';
        }
        $existing = get_user_by('email', $email);
        if ($existing) {
            $user = new WP_User($existing->ID);
            $user->set_role($role);
            wp_update_user(['ID' => $existing->ID, 'display_name' => $name]);
            wp_send_json_success([
                'user' => [
                    'id' => $existing->ID,
                    'name' => $name,
                    'username' => $existing->user_login,
                    'email' => $email,
                    'role' => $role,
                    'status' => get_user_meta($existing->ID, 'cmn_deactivated', true) ? 'deactivated' : 'active',
                ],
                'message' => 'Staff updated.',
            ]);
        }
        $base_username = $username_input !== '' ? $username_input : strstr($email, '@', true);
        $username = $this->generate_unique_username($base_username ?: $email);
        $user_id = wp_insert_user([
            'user_login' => $username,
            'user_email' => $email,
            'display_name' => $name,
            'role' => $role,
            'user_pass' => wp_generate_password(12, false),
        ]);
        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()], 400);
        }
        update_user_meta($user_id, 'cmn_deactivated', 0);
        $user = get_user_by('id', $user_id);
        if ($user) {
            $this->send_staff_welcome_email($user);
        }
        wp_send_json_success([
            'user' => [
                'id' => $user_id,
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'role' => $role,
                'status' => 'active',
            ],
            'message' => 'Staff added.',
        ]);
    }

    public function handle_update_staff_user_ajax() {
        if (!check_ajax_referer('cmn_staff_manage', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!$this->can_manage_staff_users()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = intval($_POST['user_id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $role = sanitize_text_field($_POST['role'] ?? 'cmn_staff');
        if (!$user_id || $name === '' || $email === '') {
            wp_send_json_error(['message' => 'Name and email are required.'], 400);
        }
        if (!in_array($role, ['cmn_staff', 'cmn_account_manager', 'cmn_admin'], true)) {
            $role = 'cmn_staff';
        }
        $existing_email_user = get_user_by('email', $email);
        if ($existing_email_user && (int) $existing_email_user->ID !== $user_id) {
            wp_send_json_error(['message' => 'Email already in use.'], 400);
        }
        $user = get_user_by('id', $user_id);
        if (!$user) {
            wp_send_json_error(['message' => 'User not found.'], 404);
        }
        wp_update_user([
            'ID' => $user_id,
            'user_email' => $email,
            'display_name' => $name,
        ]);
        $user->set_role($role);
        wp_send_json_success([
            'user' => [
                'id' => $user_id,
                'name' => $name,
                'username' => $user->user_login,
                'email' => $email,
                'role' => $role,
                'status' => get_user_meta($user_id, 'cmn_deactivated', true) ? 'deactivated' : 'active',
            ],
            'message' => 'Staff updated.',
        ]);
    }

    public function handle_send_staff_reset_password_ajax() {
        if (!check_ajax_referer('cmn_staff_manage', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!$this->can_manage_staff_users()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = intval($_POST['user_id'] ?? 0);
        if (!$user_id) {
            wp_send_json_error(['message' => 'User not found.'], 404);
        }
        $user = get_user_by('id', $user_id);
        if (!$user) {
            wp_send_json_error(['message' => 'User not found.'], 404);
        }
        $sent = $this->send_staff_reset_email($user);
        if (!$sent) {
            wp_send_json_error(['message' => 'Unable to send reset email.'], 500);
        }
        wp_send_json_success(['message' => 'Reset email sent.']);
    }

    public function handle_toggle_staff_deactivated_ajax() {
        if (!check_ajax_referer('cmn_staff_manage', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!$this->can_manage_staff_users()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = intval($_POST['user_id'] ?? 0);
        if (!$user_id) {
            wp_send_json_error(['message' => 'User not found.'], 404);
        }
        if ($this->is_super_admin_user_id($user_id)) {
            wp_send_json_error(['message' => 'Cannot deactivate the super admin.'], 403);
        }
        $current = get_user_meta($user_id, 'cmn_deactivated', true) ? 1 : 0;
        $new_value = $current ? 0 : 1;
        update_user_meta($user_id, 'cmn_deactivated', $new_value);
        wp_send_json_success([
            'status' => $new_value ? 'deactivated' : 'active',
            'message' => $new_value ? 'User deactivated.' : 'User reactivated.',
        ]);
    }

    public function block_deactivated_staff_login($user, $username, $password) {
        if ($user instanceof WP_User) {
            $deactivated = get_user_meta($user->ID, 'cmn_deactivated', true);
            if ($deactivated) {
                return new WP_Error('cmn_deactivated', 'Your account has been deactivated.');
            }
        }
        return $user;
    }

    public function handle_portal_login() {
        if (!isset($_POST['cmn_portal_login_nonce']) || !wp_verify_nonce($_POST['cmn_portal_login_nonce'], 'cmn_portal_login')) {
            wp_redirect(add_query_arg(['view' => 'login', 'cmn_error' => rawurlencode('Invalid request.')], $this->get_portal_base_url()));
            exit;
        }
        $creds = [
            'user_login' => sanitize_text_field($_POST['log'] ?? ''),
            'user_password' => sanitize_text_field($_POST['pwd'] ?? ''),
            'remember' => !empty($_POST['rememberme']),
        ];
        $user = wp_signon($creds, false);
        if (is_wp_error($user)) {
            wp_redirect(add_query_arg(['view' => 'login', 'cmn_error' => rawurlencode($user->get_error_message())], $this->get_portal_base_url()));
            exit;
        }
        $redirect_to = esc_url_raw((string) ($_POST['redirect_to'] ?? ''));
        if ($redirect_to !== '') {
            $base = trailingslashit($this->get_portal_base_url());
            $normalized = trailingslashit(preg_replace('/\?.*/', '', $redirect_to));
            if (strpos($normalized, $base) === 0 || strpos($redirect_to, $this->get_portal_base_url()) === 0) {
                wp_redirect($redirect_to);
                exit;
            }
        }
        $redirect = $this->handle_login_redirect($this->get_portal_base_url(), $this->get_portal_base_url(), $user);
        wp_redirect($redirect);
        exit;
    }

    public function handle_portal_forgot_password() {
        if (!isset($_POST['cmn_portal_forgot_password_nonce']) || !wp_verify_nonce($_POST['cmn_portal_forgot_password_nonce'], 'cmn_portal_forgot_password')) {
            wp_redirect(add_query_arg(['view' => 'forgot-password', 'cmn_error' => rawurlencode('Invalid request.')], $this->get_portal_base_url()));
            exit;
        }
        $user_login = sanitize_text_field($_POST['user_login'] ?? '');
        if ($user_login === '') {
            wp_redirect(add_query_arg(['view' => 'forgot-password', 'cmn_error' => rawurlencode('Enter your email or username.')], $this->get_portal_base_url()));
            exit;
        }
        $result = retrieve_password($user_login);
        if (is_wp_error($result)) {
            wp_redirect(add_query_arg(['view' => 'forgot-password', 'cmn_error' => rawurlencode($result->get_error_message())], $this->get_portal_base_url()));
            exit;
        }
        wp_redirect(add_query_arg(['view' => 'forgot-password', 'cmn_notice' => rawurlencode('Reset link sent. Check your email.')], $this->get_portal_base_url()));
        exit;
    }

    public function handle_portal_reset_password() {
        if (!isset($_POST['cmn_portal_reset_password_nonce']) || !wp_verify_nonce($_POST['cmn_portal_reset_password_nonce'], 'cmn_portal_reset_password')) {
            wp_redirect(add_query_arg(['view' => 'set-password', 'cmn_error' => rawurlencode('Invalid request.')], $this->get_portal_base_url()));
            exit;
        }
        $login = sanitize_text_field($_POST['login'] ?? '');
        $key = sanitize_text_field($_POST['key'] ?? '');
        $pass1 = sanitize_text_field($_POST['pass1'] ?? '');
        $pass2 = sanitize_text_field($_POST['pass2'] ?? '');
        $reset_url = $this->get_portal_reset_url($login, $key);
        if (!$login || !$key) {
            $reset_url = add_query_arg(['view' => 'set-password'], $this->get_portal_base_url());
        }
        if ($pass1 === '' || $pass2 === '' || $pass1 !== $pass2) {
            wp_redirect(add_query_arg(['cmn_error' => rawurlencode('Passwords do not match.')], $reset_url));
            exit;
        }
        if (strlen($pass1) < 10) {
            wp_redirect(add_query_arg(['cmn_error' => rawurlencode('Password must be at least 10 characters.')], $reset_url));
            exit;
        }
        $user = check_password_reset_key($key, $login);
        if (is_wp_error($user)) {
            wp_redirect(add_query_arg(['cmn_error' => rawurlencode($user->get_error_message())], $reset_url));
            exit;
        }
        reset_password($user, $pass1);
        wp_logout();
        $redirect = add_query_arg([
            'view' => 'login',
            'cmn_notice' => rawurlencode('Password set - you can now log in.'),
        ], $this->get_portal_base_url());
        wp_redirect($redirect);
        exit;
    }

    public function handle_support_create_ticket() {
        if (!check_ajax_referer('cmn_support', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        $category = sanitize_text_field($_POST['category'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        if ($subject === '' || $message === '') {
            wp_send_json_error(['message' => 'Subject and message are required.'], 400);
        }
        $ticket_ref = $this->generate_support_ticket_ref();
        $role_type = $this->get_support_user_role_type($user_id);
        global $wpdb;
        $ticket_table = $this->get_support_ticket_table();
        $message_table = $this->get_support_message_table();
        $now = current_time('mysql');
        $inserted = $wpdb->insert($ticket_table, [
            'ticket_ref' => $ticket_ref,
            'created_by_user_id' => $user_id,
            'user_role_type' => $role_type,
            'subject' => $subject,
            'category' => $category ?: null,
            'status' => 'new',
            'is_new_for_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
            'closed_at' => null,
        ], ['%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']);
        if (!$inserted) {
            wp_send_json_error(['message' => 'Unable to create ticket.'], 500);
        }
        $ticket_id = (int) $wpdb->insert_id;
        $uploaded = $this->handle_support_attachments_upload('attachments');
        $attachment_ids = !empty($uploaded['ids']) ? wp_json_encode(array_values(array_unique(array_map('intval', $uploaded['ids'])))) : null;
        $wpdb->insert($message_table, [
            'ticket_id' => $ticket_id,
            'sender_user_id' => $user_id,
            'sender_type' => 'user',
            'message' => $message,
            'attachment_ids' => $attachment_ids,
            'created_at' => $now,
        ], ['%d', '%d', '%s', '%s', '%s', '%s']);

        $this->notify_admins_support($ticket_id, $ticket_ref, $subject);

        $subject_line = 'Support ticket received: ' . $ticket_ref;
        $body = "Thanks for contacting CoverMeNow ONE.\n\nReference: {$ticket_ref}\nSubject: {$subject}\n\nYou can reply inside your portal. We’ll update you shortly.";
        $this->send_support_email_to_user($user_id, $subject_line, $body, 'support_ticket_received');

        wp_send_json_success([
            'ticket' => [
                'id' => $ticket_id,
                'ref' => $ticket_ref,
                'subject' => $subject,
                'status' => 'new',
                'updated_at' => $now,
                'upload_errors' => $uploaded['errors'],
            ],
        ]);
    }

    public function handle_support_list_tickets() {
        if (!check_ajax_referer('cmn_support', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        $default_status = $this->is_staff_user($user_id) ? 'active' : 'all';
        $status = sanitize_key((string) ($_POST['status'] ?? $default_status));
        if (!in_array($status, ['active', 'new', 'open', 'closed', 'new_open', 'all', 'needs_feedback'], true)) {
            $status = $default_status;
        }
        $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
        $ticket_ref = sanitize_text_field((string) ($_POST['ticket_ref'] ?? ''));
        $debug_mode = (isset($_REQUEST['cmn_debug']) && (string) $_REQUEST['cmn_debug'] === '1' && $this->is_admin_user($user_id));
        global $wpdb;
        $table = $this->get_support_ticket_table();
        $columns = $this->get_support_ticket_columns();
        $has_created_by = in_array('created_by_user_id', $columns, true);
        $has_user_id = in_array('user_id', $columns, true);
        $owner_scope = $has_created_by && $has_user_id
            ? 'created_by_or_user'
            : ($has_created_by ? 'created_by' : ($has_user_id ? 'user_id' : 'none'));
        $where = [];
        $params = [];
        if (!$this->is_staff_user()) {
            if ($owner_scope === 'created_by_or_user') {
                $where[] = '(t.created_by_user_id = %d OR t.user_id = %d)';
                $params[] = $user_id;
                $params[] = $user_id;
            } elseif ($owner_scope === 'created_by') {
                $where[] = 't.created_by_user_id = %d';
                $params[] = $user_id;
            } elseif ($owner_scope === 'user_id') {
                $where[] = 't.user_id = %d';
                $params[] = $user_id;
            }
        }
        $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $limit = $this->is_staff_user() ? 200 : 50;
        $feedback_table = $this->get_support_feedback_table();
        $is_new_expr = in_array('is_new_for_admin', $columns, true) ? 't.is_new_for_admin' : '0';
        $sql = "SELECT t.id, t.ticket_ref, t.subject, t.status, {$is_new_expr} AS is_new_for_admin, t.updated_at, (SELECT COUNT(1) FROM {$feedback_table} sf WHERE sf.ticket_id = t.id) AS feedback_count FROM {$table} t {$where_sql} ORDER BY t.updated_at DESC LIMIT {$limit}";
        $prepared = $params ? $wpdb->prepare($sql, $params) : $sql;
        $rows = (array) $wpdb->get_results($prepared, ARRAY_A);
        if (!empty($wpdb->last_error)) {
            error_log('CMN support list query error: ' . $wpdb->last_error);
        }
        $tickets_all = array_map(function ($row) {
            return [
                'id' => (int) $row['id'],
                'ref' => $row['ticket_ref'],
                'subject' => $row['subject'],
                'status' => $this->normalize_support_ticket_for_view($row, $this->is_staff_user())['status'],
                'is_new_for_admin' => isset($row['is_new_for_admin']) ? (int) $row['is_new_for_admin'] : 0,
                'feedback_count' => isset($row['feedback_count']) ? (int) $row['feedback_count'] : 0,
                'updated_at' => $row['updated_at'],
            ];
        }, $rows);
        $dashboard_counts = [
            'all' => count($tickets_all),
            'open' => 0,
            'closed' => 0,
            'needs_feedback' => 0,
        ];
        foreach ($tickets_all as $ticket_row) {
            $ticket_status = (string) ($ticket_row['status'] ?? 'open');
            if (in_array($ticket_status, ['new', 'open'], true)) {
                $dashboard_counts['open']++;
            }
            if ($ticket_status === 'closed') {
                $dashboard_counts['closed']++;
                if ((int) ($ticket_row['feedback_count'] ?? 0) < 1) {
                    $dashboard_counts['needs_feedback']++;
                }
            }
        }
        if ($status === 'all') {
            $tickets = $tickets_all;
        } else {
            $tickets = array_values(array_filter($tickets_all, function ($ticket) use ($status) {
                return $this->support_ticket_matches_filter($ticket, $status);
            }));
        }

        $selected_ticket = $this->resolve_support_requested_ticket($ticket_id, $ticket_ref);
        $forced_filter = '';
        if ($selected_ticket && $this->can_access_support_ticket($selected_ticket, $user_id)) {
            $selected_normalized = $this->normalize_support_ticket_for_view($selected_ticket, $this->is_staff_user());
            $selected_id = (int) $selected_normalized['id'];
            $already_in_list = false;
            foreach ($tickets as $ticket_item) {
                if ((int) ($ticket_item['id'] ?? 0) === $selected_id) {
                    $already_in_list = true;
                    break;
                }
            }
            if (!$already_in_list) {
                $forced_filter = $this->map_support_filter_for_status($selected_normalized['status'] ?? 'open');
                if ($forced_filter !== '' && $forced_filter !== $status) {
                    $status = $forced_filter;
                    if ($status === 'all') {
                        $tickets = $tickets_all;
                    } else {
                        $tickets = array_values(array_filter($tickets_all, function ($ticket) use ($status) {
                            return $this->support_ticket_matches_filter($ticket, $status);
                        }));
                    }
                }
                $already_in_list = false;
                foreach ($tickets as $ticket_item) {
                    if ((int) ($ticket_item['id'] ?? 0) === $selected_id) {
                        $already_in_list = true;
                        break;
                    }
                }
                if (!$already_in_list) {
                    $selected_feedback_count = (int) $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(1) FROM {$feedback_table} WHERE ticket_id = %d",
                        $selected_id
                    ));
                    $tickets = array_merge([[
                        'id' => (int) $selected_normalized['id'],
                        'ref' => (string) ($selected_normalized['ticket_ref'] ?? ''),
                        'subject' => (string) ($selected_normalized['subject'] ?? ''),
                        'status' => (string) ($selected_normalized['status'] ?? 'open'),
                        'is_new_for_admin' => isset($selected_normalized['is_new_for_admin']) ? (int) $selected_normalized['is_new_for_admin'] : 0,
                        'feedback_count' => $selected_feedback_count,
                        'updated_at' => (string) ($selected_normalized['updated_at'] ?? ''),
                    ]], $tickets);
                }
            }
        }

        $feedback_scope_where = [];
        $feedback_scope_params = [];
        if (!$this->is_staff_user()) {
            if ($owner_scope === 'created_by_or_user') {
                $feedback_scope_where[] = '(t.created_by_user_id = %d OR t.user_id = %d)';
                $feedback_scope_params[] = $user_id;
                $feedback_scope_params[] = $user_id;
            } elseif ($owner_scope === 'created_by') {
                $feedback_scope_where[] = 't.created_by_user_id = %d';
                $feedback_scope_params[] = $user_id;
            } elseif ($owner_scope === 'user_id') {
                $feedback_scope_where[] = 't.user_id = %d';
                $feedback_scope_params[] = $user_id;
            }
        }
        $feedback_scope_sql = $feedback_scope_where ? 'WHERE ' . implode(' AND ', $feedback_scope_where) : '';
        $feedback_sql = "SELECT sf.ticket_id, sf.support_rating, sf.response_time_rating, sf.issue_resolved, sf.overall_satisfaction, sf.comments, sf.created_at, t.ticket_ref, t.subject, t.status FROM {$feedback_table} sf INNER JOIN {$table} t ON t.id = sf.ticket_id {$feedback_scope_sql} ORDER BY sf.created_at DESC LIMIT 50";
        $feedback_prepared = $feedback_scope_params ? $wpdb->prepare($feedback_sql, $feedback_scope_params) : $feedback_sql;
        $feedback_rows = (array) $wpdb->get_results($feedback_prepared, ARRAY_A);
        $feedback_avg_total = 0.0;
        $feedback_avg_count = 0;
        $recent_feedback = [];
        foreach ($feedback_rows as $feedback_row) {
            $overall = (int) ($feedback_row['overall_satisfaction'] ?? 0);
            if ($overall > 0) {
                $feedback_avg_total += $overall;
                $feedback_avg_count++;
            }
            $recent_feedback[] = [
                'ticket_id' => (int) ($feedback_row['ticket_id'] ?? 0),
                'ticket_ref' => (string) ($feedback_row['ticket_ref'] ?? ''),
                'subject' => (string) ($feedback_row['subject'] ?? ''),
                'status' => $this->normalize_support_status((string) ($feedback_row['status'] ?? 'open')),
                'support_rating' => (int) ($feedback_row['support_rating'] ?? 0),
                'response_time_rating' => (int) ($feedback_row['response_time_rating'] ?? 0),
                'overall_satisfaction' => $overall,
                'issue_resolved' => (int) ($feedback_row['issue_resolved'] ?? 0),
                'comments' => (string) ($feedback_row['comments'] ?? ''),
                'created_at' => (string) ($feedback_row['created_at'] ?? ''),
            ];
        }

        $response = [
            'tickets' => $tickets,
            'filter' => $status,
            'forced_filter' => $forced_filter,
            'selected_ticket_id' => $selected_ticket ? (int) $selected_ticket['id'] : 0,
            'dashboard' => [
                'counts' => $dashboard_counts,
                'recent_feedback_avg' => $feedback_avg_count > 0 ? round($feedback_avg_total / $feedback_avg_count, 1) : 0,
                'recent_feedback' => array_slice($recent_feedback, 0, 25),
            ],
        ];
        if ($debug_mode) {
            $total_unfiltered = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
            $sample_query = "SELECT * FROM {$table} ORDER BY updated_at DESC LIMIT 5";
            $sample_rows = (array) $wpdb->get_results($sample_query, ARRAY_A);
            $samples = [];
            foreach ($sample_rows as $sample) {
                $samples[] = [
                    'ref' => (string) ($sample['ticket_ref'] ?? ''),
                    'status_raw' => (string) ($sample['status'] ?? ''),
                    'status_normalized' => $this->normalize_support_status((string) ($sample['status'] ?? '')),
                    'created_by_user_id' => isset($sample['created_by_user_id']) ? (int) $sample['created_by_user_id'] : null,
                    'user_id' => isset($sample['user_id']) ? (int) $sample['user_id'] : null,
                    'user_role_type' => (string) ($sample['user_role_type'] ?? ''),
                ];
            }
            $response['debug'] = [
                'current_user_id' => $user_id,
                'roles' => wp_get_current_user() ? (array) wp_get_current_user()->roles : [],
                'status_filter' => $status,
                'owner_scope' => $owner_scope,
                'query_sql' => $prepared,
                'query_where' => $where,
                'query_params' => $params,
                'total_count_unfiltered' => $total_unfiltered,
                'total_count_visible' => count($tickets_all),
                'total_count_filtered' => count($tickets),
                'sample_rows' => $samples,
                'selected_ticket_ref' => $selected_ticket ? (string) ($selected_ticket['ticket_ref'] ?? '') : '',
                'selected_ticket_status' => $selected_ticket ? $this->normalize_support_status((string) ($selected_ticket['status'] ?? '')) : '',
            ];
        }
        wp_send_json_success($response);
    }

    public function handle_support_list_tickets_unfiltered_admin() {
        if (!check_ajax_referer('cmn_support', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in() || !$this->is_admin_user()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        global $wpdb;
        $table = $this->get_support_ticket_table();
        $rows = (array) $wpdb->get_results("SELECT * FROM {$table} ORDER BY updated_at DESC LIMIT 20", ARRAY_A);
        if (!empty($wpdb->last_error)) {
            error_log('CMN support unfiltered query error: ' . $wpdb->last_error);
        }
        $payload = [];
        foreach ($rows as $row) {
            $payload[] = [
                'id' => (int) ($row['id'] ?? 0),
                'ref' => (string) ($row['ticket_ref'] ?? ''),
                'status_raw' => (string) ($row['status'] ?? ''),
                'status_normalized' => $this->normalize_support_status((string) ($row['status'] ?? '')),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
                'created_by_user_id' => isset($row['created_by_user_id']) ? (int) $row['created_by_user_id'] : null,
                'user_id' => isset($row['user_id']) ? (int) $row['user_id'] : null,
                'user_role_type' => (string) ($row['user_role_type'] ?? ''),
            ];
        }
        wp_send_json_success([
            'tickets' => $payload,
            'count' => count($payload),
        ]);
    }

    public function handle_support_get_ticket() {
        if (!check_ajax_referer('cmn_support', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $ticket_id = intval($_POST['ticket_id'] ?? 0);
        $ticket_ref = sanitize_text_field($_POST['ticket_ref'] ?? '');
        $ticket = null;
        if ($ticket_id) {
            $ticket = $this->get_support_ticket($ticket_id);
        } elseif ($ticket_ref !== '') {
            $ticket = $this->get_support_ticket_by_ref($ticket_ref);
        }
        if (!$ticket) {
            wp_send_json_error(['message' => 'Ticket not found.'], 404);
        }
        $ticket_id = (int) $ticket['id'];
        if (!$this->can_access_support_ticket($ticket)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        if ($this->is_staff_user()) {
            global $wpdb;
            $ticket_table = $this->get_support_ticket_table();
            $wpdb->update($ticket_table, [
                'is_new_for_admin' => 0,
            ], [
                'id' => $ticket_id,
            ], ['%d'], ['%d']);
            $ticket['is_new_for_admin'] = 0;
        }
        $ticket = $this->normalize_support_ticket_for_view($ticket, $this->is_staff_user());
        $messages = $this->get_support_ticket_messages($ticket_id);
        $formatted = [];
        foreach ($messages as $msg) {
            $sender_name = 'Support';
            if (!empty($msg['sender_user_id'])) {
                $user = get_user_by('id', (int) $msg['sender_user_id']);
                if ($user) {
                    $sender_name = $user->display_name ?: $user->user_login;
                }
            }
            $formatted[] = [
                'id' => (int) $msg['id'],
                'sender_type' => $msg['sender_type'],
                'sender_name' => $sender_name,
                'message' => $msg['message'],
                'attachments' => $this->get_support_message_attachments($msg),
                'created_at' => $msg['created_at'],
            ];
        }
        $feedback = $this->get_support_feedback($ticket_id, $this->is_staff_user() ? 0 : get_current_user_id());
        wp_send_json_success([
            'ticket' => $ticket,
            'messages' => $formatted,
            'feedback' => $feedback ?: null,
        ]);
    }

    public function handle_support_post_message() {
        if (!check_ajax_referer('cmn_support', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $user_id = get_current_user_id();
        $ticket_id = intval($_POST['ticket_id'] ?? 0);
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        if (!$ticket_id || $message === '') {
            wp_send_json_error(['message' => 'Message is required.'], 400);
        }
        $ticket = $this->get_support_ticket($ticket_id);
        if (!$this->can_access_support_ticket($ticket)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $sender_type = $this->is_staff_user() ? 'admin' : 'user';
        global $wpdb;
        $message_table = $this->get_support_message_table();
        $now = current_time('mysql');
        $uploaded = $this->handle_support_attachments_upload('attachments');
        $attachment_ids = !empty($uploaded['ids']) ? wp_json_encode(array_values(array_unique(array_map('intval', $uploaded['ids'])))) : null;
        $wpdb->insert($message_table, [
            'ticket_id' => $ticket_id,
            'sender_user_id' => $user_id,
            'sender_type' => $sender_type,
            'message' => $message,
            'attachment_ids' => $attachment_ids,
            'created_at' => $now,
        ], ['%d', '%d', '%s', '%s', '%s', '%s']);

        $ticket_table = $this->get_support_ticket_table();
        $new_status = 'open';
        $wpdb->update($ticket_table, [
            'status' => $new_status,
            'is_new_for_admin' => $sender_type === 'admin' ? 0 : 1,
            'updated_at' => $now,
            'closed_at' => null,
        ], ['id' => $ticket_id], ['%s', '%d', '%s', '%s'], ['%d']);

        if ($sender_type === 'admin') {
            $owner_id = (int) $this->get_support_owner_user_id($ticket);
            $subject_line = 'Update on your support ticket: ' . $ticket['ticket_ref'];
            $portal_link = $this->get_support_link_for_user($owner_id, $ticket_id, $ticket['ticket_ref'] ?? '');
            $body = "We’ve replied to your support ticket.\n\n{$message}\n\nYou can reply in your portal:\n{$portal_link}";
            $this->send_support_email_to_user($owner_id, $subject_line, $body, 'support_ticket_reply');
            $this->add_notification($owner_id, 'support_reply', 'Support ticket reply', $ticket['ticket_ref'] . ' updated.', $portal_link);
        } else {
            $this->notify_admins_support_reply($ticket_id, $ticket['ticket_ref']);
        }

        wp_send_json_success([
            'message' => 'Message sent.',
            'status' => $new_status,
            'upload_errors' => $uploaded['errors'],
        ]);
    }

    public function handle_support_close_ticket() {
        if (!check_ajax_referer('cmn_support', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $ticket_id = intval($_POST['ticket_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? 'closed');
        if (!$ticket_id) {
            wp_send_json_error(['message' => 'Ticket not found.'], 404);
        }
        if (!in_array($status, ['closed', 'open'], true)) {
            $status = 'closed';
        }
        global $wpdb;
        $table = $this->get_support_ticket_table();
        $ticket = $this->get_support_ticket($ticket_id);
        if (!$ticket) {
            wp_send_json_error(['message' => 'Ticket not found.'], 404);
        }
        $current_user = get_current_user_id();
        $is_staff = $this->is_staff_user($current_user);
        $is_owner = ((int) $this->get_support_owner_user_id($ticket) === (int) $current_user);
        if (!$is_staff && !$is_owner) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        if (!$is_staff && $status !== 'open') {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $owner_id = (int) $this->get_support_owner_user_id($ticket);
        $ticket_ref = (string) ($ticket['ticket_ref'] ?? '');
        $now = current_time('mysql');
        $target_status = $status === 'closed' ? 'closed' : 'open';
        $current_status = $this->normalize_support_status($ticket['status'] ?? '');
        if ($current_status === $target_status) {
            wp_send_json_success(['status' => $target_status, 'unchanged' => true]);
        }
        $wpdb->update($table, [
            'status' => $target_status,
            'updated_at' => $now,
            'closed_at' => $status === 'closed' ? $now : null,
            'is_new_for_admin' => 0,
        ], ['id' => $ticket_id], ['%s', '%s', '%s', '%d'], ['%d']);
        if ($is_staff) {
            if ($target_status === 'closed') {
                $this->insert_support_system_message_once($ticket_id, 'Ticket closed by support.', $current_user, 'admin');
            } else {
                $this->insert_support_system_message_once($ticket_id, 'Ticket reopened by support.', $current_user, 'admin');
            }
        }
        if ($owner_id) {
            $owner_link = $this->get_support_link_for_user($owner_id, $ticket_id, $ticket_ref);
            if ($target_status === 'closed') {
                $this->add_notification($owner_id, 'support_ticket_closed', 'Ticket closed', $ticket_ref . ' has been closed.', $owner_link);
            } else {
                $this->add_notification($owner_id, 'support_ticket_reopened', 'Ticket reopened', $ticket_ref . ' has been reopened.', $owner_link);
            }
        }
        if ($target_status === 'open' && !$is_staff) {
            foreach ($this->get_admin_users_for_support() as $admin_id) {
                $admin_link = $this->get_support_link_for_user($admin_id, $ticket_id, $ticket_ref);
                $this->add_notification($admin_id, 'support_ticket_reopened', 'Ticket reopened', $ticket_ref . ' was reopened by the ticket owner.', $admin_link);
            }
        }
        wp_send_json_success(['status' => $target_status]);
    }

    public function handle_support_submit_feedback() {
        if (!check_ajax_referer('cmn_support', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
        if (!$ticket_id) {
            wp_send_json_error(['message' => 'Ticket not found.'], 404);
        }
        $ticket = $this->get_support_ticket($ticket_id);
        if (!$ticket || !$this->can_access_support_ticket($ticket)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        if ($this->normalize_support_status($ticket['status'] ?? '') !== 'closed') {
            wp_send_json_error(['message' => 'Feedback is available only for closed tickets.'], 400);
        }
        $user_id = get_current_user_id();
        if ($this->is_staff_user($user_id)) {
            wp_send_json_error(['message' => 'Only ticket owner can submit feedback.'], 403);
        }
        $support_rating = max(1, min(5, (int) ($_POST['support_rating'] ?? 0)));
        $response_time_rating = max(1, min(5, (int) ($_POST['response_time_rating'] ?? 0)));
        $overall_satisfaction = max(1, min(5, (int) ($_POST['overall_satisfaction'] ?? 0)));
        $issue_resolved = !empty($_POST['issue_resolved']) && (string) $_POST['issue_resolved'] !== '0' ? 1 : 0;
        $comments = sanitize_textarea_field((string) ($_POST['comments'] ?? ''));
        if ($support_rating < 1 || $response_time_rating < 1 || $overall_satisfaction < 1) {
            wp_send_json_error(['message' => 'Please complete all ratings.'], 400);
        }
        global $wpdb;
        $table = $this->get_support_feedback_table();
        $now = current_time('mysql');
        $wpdb->replace($table, [
            'ticket_id' => $ticket_id,
            'user_id' => $user_id,
            'support_rating' => $support_rating,
            'response_time_rating' => $response_time_rating,
            'issue_resolved' => $issue_resolved,
            'overall_satisfaction' => $overall_satisfaction,
            'comments' => $comments,
            'created_at' => $now,
        ], ['%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s']);
        $ticket_ref = (string) ($ticket['ticket_ref'] ?? '');
        foreach ($this->get_admin_users_for_support() as $admin_id) {
            $admin_link = $this->get_support_link_for_user($admin_id, $ticket_id, $ticket_ref);
            $this->add_notification(
                (int) $admin_id,
                'support_feedback',
                'Support feedback',
                ($ticket_ref ?: 'Ticket') . ' feedback submitted.',
                $admin_link
            );
        }
        wp_send_json_success([
            'message' => 'Feedback saved.',
            'feedback' => $this->get_support_feedback($ticket_id, $user_id),
        ]);
    }

    public function handle_support_save_transcript() {
        if (!check_ajax_referer('cmn_support', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
        if (!$ticket_id) {
            wp_send_json_error(['message' => 'Ticket not found.'], 404);
        }
        $ticket = $this->get_support_ticket($ticket_id);
        if (!$ticket || !$this->can_access_support_ticket($ticket)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $messages = $this->get_support_ticket_messages($ticket_id);
        $formatted = [];
        foreach ($messages as $msg) {
            $sender_name = 'Support';
            if (!empty($msg['sender_user_id'])) {
                $usr = get_user_by('id', (int) $msg['sender_user_id']);
                if ($usr) {
                    $sender_name = $usr->display_name ?: $usr->user_login;
                }
            }
            $formatted[] = [
                'sender_name' => $sender_name,
                'message' => (string) ($msg['message'] ?? ''),
                'created_at' => (string) ($msg['created_at'] ?? ''),
                'attachments' => $this->get_support_message_attachments($msg),
            ];
        }
        $transcript_text = $this->build_support_transcript_text($ticket, $formatted);
        global $wpdb;
        $table = $this->get_support_transcript_table();
        $wpdb->insert($table, [
            'ticket_id' => $ticket_id,
            'created_by' => get_current_user_id(),
            'transcript_text' => $transcript_text,
            'created_at' => current_time('mysql'),
        ], ['%d', '%d', '%s', '%s']);
        wp_send_json_success([
            'message' => 'Transcript saved.',
            'transcript' => $transcript_text,
        ]);
    }

    public function handle_support_email_transcript() {
        if (!check_ajax_referer('cmn_support', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid request.'], 403);
        }
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
        if (!$ticket_id) {
            wp_send_json_error(['message' => 'Ticket not found.'], 404);
        }
        $ticket = $this->get_support_ticket($ticket_id);
        if (!$ticket || !$this->can_access_support_ticket($ticket)) {
            wp_send_json_error(['message' => 'Unauthorized.'], 403);
        }
        $owner_id = (int) ($ticket['created_by_user_id'] ?? 0);
        $owner = $owner_id ? get_user_by('id', $owner_id) : null;
        if (!$owner || !is_email($owner->user_email)) {
            wp_send_json_error(['message' => 'Ticket owner email not found.'], 400);
        }
        $messages = $this->get_support_ticket_messages($ticket_id);
        $formatted = [];
        foreach ($messages as $msg) {
            $sender_name = 'Support';
            if (!empty($msg['sender_user_id'])) {
                $usr = get_user_by('id', (int) $msg['sender_user_id']);
                if ($usr) {
                    $sender_name = $usr->display_name ?: $usr->user_login;
                }
            }
            $formatted[] = [
                'sender_name' => $sender_name,
                'message' => (string) ($msg['message'] ?? ''),
                'created_at' => (string) ($msg['created_at'] ?? ''),
                'attachments' => $this->get_support_message_attachments($msg),
            ];
        }
        $transcript_text = $this->build_support_transcript_text($ticket, $formatted);
        $subject = 'Support transcript: ' . (string) ($ticket['ticket_ref'] ?? 'CMN Ticket');
        $sent = $this->send_support_email_to_user($owner_id, $subject, $transcript_text, 'support_transcript');
        global $wpdb;
        $table = $this->get_support_transcript_email_table();
        $wpdb->insert($table, [
            'ticket_id' => $ticket_id,
            'created_by' => get_current_user_id(),
            'to_email' => $owner->user_email,
            'status' => $sent ? 'sent' : 'failed',
            'error_message' => $sent ? null : 'Unable to send transcript.',
            'created_at' => current_time('mysql'),
        ], ['%d', '%d', '%s', '%s', '%s', '%s']);
        if (!$sent) {
            wp_send_json_error(['message' => 'Unable to send transcript email.'], 500);
        }
        wp_send_json_success(['message' => 'Transcript emailed to ticket owner.']);
    }

    public function handle_add_contact_portal() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_add_contact_nonce']) || !wp_verify_nonce($_POST['cmn_add_contact_nonce'], 'cmn_add_contact')) {
            wp_die('Invalid request');
        }
        $name = sanitize_text_field($_POST['cmn_contact_name'] ?? '');
        $email = sanitize_email($_POST['cmn_contact_email'] ?? '');
        $phone = sanitize_text_field($_POST['cmn_contact_phone'] ?? '');
        $role = sanitize_text_field($_POST['cmn_contact_role'] ?? '');
        $primary_domain = sanitize_text_field($_POST['cmn_contact_primary_school_domain'] ?? '');
        $additional_domains = isset($_POST['cmn_contact_school_domains']) && is_array($_POST['cmn_contact_school_domains'])
            ? array_map('sanitize_text_field', $_POST['cmn_contact_school_domains'])
            : [];
        $user_id = get_current_user_id();
        if ($this->is_account_manager_user($user_id) && !$this->is_admin_user($user_id) && !$this->is_staff_role($user_id)) {
            $allowed_domains = [];
            foreach ($this->get_assigned_school_ids_for_account_manager($user_id) as $school_id) {
                $domain = get_post_meta($school_id, 'cmn_school_email_domain', true);
                if ($domain) {
                    $allowed_domains[] = $domain;
                }
            }
            $allowed_domains = array_unique($allowed_domains);
            if ($primary_domain && !in_array($primary_domain, $allowed_domains, true)) {
                $primary_domain = '';
            }
            if ($additional_domains) {
                $additional_domains = array_values(array_intersect($additional_domains, $allowed_domains));
            }
        }
        if ($name === '') {
            wp_die('Contact name is required.');
        }
        if ($email && !is_email($email)) {
            wp_die('Invalid email address.');
        }
        $contact_id = wp_insert_post([
            'post_type' => 'cmn_contact',
            'post_title' => $name,
            'post_status' => 'publish',
        ]);
        if (!is_wp_error($contact_id)) {
            update_post_meta($contact_id, 'cmn_contact_name', $name);
            update_post_meta($contact_id, 'cmn_contact_email', $email);
            update_post_meta($contact_id, 'cmn_contact_phone', $phone);
            update_post_meta($contact_id, 'cmn_contact_role', $role);
            if ($primary_domain) {
                $this->link_contact_to_school($contact_id, $primary_domain, true);
                $primary_post_id = $this->get_school_post_id_by_domain($primary_domain);
                if ($primary_post_id) {
                    update_post_meta($contact_id, 'cmn_contact_school_id', $primary_post_id);
                }
            }
            if ($additional_domains) {
                foreach ($additional_domains as $domain) {
                    if (!$domain || $domain === $primary_domain) {
                        continue;
                    }
                    $this->link_contact_to_school($contact_id, $domain, false);
                }
            }
        }
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode('Contact saved.')], $portal_url));
        exit;
    }

    public function handle_update_contact_portal() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_update_contact_nonce']) || !wp_verify_nonce($_POST['cmn_update_contact_nonce'], 'cmn_update_contact')) {
            wp_die('Invalid request');
        }
        $contact_id = intval($_POST['cmn_contact_id'] ?? 0);
        if (!$contact_id) {
            wp_die('Invalid contact.');
        }
        $user_id = get_current_user_id();
        if ($this->is_account_manager_user($user_id) && !$this->is_admin_user($user_id) && !$this->is_staff_role($user_id)) {
            $allowed_domains = [];
            foreach ($this->get_assigned_school_ids_for_account_manager($user_id) as $school_id) {
                $domain = get_post_meta($school_id, 'cmn_school_email_domain', true);
                if ($domain) {
                    $allowed_domains[] = $domain;
                }
            }
            $allowed_domains = array_unique($allowed_domains);
            $links = $this->get_contact_school_links($contact_id);
            $contact_domains = [];
            foreach ($links as $link) {
                if (!empty($link['school_email_domain'])) {
                    $contact_domains[] = $link['school_email_domain'];
                }
            }
            if (!$allowed_domains || !array_intersect($allowed_domains, $contact_domains)) {
                wp_die('Unauthorized');
            }
        }
        $name = sanitize_text_field($_POST['cmn_contact_name'] ?? '');
        $email = sanitize_email($_POST['cmn_contact_email'] ?? '');
        $phone = sanitize_text_field($_POST['cmn_contact_phone'] ?? '');
        $role = sanitize_text_field($_POST['cmn_contact_role'] ?? '');
        $primary_domain = sanitize_text_field($_POST['cmn_contact_primary_school_domain'] ?? '');
        $additional_domains = isset($_POST['cmn_contact_school_domains']) && is_array($_POST['cmn_contact_school_domains'])
            ? array_map('sanitize_text_field', $_POST['cmn_contact_school_domains'])
            : [];
        $user_id = get_current_user_id();
        if ($this->is_account_manager_user($user_id) && !$this->is_admin_user($user_id) && !$this->is_staff_role($user_id)) {
            $allowed_domains = [];
            foreach ($this->get_assigned_school_ids_for_account_manager($user_id) as $school_id) {
                $domain = get_post_meta($school_id, 'cmn_school_email_domain', true);
                if ($domain) {
                    $allowed_domains[] = $domain;
                }
            }
            $allowed_domains = array_unique($allowed_domains);
            if ($primary_domain && !in_array($primary_domain, $allowed_domains, true)) {
                $primary_domain = '';
            }
            if ($additional_domains) {
                $additional_domains = array_values(array_intersect($additional_domains, $allowed_domains));
            }
        }
        if ($name === '') {
            wp_die('Contact name is required.');
        }
        if ($email && !is_email($email)) {
            wp_die('Invalid email address.');
        }
        wp_update_post([
            'ID' => $contact_id,
            'post_title' => $name,
        ]);
        update_post_meta($contact_id, 'cmn_contact_name', $name);
        update_post_meta($contact_id, 'cmn_contact_email', $email);
        update_post_meta($contact_id, 'cmn_contact_phone', $phone);
        update_post_meta($contact_id, 'cmn_contact_role', $role);
        $this->clear_contact_links($contact_id);
        if ($primary_domain) {
            $this->link_contact_to_school($contact_id, $primary_domain, true);
            $primary_post_id = $this->get_school_post_id_by_domain($primary_domain);
            if ($primary_post_id) {
                update_post_meta($contact_id, 'cmn_contact_school_id', $primary_post_id);
            }
        } else {
            delete_post_meta($contact_id, 'cmn_contact_school_id');
        }
        if ($additional_domains) {
            foreach ($additional_domains as $domain) {
                if (!$domain || $domain === $primary_domain) {
                    continue;
                }
                $this->link_contact_to_school($contact_id, $domain, false);
            }
        }
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode('Contact updated.')], $portal_url));
        exit;
    }

    public function handle_delete_contact_portal() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_delete_contact_nonce']) || !wp_verify_nonce($_POST['cmn_delete_contact_nonce'], 'cmn_delete_contact')) {
            wp_die('Invalid request');
        }
        $contact_id = intval($_POST['cmn_contact_id'] ?? 0);
        $user_id = get_current_user_id();
        if ($contact_id && $this->is_account_manager_user($user_id) && !$this->is_admin_user($user_id) && !$this->is_staff_role($user_id)) {
            $allowed_domains = [];
            foreach ($this->get_assigned_school_ids_for_account_manager($user_id) as $school_id) {
                $domain = get_post_meta($school_id, 'cmn_school_email_domain', true);
                if ($domain) {
                    $allowed_domains[] = $domain;
                }
            }
            $allowed_domains = array_unique($allowed_domains);
            $links = $this->get_contact_school_links($contact_id);
            $contact_domains = [];
            foreach ($links as $link) {
                if (!empty($link['school_email_domain'])) {
                    $contact_domains[] = $link['school_email_domain'];
                }
            }
            if (!$allowed_domains || !array_intersect($allowed_domains, $contact_domains)) {
                wp_die('Unauthorized');
            }
        }
        if ($contact_id) {
            wp_delete_post($contact_id, true);
        }
        $portal_page = get_page_by_title('Portal');
        $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
        wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode('Contact deleted.')], $portal_url));
        exit;
    }

    public function handle_import_contacts_portal() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_import_contacts_nonce']) || !wp_verify_nonce($_POST['cmn_import_contacts_nonce'], 'cmn_import_contacts')) {
            wp_die('Invalid request');
        }
        $referer = wp_get_referer() ?: home_url('/portal');
        $step = sanitize_text_field($_POST['cmn_import_step'] ?? '');

        if ($step === 'upload') {
            if (empty($_FILES['cmn_contacts_file']['name'])) {
                wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode('No file selected.')], $referer));
                exit;
            }
            $ext = strtolower(pathinfo($_FILES['cmn_contacts_file']['name'], PATHINFO_EXTENSION));
            if ($ext === 'xls') {
                wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode('Please upload an .xlsx or .csv file. Save .xls as .xlsx first.')], $referer));
                exit;
            }
            if (!in_array($ext, ['csv', 'xlsx'], true)) {
                wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode('Please upload a .csv or .xlsx file.')], $referer));
                exit;
            }
            if (!function_exists('wp_handle_upload')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            $upload = wp_handle_upload($_FILES['cmn_contacts_file'], ['test_form' => false]);
            if (isset($upload['error'])) {
                wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode($upload['error'])], $referer));
                exit;
            }
            $headers = $this->read_spreadsheet_headers($upload['file'], $ext);
            if (!$headers) {
                wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode('Unable to read CSV headers.')], $referer));
                exit;
            }
            $token = wp_generate_password(12, false, false);
            set_transient('cmn_contact_import_' . $token, [
                'user_id' => get_current_user_id(),
                'file' => $upload['file'],
                'headers' => $headers,
                'ext' => $ext,
            ], HOUR_IN_SECONDS);
            wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_import_token' => $token], $referer));
            exit;
        }

        if ($step === 'map') {
            $token = sanitize_text_field($_POST['cmn_contact_import_token'] ?? '');
            $data = $token ? get_transient('cmn_contact_import_' . $token) : null;
            if (!$data || (int) ($data['user_id'] ?? 0) !== get_current_user_id()) {
                wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode('Import session expired. Please upload the file again.')], $referer));
                exit;
            }
            $map = $_POST['cmn_contact_map'] ?? [];
            if (!is_array($map)) {
                wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode('Invalid mapping data.')], $referer));
                exit;
            }
            if (!isset($map['cmn_contact_name']) || $map['cmn_contact_name'] === '') {
                wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode('Please map the required Contact Name field.')], $referer));
                exit;
            }
            $ext = $data['ext'] ?? 'csv';
            $message = $this->import_contacts_spreadsheet_mapped($data['file'], $map, $ext);
            delete_transient('cmn_contact_import_' . $token);
            if (is_string($data['file']) && file_exists($data['file'])) {
                @unlink($data['file']);
            }
            wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode($message)], $referer));
            exit;
        }

        wp_redirect(add_query_arg(['view' => 'contacts', 'cmn_contact_msg' => rawurlencode('Invalid import request.')], $referer));
        exit;
    }

    public function handle_bulk_schools() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_bulk_schools_nonce']) || !wp_verify_nonce($_POST['cmn_bulk_schools_nonce'], 'cmn_bulk_schools')) {
            wp_die('Invalid request');
        }
        $action = sanitize_text_field($_POST['cmn_bulk_action'] ?? '');
        $ids = isset($_POST['cmn_school_ids']) && is_array($_POST['cmn_school_ids']) ? array_map('intval', $_POST['cmn_school_ids']) : [];
        $redirect = esc_url_raw($_POST['cmn_redirect'] ?? '') ?: (wp_get_referer() ?: home_url('/portal'));

        if ($action === '' || $action === 'select') {
            wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('Select a bulk action first.')], $redirect));
            exit;
        }
        if (empty($ids)) {
            wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('Select at least one school.')], $redirect));
            exit;
        }
        if ($action === 'delete') {
            $deleted = 0;
            foreach ($ids as $id) {
                if (!$id) {
                    continue;
                }
                $post = get_post($id);
                if (!$post || $post->post_type !== 'cmn_school') {
                    continue;
                }
                wp_delete_post($id, true);
                $deleted++;
            }
            wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('Deleted ' . $deleted . ' school(s).')], $redirect));
            exit;
        }

        wp_redirect(add_query_arg(['view' => 'schools', 'cmn_imported' => '1', 'cmn_import_msg' => rawurlencode('Unknown bulk action.')], $redirect));
        exit;
    }

    public function handle_assign_contact_to_school() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_assign_contact_nonce']) || !wp_verify_nonce($_POST['cmn_assign_contact_nonce'], 'cmn_assign_contact')) {
            wp_die('Invalid request');
        }
        $contact_id = intval($_POST['cmn_contact_id'] ?? 0);
        $school_domain = sanitize_text_field($_POST['cmn_school_domain'] ?? '');
        if ($school_domain && !$this->user_can_access_school($school_domain)) {
            wp_die('Unauthorized');
        }
        if ($contact_id && $school_domain) {
            $is_primary = $this->get_contact_primary_school_id($contact_id) === '';
            $this->link_contact_to_school($contact_id, $school_domain, $is_primary);
            if ($is_primary) {
                $primary_post = $this->get_school_post_id_by_domain($school_domain);
                if ($primary_post) {
                    update_post_meta($contact_id, 'cmn_contact_school_id', $primary_post);
                }
            }
        }
        $redirect = esc_url_raw($_POST['cmn_redirect'] ?? '');
        if ($redirect) {
            wp_redirect($redirect);
        } else {
            $portal_page = get_page_by_title('Portal');
            $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
            wp_redirect(add_query_arg(['view' => 'schools'], $portal_url));
        }
        exit;
    }

    public function handle_unassign_contact() {
        if (!$this->is_staff_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_unassign_contact_nonce']) || !wp_verify_nonce($_POST['cmn_unassign_contact_nonce'], 'cmn_unassign_contact')) {
            wp_die('Invalid request');
        }
        $contact_id = intval($_POST['cmn_contact_id'] ?? 0);
        $school_domain = sanitize_text_field($_POST['cmn_school_domain'] ?? '');
        if ($school_domain && !$this->user_can_access_school($school_domain)) {
            wp_die('Unauthorized');
        }
        if ($contact_id && $school_domain) {
            $this->unlink_contact_from_school($contact_id, $school_domain);
            $primary = $this->get_contact_primary_school_id($contact_id);
            if ($primary === '') {
                delete_post_meta($contact_id, 'cmn_contact_school_id');
            }
        }
        $redirect = esc_url_raw($_POST['cmn_redirect'] ?? '');
        if ($redirect) {
            wp_redirect($redirect);
        } else {
            $portal_page = get_page_by_title('Portal');
            $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
            wp_redirect(add_query_arg(['view' => 'schools'], $portal_url));
        }
        exit;
    }

    public function handle_assign_account_manager() {
        if (!$this->is_admin_user()) {
            wp_die('Unauthorized');
        }
        if (!isset($_POST['cmn_assign_account_manager_nonce']) || !wp_verify_nonce($_POST['cmn_assign_account_manager_nonce'], 'cmn_assign_account_manager')) {
            wp_die('Invalid request');
        }
        $school_id = intval($_POST['cmn_school_id'] ?? 0);
        $manager_id = intval($_POST['cmn_account_manager_user'] ?? 0);
        if (!$school_id) {
            wp_die('Invalid school.');
        }
        if ($manager_id) {
            $manager = get_user_by('id', $manager_id);
            if ($manager) {
                update_post_meta($school_id, 'cmn_account_manager_user', $manager_id);
                update_post_meta($school_id, 'cmn_account_manager', $manager->display_name);
            }
        } else {
            delete_post_meta($school_id, 'cmn_account_manager_user');
        }
        $redirect = esc_url_raw($_POST['cmn_redirect'] ?? '');
        if ($redirect) {
            wp_redirect($redirect);
        } else {
            $portal_page = get_page_by_title('Portal');
            $portal_url = $portal_page ? get_permalink($portal_page) : home_url('/portal');
            $school_code = get_post_meta($school_id, 'cmn_school_id', true);
            wp_redirect(add_query_arg(['view' => 'schools', 'school_id' => $school_code], $portal_url));
        }
        exit;
    }

    private function read_csv_headers($file) {
        $handle = fopen($file, 'r');
        if (!$handle) {
            return [];
        }
        $header = fgetcsv($handle);
        fclose($handle);
        if (!$header) {
            return [];
        }
        return array_map('trim', $header);
    }

    private function read_spreadsheet_headers($file, $ext) {
        $ext = strtolower($ext);
        if ($ext === 'xlsx') {
            $rows = $this->read_xlsx_rows($file);
            if (!$rows) {
                return [];
            }
            $header = $rows[0] ?? [];
            return array_map('trim', array_map('strval', $header));
        }
        return $this->read_csv_headers($file);
    }

    private function read_spreadsheet_rows($file, $ext) {
        $ext = strtolower($ext);
        if ($ext === 'xlsx') {
            return $this->read_xlsx_rows($file);
        }
        return $this->read_csv_rows($file);
    }

    private function read_csv_rows($file) {
        $handle = fopen($file, 'r');
        if (!$handle) {
            return [];
        }
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    private function read_xlsx_rows($file) {
        if (!class_exists('ZipArchive')) {
            return [];
        }
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            return [];
        }
        $shared = $this->read_xlsx_shared_strings($zip);
        $sheetXml = $this->read_xlsx_sheet_xml($zip);
        if (!$sheetXml) {
            $zip->close();
            return [];
        }
        $xml = simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData)) {
            $zip->close();
            return [];
        }
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                $type = (string) $c['t'];
                $value = '';
                if ($type === 's') {
                    $idx = isset($c->v) ? (int) $c->v : -1;
                    $value = $idx >= 0 && isset($shared[$idx]) ? $shared[$idx] : '';
                } elseif ($type === 'inlineStr') {
                    $value = isset($c->is->t) ? (string) $c->is->t : '';
                } else {
                    $value = isset($c->v) ? (string) $c->v : '';
                }
                $colIndex = $this->xlsx_col_to_index($ref);
                if ($colIndex !== null) {
                    $cells[$colIndex] = $value;
                }
            }
            if (!$cells) {
                $rows[] = [];
                continue;
            }
            $maxIndex = max(array_keys($cells));
            $rowData = [];
            for ($i = 0; $i <= $maxIndex; $i++) {
                $rowData[$i] = $cells[$i] ?? '';
            }
            $rows[] = $rowData;
        }
        $zip->close();
        return $rows;
    }

    private function read_xlsx_shared_strings($zip) {
        $strings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if (!$sharedXml) {
            return $strings;
        }
        $xml = simplexml_load_string($sharedXml);
        if (!$xml) {
            return $strings;
        }
        foreach ($xml->si as $si) {
            $text = '';
            if (isset($si->t)) {
                $text = (string) $si->t;
            } else {
                foreach ($si->r as $r) {
                    $text .= (string) $r->t;
                }
            }
            $strings[] = $text;
        }
        return $strings;
    }

    private function read_xlsx_sheet_xml($zip) {
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml) {
            return $sheetXml;
        }
        for ($i = 1; $i <= 5; $i++) {
            $name = 'xl/worksheets/sheet' . $i . '.xml';
            $sheetXml = $zip->getFromName($name);
            if ($sheetXml) {
                return $sheetXml;
            }
        }
        return '';
    }

    private function xlsx_col_to_index($ref) {
        if (!preg_match('/^([A-Z]+)/i', $ref, $match)) {
            return null;
        }
        $letters = strtoupper($match[1]);
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }
        return $index - 1;
    }

    private function csv_value($row, $index) {
        if ($index === '' || $index === null) {
            return '';
        }
        $idx = (int) $index;
        return isset($row[$idx]) ? trim((string) $row[$idx]) : '';
    }

    private function import_schools_spreadsheet_mapped($file, $map, $ext) {
        $rows = $this->read_spreadsheet_rows($file, $ext);
        if (!$rows) {
            return 'No rows found.';
        }
        $header = array_shift($rows);
        if (!$header) {
            return 'Empty spreadsheet.';
        }
        $count = 0;
        $skipped = 0;

        $created = 0;
        $updated = 0;
        $contact_warnings = 0;
        foreach ($rows as $row) {
            $school_name = $this->csv_value($row, $map['cmn_school_name'] ?? '');
            $location = $this->csv_value($row, $map['cmn_location'] ?? '');
            $phone = $this->csv_value($row, $map['cmn_phone'] ?? '');
            $school_email = $this->csv_value($row, $map['cmn_email'] ?? '');
            $cover_manager_name = $this->csv_value($row, $map['cmn_cover_manager'] ?? '');
            $cover_manager_email = $this->csv_value($row, $map['cmn_cover_manager_email'] ?? '');
            $email_name = $this->csv_value($row, $map['cmn_email_name'] ?? '');

            if ($school_name === '' || $location === '' || $phone === '' || $school_email === '' || $cover_manager_name === '' || $cover_manager_email === '' || $email_name === '') {
                $skipped += 1;
                continue;
            }
            if ($school_email && !is_email($school_email)) {
                $skipped += 1;
                continue;
            }
            if ($cover_manager_email && !is_email($cover_manager_email)) {
                $skipped += 1;
                continue;
            }
            if (strlen(preg_replace('/\\D+/', '', $phone)) < 7) {
                $skipped += 1;
                continue;
            }
            $school_domain = $this->get_email_domain($school_email);
            if ($school_domain === '') {
                $skipped += 1;
                continue;
            }

            $school_code = $this->csv_value($row, $map['cmn_school_id'] ?? '');
            if (!preg_match('/^CMN\\d+$/i', $school_code)) {
                $school_code = '';
            }
            $existing_id = 0;
            if ($school_code !== '') {
                $existing_id = $this->get_school_post_id_by_school_id($school_code);
            }
            if (!$existing_id) {
                $existing_id = $this->get_school_post_id_by_domain($school_domain);
            }
            if ($existing_id) {
                $post_id = $existing_id;
                wp_update_post([
                    'ID' => $post_id,
                    'post_title' => $school_name,
                ]);
                $updated++;
            } else {
                $post_id = wp_insert_post([
                    'post_type' => 'cmn_school',
                    'post_title' => $school_name,
                    'post_status' => 'publish',
                ]);
                if (is_wp_error($post_id)) {
                    $skipped += 1;
                    continue;
                }
                $created++;
            }
            $domain_owner = $this->get_school_post_id_by_domain($school_domain);
            if ($domain_owner && (int) $domain_owner !== (int) $post_id) {
                $skipped += 1;
                continue;
            }

            if ($school_code === '') {
                $school_code = get_post_meta($post_id, 'cmn_school_id', true);
                if ($school_code === '') {
                    $school_code = $this->generate_school_id();
                }
            }
            $meta = [
                'cmn_location' => $location,
                'cmn_phone' => $phone,
                'cmn_cover_manager' => $cover_manager_name,
                'cmn_cover_manager_email' => $cover_manager_email,
                'cmn_account_manager' => $this->csv_value($row, $map['cmn_account_manager'] ?? ''),
                'cmn_email' => $school_email,
                'cmn_email_name' => $email_name,
                'cmn_spoke_to_cm' => $this->csv_value($row, $map['cmn_spoke_to_cm'] ?? ''),
                'cmn_switchboard' => $this->csv_value($row, $map['cmn_switchboard'] ?? ''),
                'cmn_website' => $this->csv_value($row, $map['cmn_website'] ?? ''),
                'cmn_school_id' => $school_code,
                'cmn_status' => $this->csv_value($row, $map['cmn_status'] ?? '') ?: 'lead',
                'cmn_pipeline_stage' => $this->csv_value($row, $map['cmn_pipeline_stage'] ?? '') ?: 'new_lead',
                'cmn_school_email_domain' => $school_domain,
            ];
            if ($meta['cmn_status'] === 'client' && $meta['cmn_pipeline_stage'] !== 'lost') {
                $meta['cmn_pipeline_stage'] = $meta['cmn_pipeline_stage'] ?: 'won';
            }
            foreach ($meta as $key => $value) {
                update_post_meta($post_id, $key, sanitize_text_field($value));
            }
            $this->store_cover_manager_split($post_id, $cover_manager_name);
            $this->upsert_school_index($post_id);

            $contact_name = $this->csv_value($row, $map['cmn_contact_name'] ?? '');
            $contact_email = $this->csv_value($row, $map['cmn_contact_email'] ?? '');
            $contact_phone = $this->csv_value($row, $map['cmn_contact_phone'] ?? '');
            $contact_role = $this->csv_value($row, $map['cmn_contact_role'] ?? '');
            if ($contact_name !== '') {
                $contact_id = $this->create_or_update_contact([
                    'name' => $contact_name,
                    'email' => $contact_email,
                    'phone' => $contact_phone,
                    'role' => $contact_role,
                    'school_id' => $post_id,
                    'school_domain' => $school_domain,
                    'is_primary' => true,
                ]);
                if (!$contact_id) {
                    $contact_warnings++;
                } else {
                    update_post_meta($post_id, 'cmn_contact1', $contact_name);
                    update_post_meta($post_id, 'cmn_contact1_email', $contact_email);
                    update_post_meta($post_id, 'cmn_contact_role', $contact_role);
                    update_post_meta($post_id, 'cmn_primary_contact_name', $contact_name);
                    update_post_meta($post_id, 'cmn_primary_contact_email', $contact_email ?: $school_email);
                    update_post_meta($post_id, 'cmn_primary_contact_phone', $contact_phone ?: $phone);
                    update_post_meta($post_id, 'cmn_primary_contact_role', $contact_role);
                }
            }
            $count += 1;
        }
        $message = 'Imported ' . $count . ' schools (' . $created . ' new, ' . $updated . ' updated).';
        if ($skipped > 0) {
            $message .= ' Skipped ' . $skipped . ' rows with missing or invalid required fields.';
        }
        if ($contact_warnings > 0) {
            $message .= ' Contact warnings: ' . $contact_warnings . '.';
        }
        return $message;
    }

    private function import_contacts_spreadsheet_mapped($file, $map, $ext) {
        $rows = $this->read_spreadsheet_rows($file, $ext);
        if (!$rows) {
            return 'No rows found.';
        }
        $header = array_shift($rows);
        if (!$header) {
            return 'Empty spreadsheet.';
        }

        $count = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $warnings = 0;

        foreach ($rows as $row) {
            $name = $this->csv_value($row, $map['cmn_contact_name'] ?? '');
            if ($name === '') {
                $skipped++;
                continue;
            }
            $email = $this->csv_value($row, $map['cmn_contact_email'] ?? '');
            $phone = $this->csv_value($row, $map['cmn_contact_phone'] ?? '');
            $role = $this->csv_value($row, $map['cmn_contact_role'] ?? '');
            if ($email && !is_email($email)) {
                $skipped++;
                $warnings++;
                continue;
            }
            $school_domain = '';
            $school_identifier = $this->csv_value($row, $map['cmn_contact_school_id'] ?? '');
            if ($school_identifier !== '') {
                $post_id = $this->get_school_post_id_by_school_id($school_identifier);
                if ($post_id) {
                    $school_domain = (string) get_post_meta($post_id, 'cmn_school_email_domain', true);
                } else {
                    $warnings++;
                }
            }
            if ($school_domain === '') {
                $school_email = $this->csv_value($row, $map['cmn_contact_school_email'] ?? '');
                if ($school_email !== '') {
                    $school_domain = $this->get_email_domain($school_email);
                    if ($school_domain === '') {
                        $warnings++;
                    }
                }
            }
            if ($school_domain === '') {
                $school_name = $this->csv_value($row, $map['cmn_contact_school_name'] ?? '');
                if ($school_name !== '') {
                    $ambiguous = false;
                    $school_domain = $this->find_school_domain_by_name($school_name, $ambiguous);
                    if ($ambiguous || $school_domain === '') {
                        $warnings++;
                        $school_domain = '';
                    }
                }
            }

            $existing_id = 0;
            if ($email) {
                $existing = get_posts([
                    'post_type' => 'cmn_contact',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'meta_query' => [
                        [
                            'key' => 'cmn_contact_email',
                            'value' => $email,
                        ],
                    ],
                ]);
                if ($existing) {
                    $existing_id = (int) $existing[0];
                }
            }

            $contact_id = $this->create_or_update_contact([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'school_id' => 0,
            ]);
            if (!$contact_id) {
                $skipped++;
                continue;
            }
            if ($school_domain) {
                $this->link_contact_to_school($contact_id, $school_domain, true);
                $primary_post = $this->get_school_post_id_by_domain($school_domain);
                if ($primary_post) {
                    update_post_meta($contact_id, 'cmn_contact_school_id', $primary_post);
                }
            }
            if ($existing_id) {
                $updated++;
            } else {
                $created++;
            }
            $count++;
        }

        $message = 'Imported ' . $count . ' contacts (' . $created . ' new, ' . $updated . ' updated).';
        if ($skipped > 0) {
            $message .= ' Skipped ' . $skipped . ' rows with missing or invalid required fields.';
        }
        if ($warnings > 0) {
            $message .= ' Warnings: ' . $warnings . '.';
        }
        return $message;
    }

    public function school_columns($columns) {
        $new = [];
        $new['cb'] = $columns['cb'];
        $new['title'] = 'School';
        $new['cmn_location'] = 'Location';
        $new['cmn_phone'] = 'Phone';
        $new['cmn_email'] = 'Email';
        $new['cmn_status'] = 'Status';
        $new['date'] = $columns['date'];
        return $new;
    }

    public function school_column_values($column, $post_id) {
        switch ($column) {
            case 'cmn_location':
                echo esc_html(get_post_meta($post_id, 'cmn_location', true));
                break;
            case 'cmn_phone':
                echo esc_html(get_post_meta($post_id, 'cmn_phone', true));
                break;
            case 'cmn_email':
                echo esc_html(get_post_meta($post_id, 'cmn_email', true));
                break;
            case 'cmn_status':
                echo esc_html(get_post_meta($post_id, 'cmn_status', true));
                break;
        }
    }

    public function candidate_columns($columns) {
        $new = [];
        $new['cb'] = $columns['cb'];
        $new['title'] = 'Candidate';
        $new['cmn_location'] = 'Location';
        $new['cmn_phone'] = 'Phone';
        $new['cmn_email'] = 'Email';
        $new['cmn_status'] = 'Status';
        $new['date'] = $columns['date'];
        return $new;
    }

    public function candidate_column_values($column, $post_id) {
        switch ($column) {
            case 'cmn_location':
                echo esc_html(get_post_meta($post_id, 'cmn_location', true));
                break;
            case 'cmn_phone':
                echo esc_html(get_post_meta($post_id, 'cmn_phone', true));
                break;
            case 'cmn_email':
                echo esc_html(get_post_meta($post_id, 'cmn_email', true));
                break;
            case 'cmn_status':
                echo esc_html(get_post_meta($post_id, 'cmn_status', true));
                break;
        }
    }
}

if (!function_exists('cmn_get_current_user_role')) {
    function cmn_get_current_user_role() {
        $plugin = $GLOBALS['cmn_one_plugin'] ?? null;
        if ($plugin instanceof CMN_One_Plugin) {
            return $plugin->get_current_user_role();
        }
        return 'guest';
    }
}

if (!function_exists('cmn_user_can_access_school')) {
    function cmn_user_can_access_school($school_identifier) {
        $plugin = $GLOBALS['cmn_one_plugin'] ?? null;
        if ($plugin instanceof CMN_One_Plugin) {
            return $plugin->user_can_access_school($school_identifier);
        }
        return false;
    }
}

if (!function_exists('cmn_user_can_view_candidate')) {
    function cmn_user_can_view_candidate($candidate_id) {
        $plugin = $GLOBALS['cmn_one_plugin'] ?? null;
        if ($plugin instanceof CMN_One_Plugin) {
            return $plugin->user_can_view_candidate($candidate_id);
        }
        return false;
    }
}

if (!function_exists('cmn_send_candidate_email')) {
    function cmn_send_candidate_email($to, $subject, $message, $context = []) {
        $plugin = $GLOBALS['cmn_one_plugin'] ?? null;
        if ($plugin instanceof CMN_One_Plugin) {
            return $plugin->send_candidate_email($to, $subject, $message, $context);
        }
        return wp_mail($to, $subject, $message);
    }
}

if (!function_exists('cmn_send_school_email')) {
    function cmn_send_school_email($to, $subject, $message, $headers = [], $attachments = []) {
        $plugin = $GLOBALS['cmn_one_plugin'] ?? null;
        if ($plugin instanceof CMN_One_Plugin) {
            return $plugin->send_school_email($to, $subject, $message, $headers, $attachments);
        }
        return wp_mail($to, $subject, $message, $headers, $attachments);
    }
}

$GLOBALS['cmn_one_plugin'] = new CMN_One_Plugin();
register_activation_hook(__FILE__, ['CMN_One_Plugin', 'activate']);
