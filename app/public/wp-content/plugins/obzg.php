<?php
/*
Plugin Name: OBZG
Description: Bocce Ball Tournament Management - Custom post types for clubs, players, and matches.
Version: 1.0.0
Author: Your Name
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class OBZG_Plugin {
    public function __construct() {
        add_action( 'init', [ $this, 'register_custom_post_types' ] );
        add_action( 'admin_menu', [ $this, 'register_admin_pages' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        // Ensure custom roles
        add_action( 'init', [ $this, 'ensure_custom_roles' ] );
        
        // AJAX handlers
        add_action( 'wp_ajax_obzg_save_club', [ $this, 'ajax_save_club' ] );
        add_action( 'wp_ajax_obzg_get_club', [ $this, 'ajax_get_club' ] );
        add_action( 'wp_ajax_obzg_delete_club', [ $this, 'ajax_delete_club' ] );
        add_action( 'wp_ajax_obzg_get_single_club', [ $this, 'ajax_get_single_club' ] );
        add_action( 'wp_ajax_obzg_get_player', [ $this, 'ajax_get_player' ] );
        add_action( 'wp_ajax_obzg_get_single_player', [ $this, 'ajax_get_single_player' ] );
        add_action( 'wp_ajax_obzg_save_player', [ $this, 'ajax_save_player' ] );
        add_action( 'wp_ajax_obzg_delete_player', [ $this, 'ajax_delete_player' ] );
        add_action( 'wp_ajax_obzg_get_match', [ $this, 'ajax_get_match' ] );
        add_action( 'wp_ajax_obzg_get_single_match', [ $this, 'ajax_get_single_match' ] );
        add_action( 'wp_ajax_obzg_save_match', [ $this, 'ajax_save_match' ] );
        add_action( 'wp_ajax_obzg_assign_players', [ $this, 'ajax_assign_players' ] );
        add_action( 'wp_ajax_obzg_import_players_excel', [ $this, 'ajax_import_players_excel' ] );
        add_action( 'wp_ajax_obzg_get_tournament', [ $this, 'ajax_get_tournament' ] );
        add_action( 'wp_ajax_obzg_get_single_tournament', [ $this, 'ajax_get_single_tournament' ] );
        add_action( 'wp_ajax_obzg_save_tournament', [ $this, 'ajax_save_tournament' ] );
        add_action( 'wp_ajax_obzg_delete_tournament', [ $this, 'ajax_delete_tournament' ] );
        add_action( 'wp_ajax_obzg_generate_swiss_rounds', [ $this, 'ajax_generate_swiss_rounds' ] );
        add_action( 'wp_ajax_obzg_save_match_result', [ $this, 'ajax_save_match_result' ] );
        add_action( 'wp_ajax_obzg_get_tournament_standings', [ $this, 'ajax_get_tournament_standings' ] );
        add_action( 'wp_ajax_obzg_add_team_to_tournament', [ $this, 'ajax_add_team_to_tournament' ] );
        add_action( 'wp_ajax_obzg_remove_team_from_tournament', [ $this, 'ajax_remove_team_from_tournament' ] );
        add_action( 'wp_ajax_obzg_get_available_teams', [ $this, 'ajax_get_available_teams' ] );
        add_action( 'wp_ajax_obzg_add_random_teams', [ $this, 'ajax_add_random_teams' ] );
        add_action( 'wp_ajax_obzg_add_sample_clubs', [ $this, 'ajax_add_sample_clubs' ] );
        
        // REST API endpoints for React frontend
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
        
        // Add CORS headers for React frontend
        add_action( 'init', [ $this, 'add_cors_headers' ] );
    }

    /**
     * Ensure custom roles/capabilities exist
     */
    public function ensure_custom_roles() {
        // Remove legacy role if it exists
        if (get_role('obzg_results')) {
            // Migrate users from obzg_results to subscriber
            $users = get_users([ 'role' => 'obzg_results' ]);
            foreach ($users as $u) {
                $u->add_role('subscriber');
                $u->remove_role('obzg_results');
            }
            remove_role('obzg_results');
        }

        // Ensure capabilities on standard roles
        $admin = get_role('administrator');
        if ($admin && !$admin->has_cap('obzg_enter_results')) {
            $admin->add_cap('obzg_enter_results');
        }
        $subscriber = get_role('subscriber');
        if ($subscriber && !$subscriber->has_cap('obzg_enter_results')) {
            $subscriber->add_cap('obzg_enter_results');
        }

        // Make sure designated super admin email has admin role
        $super_email = 'leja.vehovec28@gmail.com';
        $su = get_user_by('email', $super_email);
        if ($su && !in_array('administrator', (array)$su->roles, true)) {
            $su->set_role('administrator');
        }
    }

    public function register_custom_post_types() {
        // Register Club CPT
        register_post_type( 'obzg_club', [
            'labels' => [
                'name' => __( 'Clubs', 'obzg' ),
                'singular_name' => __( 'Club', 'obzg' ),
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => [ 'title', 'editor', 'thumbnail' ],
        ] );

        // Register Player CPT
        register_post_type( 'obzg_player', [
            'labels' => [
                'name' => __( 'Players', 'obzg' ),
                'singular_name' => __( 'Player', 'obzg' ),
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => [ 'title', 'editor', 'thumbnail' ],
        ] );

        // Register Match CPT
        register_post_type( 'obzg_match', [
            'labels' => [
                'name' => __( 'Matches', 'obzg' ),
                'singular_name' => __( 'Match', 'obzg' ),
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => [ 'title', 'editor' ],
        ] );

        // Register Tournament CPT
        register_post_type( 'obzg_tournament', [
            'labels' => [
                'name' => __( 'Tournaments', 'obzg' ),
                'singular_name' => __( 'Tournament', 'obzg' ),
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => [ 'title', 'editor' ],
        ] );
    }

    // Relationship helpers
    // Assign players to a club
    public static function set_club_players( $club_id, $player_ids ) {
        update_post_meta( $club_id, '_obzg_club_players', array_map( 'intval', (array)$player_ids ) );
    }
    public static function get_club_players( $club_id ) {
        return get_post_meta( $club_id, '_obzg_club_players', true ) ?: [];
    }
    // Assign clubs to a match
    public static function set_match_clubs( $match_id, $club_ids ) {
        update_post_meta( $match_id, '_obzg_match_clubs', array_map( 'intval', (array)$club_ids ) );
    }
    public static function get_match_clubs( $match_id ) {
        return get_post_meta( $match_id, '_obzg_match_clubs', true ) ?: [];
    }

    // Tournament helpers
    public static function set_tournament_matches( $tournament_id, $match_ids ) {
        update_post_meta( $tournament_id, '_obzg_tournament_matches', array_map( 'intval', (array)$match_ids ) );
    }
    public static function get_tournament_matches( $tournament_id ) {
        return get_post_meta( $tournament_id, '_obzg_tournament_matches', true ) ?: [];
    }
    public static function set_tournament_clubs( $tournament_id, $club_ids ) {
        update_post_meta( $tournament_id, '_obzg_tournament_clubs', array_map( 'intval', (array)$club_ids ) );
    }
    public static function get_tournament_clubs( $tournament_id ) {
        return get_post_meta( $tournament_id, '_obzg_tournament_clubs', true ) ?: [];
    }

    // Swiss system helpers
    public static function get_tournament_rounds( $tournament_id ) {
        return get_post_meta( $tournament_id, '_obzg_tournament_rounds', true ) ?: [];
    }
    public static function set_tournament_rounds( $tournament_id, $rounds ) {
        update_post_meta( $tournament_id, '_obzg_tournament_rounds', $rounds );
    }
    public static function get_tournament_standings( $tournament_id ) {
        return get_post_meta( $tournament_id, '_obzg_tournament_standings', true ) ?: [];
    }
    public static function set_tournament_standings( $tournament_id, $standings ) {
        update_post_meta( $tournament_id, '_obzg_tournament_standings', $standings );
    }
    public static function get_match_result( $match_id ) {
        return get_post_meta( $match_id, '_obzg_match_result', true ) ?: null;
    }
    public static function set_match_result( $match_id, $result ) {
        update_post_meta( $match_id, '_obzg_match_result', $result );
    }
    public static function get_tournament_max_teams( $tournament_id ) {
        return get_post_meta( $tournament_id, '_obzg_tournament_max_teams', true ) ?: 0;
    }
    public static function set_tournament_max_teams( $tournament_id, $max_teams ) {
        update_post_meta( $tournament_id, '_obzg_tournament_max_teams', $max_teams );
    }

    // Admin page for managing clubs
    public function register_admin_pages() {
        add_menu_page(
            __( 'Manage Clubs', 'obzg' ),
            __( 'Clubs', 'obzg' ),
            'manage_options',
            'obzg_manage_clubs',
            [ $this, 'render_manage_clubs_page' ],
            'dashicons-groups',
            6
        );
        add_menu_page(
            __( 'Add Player', 'obzg' ),
            __( 'Add Player', 'obzg' ),
            'manage_options',
            'obzg_manage_players',
            [ $this, 'render_manage_players_page' ],
            'dashicons-admin-users',
            7
        );
        add_menu_page(
            __( 'Manage Matches', 'obzg' ),
            __( 'Matches', 'obzg' ),
            'manage_options',
            'obzg_manage_matches',
            [ $this, 'render_manage_matches_page' ],
            'dashicons-calendar-alt',
            8
        );
        add_menu_page(
            __( 'Manage Tournaments', 'obzg' ),
            __( 'Turnir', 'obzg' ),
            'manage_options',
            'obzg_manage_tournaments',
            [ $this, 'render_manage_tournaments_page' ],
            'dashicons-trophy',
            9
        );
    }
    public function render_manage_clubs_page() {
        echo '<div id="obzg-club-admin-root"></div>';
    }
    public function render_manage_players_page() {
        echo '<div id="obzg-player-admin-root"></div>';
    }
    public function render_manage_matches_page() {
        echo '<div id="obzg-match-admin-root"></div>';
    }
    public function render_manage_tournaments_page() {
        echo '<div id="obzg-tournament-admin-root"></div>';
    }

    // AJAX handlers (stubs)
    public function ajax_save_club() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $club_id = isset($_POST['club_id']) ? intval($_POST['club_id']) : 0;
        $title = isset($_POST['club_title']) ? sanitize_text_field($_POST['club_title']) : '';
        $desc = ''; // Description removed
        $email = isset($_POST['club_email']) ? sanitize_email($_POST['club_email']) : '';
        $phone = isset($_POST['club_phone']) ? sanitize_text_field($_POST['club_phone']) : '';
        $address = isset($_POST['club_address']) ? sanitize_text_field($_POST['club_address']) : '';
        $city = isset($_POST['club_city']) ? sanitize_text_field($_POST['club_city']) : '';
        $city_number = isset($_POST['club_city_number']) ? sanitize_text_field($_POST['club_city_number']) : '';
        $president = isset($_POST['club_president']) ? sanitize_text_field($_POST['club_president']) : '';
        $league = isset($_POST['club_league']) ? (array)$_POST['club_league'] : [];
        if (empty($title)) {
            wp_send_json_error(['message' => __('Club name is required.', 'obzg')]);
        }
        $post_data = [
            'post_title'   => $title,
            'post_content' => $desc,
            'post_type'    => 'obzg_club',
            'post_status'  => 'publish',
        ];
        if ($club_id) {
            $post_data['ID'] = $club_id;
            $result = wp_update_post($post_data, true);
        } else {
            $result = wp_insert_post($post_data, true);
        }
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        $club_id = is_numeric($result) ? $result : $club_id;
        update_post_meta($club_id, '_obzg_club_email', $email);
        update_post_meta($club_id, '_obzg_club_phone', $phone);
        update_post_meta($club_id, '_obzg_club_address', $address);
        update_post_meta($club_id, '_obzg_club_city', $city);
        update_post_meta($club_id, '_obzg_club_city_number', $city_number);
        update_post_meta($club_id, '_obzg_club_president', $president);
        update_post_meta($club_id, '_obzg_club_league', $league);
        wp_send_json_success(['id' => $club_id]);
    }
    public function ajax_get_club() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $args = [
            'post_type'      => 'obzg_club',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];
        $query = new WP_Query( $args );
        $clubs = [];
        foreach ( $query->posts as $post ) {
            $clubs[] = [
                'id'    => $post->ID,
                'title' => get_the_title( $post ),
                'email' => get_post_meta($post->ID, '_obzg_club_email', true),
                'phone' => get_post_meta($post->ID, '_obzg_club_phone', true),
                'address' => get_post_meta($post->ID, '_obzg_club_address', true),
                'city' => get_post_meta($post->ID, '_obzg_club_city', true),
                'city_number' => get_post_meta($post->ID, '_obzg_club_city_number', true),
                'president' => get_post_meta($post->ID, '_obzg_club_president', true),
                'league' => get_post_meta($post->ID, '_obzg_club_league', true),
                'players' => self::get_club_players($post->ID),
            ];
        }
        wp_send_json_success( $clubs );
    }
    public function ajax_delete_club() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $club_id = isset($_POST['club_id']) ? intval($_POST['club_id']) : 0;
        if (!$club_id) {
            wp_send_json_error(['message' => __('Invalid club ID.', 'obzg')]);
        }
        $deleted = wp_delete_post($club_id, true);
        if ($deleted) {
            wp_send_json_success();
        } else {
            wp_send_json_error(['message' => __('Failed to delete club.', 'obzg')]);
        }
    }

    public function ajax_get_single_club() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $club_id = isset($_POST['club_id']) ? intval($_POST['club_id']) : 0;
        if (!$club_id) {
            wp_send_json_error(['message' => __('Invalid club ID.', 'obzg')]);
        }
        $post = get_post($club_id);
        if (!$post || $post->post_type !== 'obzg_club') {
            wp_send_json_error(['message' => __('Club not found.', 'obzg')]);
        }
        $club = [
            'id'    => $post->ID,
            'title' => $post->post_title,
            'email' => get_post_meta($post->ID, '_obzg_club_email', true),
            'phone' => get_post_meta($post->ID, '_obzg_club_phone', true),
            'address' => get_post_meta($post->ID, '_obzg_club_address', true),
            'city' => get_post_meta($post->ID, '_obzg_club_city', true),
            'city_number' => get_post_meta($post->ID, '_obzg_club_city_number', true),
            'president' => get_post_meta($post->ID, '_obzg_club_president', true),
            'league' => get_post_meta($post->ID, '_obzg_club_league', true),
            'players' => self::get_club_players($post->ID),
        ];
        wp_send_json_success($club);
    }

    // AJAX handlers for players
    public function ajax_get_player() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $args = [
            'post_type'      => 'obzg_player',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];
        $query = new WP_Query( $args );
        $players = [];
        foreach ( $query->posts as $post ) {
            $players[] = [
                'id'    => $post->ID,
                'name'  => get_post_meta($post->ID, '_obzg_player_name', true),
                'surname' => get_post_meta($post->ID, '_obzg_player_surname', true),
                'email' => get_post_meta($post->ID, '_obzg_player_email', true),
                'number' => get_post_meta($post->ID, '_obzg_player_number', true),
                'dob' => get_post_meta($post->ID, '_obzg_player_dob', true),
                'address' => get_post_meta($post->ID, '_obzg_player_address', true),
                'city' => get_post_meta($post->ID, '_obzg_player_city', true),
                'city_number' => get_post_meta($post->ID, '_obzg_player_city_number', true),
                'gender' => get_post_meta($post->ID, '_obzg_player_gender', true),
                'emso' => get_post_meta($post->ID, '_obzg_player_emso', true),
                'city_of_birth' => get_post_meta($post->ID, '_obzg_player_city_of_birth', true),
                'citizenship' => get_post_meta($post->ID, '_obzg_player_citizenship', true),
                'club_id' => get_post_meta($post->ID, '_obzg_player_club_id', true),
            ];
        }
        wp_send_json_success( $players );
    }
    public function ajax_get_single_player() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $player_id = isset($_POST['player_id']) ? intval($_POST['player_id']) : 0;
        if (!$player_id) {
            wp_send_json_error(['message' => __('Invalid player ID.', 'obzg')]);
        }
        $post = get_post($player_id);
        if (!$post || $post->post_type !== 'obzg_player') {
            wp_send_json_error(['message' => __('Player not found.', 'obzg')]);
        }
        $player = [
            'id'    => $post->ID,
            'name'  => get_post_meta($post->ID, '_obzg_player_name', true),
            'surname' => get_post_meta($post->ID, '_obzg_player_surname', true),
            'email' => get_post_meta($post->ID, '_obzg_player_email', true),
            'number' => get_post_meta($post->ID, '_obzg_player_number', true),
            'dob' => get_post_meta($post->ID, '_obzg_player_dob', true),
            'address' => get_post_meta($post->ID, '_obzg_player_address', true),
            'city' => get_post_meta($post->ID, '_obzg_player_city', true),
            'city_number' => get_post_meta($post->ID, '_obzg_player_city_number', true),
            'gender' => get_post_meta($post->ID, '_obzg_player_gender', true),
            'emso' => get_post_meta($post->ID, '_obzg_player_emso', true),
            'city_of_birth' => get_post_meta($post->ID, '_obzg_player_city_of_birth', true),
            'citizenship' => get_post_meta($post->ID, '_obzg_player_citizenship', true),
            'club_id' => get_post_meta($post->ID, '_obzg_player_club_id', true),
        ];
        wp_send_json_success($player);
    }
    public function ajax_save_player() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $player_id = isset($_POST['player_id']) ? intval($_POST['player_id']) : 0;
        $name = isset($_POST['player_name']) ? sanitize_text_field($_POST['player_name']) : '';
        $surname = isset($_POST['player_surname']) ? sanitize_text_field($_POST['player_surname']) : '';
        $email = isset($_POST['player_email']) ? sanitize_email($_POST['player_email']) : '';
        $number = isset($_POST['player_number']) ? sanitize_text_field($_POST['player_number']) : '';
        $dob = isset($_POST['player_dob']) ? sanitize_text_field($_POST['player_dob']) : '';
        $address = isset($_POST['player_address']) ? sanitize_text_field($_POST['player_address']) : '';
        $city = isset($_POST['player_city']) ? sanitize_text_field($_POST['player_city']) : '';
        $city_number = isset($_POST['player_city_number']) ? sanitize_text_field($_POST['player_city_number']) : '';
        $gender = isset($_POST['player_gender']) ? sanitize_text_field($_POST['player_gender']) : '';
        $emso = isset($_POST['player_emso']) ? sanitize_text_field($_POST['player_emso']) : '';
        $city_of_birth = isset($_POST['player_city_of_birth']) ? sanitize_text_field($_POST['player_city_of_birth']) : '';
        $citizenship = isset($_POST['player_citizenship']) ? sanitize_text_field($_POST['player_citizenship']) : '';
        $club_id = isset($_POST['player_club_id']) ? intval($_POST['player_club_id']) : 0;
        if (empty($name) || empty($surname)) {
            wp_send_json_error(['message' => __('Name and surname are required.', 'obzg')]);
        }
        $post_data = [
            'post_title'   => $name . ' ' . $surname,
            'post_type'    => 'obzg_player',
            'post_status'  => 'publish',
        ];
        if ($player_id) {
            $post_data['ID'] = $player_id;
            $result = wp_update_post($post_data, true);
        } else {
            $result = wp_insert_post($post_data, true);
        }
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        $player_id = is_numeric($result) ? $result : $player_id;
        update_post_meta($player_id, '_obzg_player_name', $name);
        update_post_meta($player_id, '_obzg_player_surname', $surname);
        update_post_meta($player_id, '_obzg_player_email', $email);
        update_post_meta($player_id, '_obzg_player_number', $number);
        update_post_meta($player_id, '_obzg_player_dob', $dob);
        update_post_meta($player_id, '_obzg_player_address', $address);
        update_post_meta($player_id, '_obzg_player_city', $city);
        update_post_meta($player_id, '_obzg_player_city_number', $city_number);
        update_post_meta($player_id, '_obzg_player_gender', $gender);
        update_post_meta($player_id, '_obzg_player_emso', $emso);
        update_post_meta($player_id, '_obzg_player_city_of_birth', $city_of_birth);
        update_post_meta($player_id, '_obzg_player_citizenship', $citizenship);
        update_post_meta($player_id, '_obzg_player_club_id', $club_id);
        // Update club's player list
        if ($club_id) {
            $players = self::get_club_players($club_id);
            if (!in_array($player_id, $players)) {
                $players[] = $player_id;
                self::set_club_players($club_id, $players);
            }
        }
        // Remove from previous clubs if changed
        $all_clubs = get_posts(['post_type'=>'obzg_club','post_status'=>'publish','numberposts'=>-1]);
        foreach ($all_clubs as $club) {
            if ($club->ID != $club_id) {
                $players = self::get_club_players($club->ID);
                if (($key = array_search($player_id, $players)) !== false) {
                    unset($players[$key]);
                    self::set_club_players($club->ID, $players);
                }
            }
        }
        wp_send_json_success(['id' => $player_id]);
    }

    public function ajax_delete_player() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $player_id = isset($_POST['player_id']) ? intval($_POST['player_id']) : 0;
        if (!$player_id) {
            wp_send_json_error(['message' => __('Invalid player ID.', 'obzg')]);
        }
        // Remove player from all clubs
        $all_clubs = get_posts(['post_type'=>'obzg_club','post_status'=>'publish','numberposts'=>-1]);
        foreach ($all_clubs as $club) {
            $players = self::get_club_players($club->ID);
            if (($key = array_search($player_id, $players)) !== false) {
                unset($players[$key]);
                self::set_club_players($club->ID, $players);
            }
        }
        $deleted = wp_delete_post($player_id, true);
        if ($deleted) {
            wp_send_json_success();
        } else {
            wp_send_json_error(['message' => __('Failed to delete player.', 'obzg')]);
        }
    }

    // AJAX handlers for matches
    public function ajax_get_match() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $args = [
            'post_type'      => 'obzg_match',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];
        $query = new WP_Query( $args );
        $matches = [];
        foreach ( $query->posts as $post ) {
            $matches[] = [
                'id'    => $post->ID,
                'title' => get_the_title( $post ),
                'desc'  => $post->post_content,
            ];
        }
        wp_send_json_success( $matches );
    }
    public function ajax_get_single_match() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $match_id = isset($_POST['match_id']) ? intval($_POST['match_id']) : 0;
        if (!$match_id) {
            wp_send_json_error(['message' => __('Invalid match ID.', 'obzg')]);
        }
        $post = get_post($match_id);
        if (!$post || $post->post_type !== 'obzg_match') {
            wp_send_json_error(['message' => __('Match not found.', 'obzg')]);
        }
        $match = [
            'id'    => $post->ID,
            'title' => $post->post_title,
            'desc'  => $post->post_content,
        ];
        wp_send_json_success($match);
    }
    public function ajax_save_match() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $match_id = isset($_POST['match_id']) ? intval($_POST['match_id']) : 0;
        $title = isset($_POST['match_title']) ? sanitize_text_field($_POST['match_title']) : '';
        $desc = isset($_POST['match_desc']) ? wp_kses_post($_POST['match_desc']) : '';
        if (empty($title)) {
            wp_send_json_error(['message' => __('Match name is required.', 'obzg')]);
        }
        $post_data = [
            'post_title'   => $title,
            'post_content' => $desc,
            'post_type'    => 'obzg_match',
            'post_status'  => 'publish',
        ];
        if ($match_id) {
            $post_data['ID'] = $match_id;
            $result = wp_update_post($post_data, true);
        } else {
            $result = wp_insert_post($post_data, true);
        }
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        wp_send_json_success(['id' => is_numeric($result) ? $result : $match_id]);
    }

    public function ajax_assign_players() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $club_id = isset($_POST['club_id']) ? intval($_POST['club_id']) : 0;
        $players = isset($_POST['club_players']) ? (array)$_POST['club_players'] : [];
        if (!$club_id) {
            wp_send_json_error(['message' => __('Invalid club ID.', 'obzg')]);
        }
        self::set_club_players($club_id, $players);
        wp_send_json_success();
    }

    public function ajax_import_players_excel() {
        check_ajax_referer( 'obzg_admin_nonce' );
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }
        if (empty($_FILES['players_excel']['tmp_name'])) {
            wp_send_json_error(['message' => 'No file uploaded.']);
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $file = $_FILES['players_excel'];
        $tmp = $file['tmp_name'];
        // Use PHPSpreadsheet for .xlsx parsing
        if (!class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            wp_send_json_error(['message' => 'PHPSpreadsheet is not installed. Please run composer require phpoffice/phpspreadsheet in your plugin directory.']);
        }
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            $header = array_map('strtolower', array_map('trim', $rows[1]));
            $map = [
                'name' => array_search('name', $header),
                'surname' => array_search('surname', $header),
                'email' => array_search('email', $header),
                'number' => array_search('phone number', $header),
                'dob' => array_search('date of birth', $header),
                'city_of_birth' => array_search('city of birth', $header),
                'gender' => array_search('gender', $header),
                'emso' => array_search('emšo', $header),
                'club' => array_search('club', $header),
                'address' => array_search('address', $header),
                'city' => array_search('city', $header),
                'city_number' => array_search('city number', $header),
                'citizenship' => array_search('citizenship', $header),
            ];
            $inserted = 0;
            for ($i = 2; $i <= count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row[$map['name']]) && empty($row[$map['surname']])) continue;
                $post_data = [
                    'post_title'   => $row[$map['name']] . ' ' . $row[$map['surname']],
                    'post_type'    => 'obzg_player',
                    'post_status'  => 'publish',
                ];
                $player_id = wp_insert_post($post_data);
                if (!$player_id) continue;
                update_post_meta($player_id, '_obzg_player_name', $row[$map['name']]);
                update_post_meta($player_id, '_obzg_player_surname', $row[$map['surname']]);
                update_post_meta($player_id, '_obzg_player_email', $row[$map['email']]);
                update_post_meta($player_id, '_obzg_player_number', $row[$map['number']]);
                update_post_meta($player_id, '_obzg_player_dob', $row[$map['dob']]);
                update_post_meta($player_id, '_obzg_player_city_of_birth', $row[$map['city_of_birth']]);
                update_post_meta($player_id, '_obzg_player_gender', $row[$map['gender']]);
                update_post_meta($player_id, '_obzg_player_emso', $row[$map['emso']]);
                update_post_meta($player_id, '_obzg_player_address', $row[$map['address']]);
                update_post_meta($player_id, '_obzg_player_city', $row[$map['city']]);
                update_post_meta($player_id, '_obzg_player_city_number', $row[$map['city_number']]);
                update_post_meta($player_id, '_obzg_player_citizenship', $row[$map['citizenship']]);
                // Club assignment (by name)
                if (!empty($row[$map['club']])) {
                    $club = get_page_by_title($row[$map['club']], OBJECT, 'obzg_club');
                    if ($club) {
                        update_post_meta($player_id, '_obzg_player_club_id', $club->ID);
                        $players = self::get_club_players($club->ID);
                        if (!in_array($player_id, $players)) {
                            $players[] = $player_id;
                            self::set_club_players($club->ID, $players);
                        }
                    }
                }
                $inserted++;
            }
            wp_send_json_success(['inserted' => $inserted]);
        } catch (\Throwable $e) {
            wp_send_json_error(['message' => 'Excel import error: ' . $e->getMessage()]);
        }
    }

    // AJAX handlers for tournaments
    public function ajax_get_tournament() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $args = [
            'post_type'      => 'obzg_tournament',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];
        $query = new WP_Query( $args );
        $tournaments = [];
        foreach ( $query->posts as $post ) {
            $tournaments[] = [
                'id'    => $post->ID,
                'title' => get_the_title( $post ),
                'desc'  => $post->post_content,
                'start_date' => get_post_meta($post->ID, '_obzg_tournament_start_date', true),
                'end_date' => get_post_meta($post->ID, '_obzg_tournament_end_date', true),
                'location' => get_post_meta($post->ID, '_obzg_tournament_location', true),
                'status' => get_post_meta($post->ID, '_obzg_tournament_status', true),
                'tournament_type' => get_post_meta($post->ID, '_obzg_tournament_type', true),
                'num_rounds' => get_post_meta($post->ID, '_obzg_tournament_num_rounds', true),
                'max_teams' => self::get_tournament_max_teams($post->ID),
                'matches' => self::get_tournament_matches($post->ID),
                'clubs' => self::get_tournament_clubs($post->ID),
                'rounds' => self::get_tournament_rounds($post->ID),
                'standings' => self::get_tournament_standings($post->ID),
            ];
        }
        wp_send_json_success( $tournaments );
    }

    public function ajax_get_single_tournament() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $tournament_id = isset($_POST['tournament_id']) ? intval($_POST['tournament_id']) : 0;
        if (!$tournament_id) {
            wp_send_json_error(['message' => __('Invalid tournament ID.', 'obzg')]);
        }
        $post = get_post($tournament_id);
        if (!$post || $post->post_type !== 'obzg_tournament') {
            wp_send_json_error(['message' => __('Tournament not found.', 'obzg')]);
        }
        $tournament = [
            'id'    => $post->ID,
            'title' => $post->post_title,
            'desc'  => $post->post_content,
            'start_date' => get_post_meta($post->ID, '_obzg_tournament_start_date', true),
            'end_date' => get_post_meta($post->ID, '_obzg_tournament_end_date', true),
            'location' => get_post_meta($post->ID, '_obzg_tournament_location', true),
            'status' => get_post_meta($post->ID, '_obzg_tournament_status', true),
            'tournament_type' => get_post_meta($post->ID, '_obzg_tournament_type', true),
            'num_rounds' => get_post_meta($post->ID, '_obzg_tournament_num_rounds', true),
            'max_teams' => self::get_tournament_max_teams($post->ID),
            'matches' => self::get_tournament_matches($post->ID),
            'clubs' => self::get_tournament_clubs($post->ID),
            'rounds' => self::get_tournament_rounds($post->ID),
            'standings' => self::get_tournament_standings($post->ID),
        ];
        wp_send_json_success($tournament);
    }

    public function ajax_save_tournament() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $tournament_id = isset($_POST['tournament_id']) ? intval($_POST['tournament_id']) : 0;
        $title = isset($_POST['tournament_title']) ? sanitize_text_field($_POST['tournament_title']) : '';
        $desc = isset($_POST['tournament_desc']) ? wp_kses_post($_POST['tournament_desc']) : '';
        $start_date = isset($_POST['tournament_start_date']) ? sanitize_text_field($_POST['tournament_start_date']) : '';
        $end_date = isset($_POST['tournament_end_date']) ? sanitize_text_field($_POST['tournament_end_date']) : '';
        $location = isset($_POST['tournament_location']) ? sanitize_text_field($_POST['tournament_location']) : '';
        $status = isset($_POST['tournament_status']) ? sanitize_text_field($_POST['tournament_status']) : '';
        $matches = isset($_POST['tournament_matches']) ? (array)$_POST['tournament_matches'] : [];
        $clubs = isset($_POST['tournament_clubs']) ? (array)$_POST['tournament_clubs'] : [];
        $tournament_type = isset($_POST['tournament_type']) ? sanitize_text_field($_POST['tournament_type']) : 'standard';
        $num_rounds = isset($_POST['tournament_num_rounds']) ? intval($_POST['tournament_num_rounds']) : 5;
        $max_teams = isset($_POST['tournament_max_teams']) ? intval($_POST['tournament_max_teams']) : 0;

        if (empty($title)) {
            wp_send_json_error(['message' => __('Tournament name is required.', 'obzg')]);
        }

        $post_data = [
            'post_title'   => $title,
            'post_content' => $desc,
            'post_type'    => 'obzg_tournament',
            'post_status'  => 'publish',
        ];

        if ($tournament_id) {
            $post_data['ID'] = $tournament_id;
            $result = wp_update_post($post_data, true);
        } else {
            $result = wp_insert_post($post_data, true);
        }

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        $tournament_id = is_numeric($result) ? $result : $tournament_id;

        update_post_meta($tournament_id, '_obzg_tournament_start_date', $start_date);
        update_post_meta($tournament_id, '_obzg_tournament_end_date', $end_date);
        update_post_meta($tournament_id, '_obzg_tournament_location', $location);
        update_post_meta($tournament_id, '_obzg_tournament_status', $status);
        update_post_meta($tournament_id, '_obzg_tournament_type', $tournament_type);
        update_post_meta($tournament_id, '_obzg_tournament_num_rounds', $num_rounds);
        self::set_tournament_max_teams($tournament_id, $max_teams);

        self::set_tournament_matches($tournament_id, $matches);
        self::set_tournament_clubs($tournament_id, $clubs);

        wp_send_json_success(['id' => $tournament_id]);
    }

    public function ajax_delete_tournament() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $tournament_id = isset($_POST['tournament_id']) ? intval($_POST['tournament_id']) : 0;
        if (!$tournament_id) {
            wp_send_json_error(['message' => __('Invalid tournament ID.', 'obzg')]);
        }
        $deleted = wp_delete_post($tournament_id, true);
        if ($deleted) {
            wp_send_json_success();
        } else {
            wp_send_json_error(['message' => __('Failed to delete tournament.', 'obzg')]);
        }
    }

    // Swiss System AJAX handlers
    public function ajax_generate_swiss_rounds() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $tournament_id = isset($_POST['tournament_id']) ? intval($_POST['tournament_id']) : 0;
        $round_number = isset($_POST['round_number']) ? intval($_POST['round_number']) : 1;
        
        if (!$tournament_id) {
            wp_send_json_error(['message' => __('Invalid tournament ID.', 'obzg')]);
        }

        $clubs = self::get_tournament_clubs($tournament_id);
        if (count($clubs) < 2) {
            wp_send_json_error(['message' => __('Need at least 2 clubs to generate rounds.', 'obzg')]);
        }

        // Enforce optimal Swiss rounds limit
        $optimal_rounds = (int)ceil(log(count($clubs), 2));
        if ($round_number > $optimal_rounds) {
            wp_send_json_error(['message' => __('You cannot generate more than ' . $optimal_rounds . ' rounds for ' . count($clubs) . ' teams.', 'obzg')]);
        }

        // Get current standings
        $standings = self::get_tournament_standings($tournament_id);
        if (empty($standings)) {
            // Initialize standings for first round
            $standings = [];
            foreach ($clubs as $club_id) {
                $club = get_post($club_id);
                if ($club) {
                    $standings[] = [
                        'club_id' => $club_id,
                        'club_name' => $club->post_title,
                        'points' => 0,
                        'games_played' => 0,
                        'wins' => 0,
                        'losses' => 0,
                        'draws' => 0,
                        'opponents' => []
                    ];
                }
            }
            self::set_tournament_standings($tournament_id, $standings);
        }

        // Get existing rounds
        $existing_rounds = self::get_tournament_rounds($tournament_id);
        
        // Check if this round already exists
        foreach ($existing_rounds as $existing_round) {
            if ($existing_round['round'] == $round_number) {
                wp_send_json_error(['message' => __('Round ' . $round_number . ' already exists.', 'obzg')]);
            }
        }

        // Generate the specific round
        $round_matches = $this->generate_swiss_round($standings, $round_number, $tournament_id);
        
        // Add the new round to existing rounds
        $existing_rounds[] = [
            'round' => $round_number,
            'matches' => $round_matches
        ];
        
        // Sort rounds by round number
        usort($existing_rounds, function($a, $b) {
            return $a['round'] - $b['round'];
        });

        self::set_tournament_rounds($tournament_id, $existing_rounds);
        
        $message = $round_number == 1 ? 'First round generated successfully!' : 'Round ' . $round_number . ' generated successfully!';
        wp_send_json_success([
            'rounds' => $existing_rounds, 
            'standings' => $standings,
            'message' => $message
        ]);
    }

    private function generate_swiss_round($standings, $round_number, $tournament_id) {
        $matches = [];
        $used_clubs = [];
        
        // Get tournament groups to work within group structure
        $groups = $this->get_tournament_groups($tournament_id);
        
        // If groups exist, generate matches within each group
        if (!empty($groups)) {
            foreach ($groups as $group) {
                $group_matches = $this->generate_group_round($group, $round_number, $standings);
                $matches = array_merge($matches, $group_matches);
            }
            return $matches;
        }
        
        // Fallback to original Swiss system if no groups
        return $this->generate_swiss_round_fallback($standings, $round_number);
    }
    
    private function generate_group_round($group, $round_number, $standings) {
        $matches = [];
        $group_clubs = $group['club_ids'];
        $group_standings = [];
        
        // Get standings for clubs in this group
        foreach ($standings as $standing) {
            if (in_array($standing['club_id'], $group_clubs)) {
                $group_standings[] = $standing;
            }
        }
        
        // Sort group standings by points (descending), then by wins, then by games played
        usort($group_standings, function($a, $b) {
            if ($a['points'] != $b['points']) {
                return $b['points'] - $a['points'];
            }
            if ($a['wins'] != $b['wins']) {
                return $b['wins'] - $a['wins'];
            }
            return $a['games_played'] - $b['games_played'];
        });
        
        $clubs_copy = array_values($group_standings);
        $used_in_group = [];
        
        // First round: random pairing within group
        if ($round_number == 1) {
            shuffle($clubs_copy);
            
            for ($i = 0; $i < count($clubs_copy) - 1; $i += 2) {
                $matches[] = [
                    'match_id' => 0,
                    'club1_id' => $clubs_copy[$i]['club_id'],
                    'club1_name' => $clubs_copy[$i]['club_name'],
                    'club2_id' => $clubs_copy[$i + 1]['club_id'],
                    'club2_name' => $clubs_copy[$i + 1]['club_name'],
                    'result' => null,
                    'round' => $round_number,
                    'group' => $group['name']
                ];
                $used_in_group[] = $clubs_copy[$i]['club_id'];
                $used_in_group[] = $clubs_copy[$i + 1]['club_id'];
            }
            
            // Handle odd number of clubs in group (one team gets X - automatic 6:0 win)
            if (count($clubs_copy) % 2 == 1) {
                $x_club = $clubs_copy[count($clubs_copy) - 1];
                $matches[] = [
                    'match_id' => 0,
                    'club1_id' => $x_club['club_id'],
                    'club1_name' => $x_club['club_name'],
                    'club2_id' => 0,
                    'club2_name' => 'X',
                    'result' => [
                        'club1_score' => 6,
                        'club2_score' => 0,
                        'timestamp' => current_time('mysql')
                    ],
                    'round' => $round_number,
                    'group' => $group['name']
                ];
                $used_in_group[] = $x_club['club_id'];
            }
        } else {
            // Subsequent rounds: Swiss system pairing within group (no rematches)
            $unpaired_clubs = $clubs_copy;
            
            while (!empty($unpaired_clubs)) {
                $current_club = array_shift($unpaired_clubs);
                if (in_array($current_club['club_id'], $used_in_group)) {
                    continue;
                }
                
                // Find all possible opponents within group (not used, not already played)
                $possible_opponents = [];
                foreach ($unpaired_clubs as $index => $potential_opponent) {
                    if (in_array($potential_opponent['club_id'], $used_in_group)) {
                        continue;
                    }
                    // Check if they have already played against each other
                    if (in_array($potential_opponent['club_id'], $current_club['opponents'])) {
                        continue;
                    }
                    // Double-check: also check if current club is in opponent's opponents list
                    if (in_array($current_club['club_id'], $potential_opponent['opponents'])) {
                        continue;
                    }
                    $possible_opponents[] = [
                        'opponent' => $potential_opponent,
                        'index' => $index,
                        'score_diff' => abs($potential_opponent['points'] - $current_club['points'])
                    ];
                }
                
                // Prefer same score, then closest score
                usort($possible_opponents, function($a, $b) {
                    if ($a['score_diff'] == $b['score_diff']) return 0;
                    return $a['score_diff'] < $b['score_diff'] ? -1 : 1;
                });
                
                if (!empty($possible_opponents)) {
                    $best = $possible_opponents[0];
                    $best_opponent = $best['opponent'];
                    $best_opponent_index = $best['index'];
                    $matches[] = [
                        'match_id' => 0,
                        'club1_id' => $current_club['club_id'],
                        'club1_name' => $current_club['club_name'],
                        'club2_id' => $best_opponent['club_id'],
                        'club2_name' => $best_opponent['club_name'],
                        'result' => null,
                        'round' => $round_number,
                        'group' => $group['name']
                    ];
                    $used_in_group[] = $current_club['club_id'];
                    $used_in_group[] = $best_opponent['club_id'];
                    unset($unpaired_clubs[$best_opponent_index]);
                    $unpaired_clubs = array_values($unpaired_clubs); // Re-index array
                } else {
                    // No valid opponent left in group, assign X - automatic 6:0 win
                    $matches[] = [
                        'match_id' => 0,
                        'club1_id' => $current_club['club_id'],
                        'club1_name' => $current_club['club_name'],
                        'club2_id' => 0,
                        'club2_name' => 'X',
                        'result' => [
                            'club1_score' => 6,
                            'club2_score' => 0,
                            'timestamp' => current_time('mysql')
                        ],
                        'round' => $round_number,
                        'group' => $group['name']
                    ];
                    $used_in_group[] = $current_club['club_id'];
                }
            }
        }
        
        return $matches;
    }
    
    private function generate_swiss_round_fallback($standings, $round_number) {
        $matches = [];
        $used_clubs = [];
        
        // Sort standings by points (descending), then by wins, then by games played
        usort($standings, function($a, $b) {
            if ($a['points'] != $b['points']) {
                return $b['points'] - $a['points'];
            }
            if ($a['wins'] != $b['wins']) {
                return $b['wins'] - $a['wins'];
            }
            return $a['games_played'] - $b['games_played'];
        });

        // First round: random pairing
        if ($round_number == 1) {
            $clubs_copy = array_values($standings);
            shuffle($clubs_copy);
            
            for ($i = 0; $i < count($clubs_copy) - 1; $i += 2) {
                $matches[] = [
                    'match_id' => 0,
                    'club1_id' => $clubs_copy[$i]['club_id'],
                    'club1_name' => $clubs_copy[$i]['club_name'],
                    'club2_id' => $clubs_copy[$i + 1]['club_id'],
                    'club2_name' => $clubs_copy[$i + 1]['club_name'],
                    'result' => null,
                    'round' => $round_number
                ];
            }
            
            // Handle odd number of clubs
            if (count($clubs_copy) % 2 == 1) {
                $matches[] = [
                    'match_id' => 0,
                    'club1_id' => $clubs_copy[count($clubs_copy) - 1]['club_id'],
                    'club1_name' => $clubs_copy[count($clubs_copy) - 1]['club_name'],
                    'club2_id' => 0,
                    'club2_name' => 'X',
                    'result' => [
                        'club1_score' => 6,
                        'club2_score' => 0,
                        'timestamp' => current_time('mysql')
                    ],
                    'round' => $round_number
                ];
            }
        } else {
            // Subsequent rounds: bulletproof Swiss system pairing (no rematches)
            $clubs_copy = array_values($standings);
            $unpaired_clubs = $clubs_copy;
            
            while (!empty($unpaired_clubs)) {
                $current_club = array_shift($unpaired_clubs);
                if (in_array($current_club['club_id'], $used_clubs)) {
                    continue;
                }
                
                // Find all possible opponents (not used, not already played)
                $possible_opponents = [];
                foreach ($unpaired_clubs as $index => $potential_opponent) {
                    if (in_array($potential_opponent['club_id'], $used_clubs)) {
                        continue;
                    }
                    // Check if they have already played against each other
                    if (in_array($potential_opponent['club_id'], $current_club['opponents'])) {
                        continue;
                    }
                    // Double-check: also check if current club is in opponent's opponents list
                    if (in_array($current_club['club_id'], $potential_opponent['opponents'])) {
                        continue;
                    }
                    $possible_opponents[] = [
                        'opponent' => $potential_opponent,
                        'index' => $index,
                        'score_diff' => abs($potential_opponent['points'] - $current_club['points'])
                    ];
                }
                
                // Prefer same score, then closest score
                usort($possible_opponents, function($a, $b) {
                    if ($a['score_diff'] == $b['score_diff']) return 0;
                    return $a['score_diff'] < $b['score_diff'] ? -1 : 1;
                });
                
                if (!empty($possible_opponents)) {
                    $best = $possible_opponents[0];
                    $best_opponent = $best['opponent'];
                    $best_opponent_index = $best['index'];
                    $matches[] = [
                        'match_id' => 0,
                        'club1_id' => $current_club['club_id'],
                        'club1_name' => $current_club['club_name'],
                        'club2_id' => $best_opponent['club_id'],
                        'club2_name' => $best_opponent['club_name'],
                        'result' => null,
                        'round' => $round_number
                    ];
                    $used_clubs[] = $current_club['club_id'];
                    $used_clubs[] = $best_opponent['club_id'];
                    unset($unpaired_clubs[$best_opponent_index]);
                    $unpaired_clubs = array_values($unpaired_clubs); // Re-index array
                } else {
                    // No valid opponent left (all opponents already played), assign X - automatic 6:0 win
                    error_log("OBZG Debug: No valid opponent found for club {$current_club['club_id']} ({$current_club['club_name']}) - all opponents already played");
                    $matches[] = [
                        'match_id' => 0,
                        'club1_id' => $current_club['club_id'],
                        'club1_name' => $current_club['club_name'],
                        'club2_id' => 0,
                        'club2_name' => 'X',
                        'result' => [
                            'club1_score' => 6,
                            'club2_score' => 0,
                            'timestamp' => current_time('mysql')
                        ],
                        'round' => $round_number
                    ];
                    $used_clubs[] = $current_club['club_id'];
                }
            }
        }
        return $matches;
    }
    


    private function update_standings_opponents(&$standings, $club1_id, $club2_id) {
        foreach ($standings as &$standing) {
            if ($standing['club_id'] == $club1_id && $club2_id > 0) {
                // Only add if not already in opponents list
                if (!in_array($club2_id, $standing['opponents'])) {
                    $standing['opponents'][] = $club2_id;
                }
            } elseif ($standing['club_id'] == $club2_id && $club1_id > 0) {
                // Only add if not already in opponents list
                if (!in_array($club1_id, $standing['opponents'])) {
                    $standing['opponents'][] = $club1_id;
                }
            }
        }
    }

    public function ajax_save_match_result() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $tournament_id = isset($_POST['tournament_id']) ? intval($_POST['tournament_id']) : 0;
        $round_number = isset($_POST['round_number']) ? intval($_POST['round_number']) : 0;
        $match_index = isset($_POST['match_index']) ? intval($_POST['match_index']) : 0;
        $club1_id = isset($_POST['club1_id']) ? intval($_POST['club1_id']) : 0;
        $club2_id = isset($_POST['club2_id']) ? intval($_POST['club2_id']) : 0;
        $club1_score = isset($_POST['club1_score']) ? intval($_POST['club1_score']) : 0;
        $club2_score = isset($_POST['club2_score']) ? intval($_POST['club2_score']) : 0;
        
        if (!$tournament_id || !$round_number || $match_index < 0) {
            wp_send_json_error(['message' => __('Invalid tournament or match information.', 'obzg')]);
        }

        // Get tournament rounds
        $rounds = self::get_tournament_rounds($tournament_id);
        if (empty($rounds)) {
            wp_send_json_error(['message' => __('No rounds found for tournament.', 'obzg')]);
        }
        
        // Find the correct round
        $target_round = null;
        $round_index = null;
        foreach ($rounds as $index => $round) {
            if ($round['round'] == $round_number) {
                $target_round = $round;
                $round_index = $index;
                break;
            }
        }
        
        if (!$target_round || !isset($target_round['matches'][$match_index])) {
            wp_send_json_error(['message' => __('Match not found in tournament.', 'obzg')]);
        }

        // Update the match result in rounds
        $rounds[$round_index]['matches'][$match_index]['result'] = [
            'club1_score' => $club1_score,
            'club2_score' => $club2_score,
            'timestamp' => current_time('mysql')
        ];

        // Save updated rounds
        self::set_tournament_rounds($tournament_id, $rounds);

        // Update standings
        $this->update_tournament_standings_from_match($tournament_id, $club1_id, $club2_id, $club1_score, $club2_score);

        wp_send_json_success(['message' => 'Match result saved successfully']);
    }

    private function update_tournament_standings_from_match($tournament_id, $club1_id, $club2_id, $club1_score, $club2_score) {
        $standings = self::get_tournament_standings($tournament_id);
        
        if (empty($standings)) {
            return;
        }
        
        // Update standings for club1
        foreach ($standings as &$standing) {
            if ($standing['club_id'] == $club1_id) {
                $standing['games_played']++;
                if ($club1_score > $club2_score) {
                    $standing['wins']++;
                    $standing['points'] += 3;
                } elseif ($club1_score < $club2_score) {
                    $standing['losses']++;
                } else {
                    $standing['draws']++;
                    $standing['points'] += 1;
                }
            } elseif ($standing['club_id'] == $club2_id && $club2_id > 0) {
                $standing['games_played']++;
                if ($club2_score > $club1_score) {
                    $standing['wins']++;
                    $standing['points'] += 3;
                } elseif ($club2_score < $club1_score) {
                    $standing['losses']++;
                } else {
                    $standing['draws']++;
                    $standing['points'] += 1;
                }
            }
        }
        
        // Handle X matches (automatic 6:0 results) - update standings immediately
        if ($club2_id == 0) { // X opponent
            foreach ($standings as &$standing) {
                if ($standing['club_id'] == $club1_id) {
                    // Club1 automatically gets 3 points for beating X
                    $standing['wins']++;
                    $standing['points'] += 3;
                }
            }
        }
        
        // Update opponents list to prevent rematches
        $this->update_standings_opponents($standings, $club1_id, $club2_id);
        
        self::set_tournament_standings($tournament_id, $standings);
    }

    private function update_tournament_standings($tournament_id, $match_id, $club1_score, $club2_score) {
        $standings = self::get_tournament_standings($tournament_id);
        $rounds = self::get_tournament_rounds($tournament_id);
        
        // Find the match in rounds
        $match_found = false;
        foreach ($rounds as &$round) {
            foreach ($round['matches'] as &$match) {
                if ($match['match_id'] == $match_id) {
                    $match['result'] = ['club1_score' => $club1_score, 'club2_score' => $club2_score];
                    $match_found = true;
                    
                    // Update standings
                    $club1_id = $match['club1_id'];
                    $club2_id = $match['club2_id'];
                    
                    foreach ($standings as &$standing) {
                        if ($standing['club_id'] == $club1_id) {
                            $standing['games_played']++;
                            if ($club1_score > $club2_score) {
                                $standing['wins']++;
                                $standing['points'] += 3;
                            } elseif ($club1_score < $club2_score) {
                                $standing['losses']++;
                            } else {
                                $standing['draws']++;
                                $standing['points'] += 1;
                            }
                        } elseif ($standing['club_id'] == $club2_id && $club2_id > 0) {
                            $standing['games_played']++;
                            if ($club2_score > $club1_score) {
                                $standing['wins']++;
                                $standing['points'] += 3;
                            } elseif ($club2_score < $club1_score) {
                                $standing['losses']++;
                            } else {
                                $standing['draws']++;
                                $standing['points'] += 1;
                            }
                        }
                    }
                    break 2;
                }
            }
        }
        
        if ($match_found) {
            self::set_tournament_standings($tournament_id, $standings);
            self::set_tournament_rounds($tournament_id, $rounds);
        }
    }

    public function ajax_get_tournament_standings() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $tournament_id = isset($_POST['tournament_id']) ? intval($_POST['tournament_id']) : 0;
        
        if (!$tournament_id) {
            wp_send_json_error(['message' => __('Invalid tournament ID.', 'obzg')]);
        }

        $standings = self::get_tournament_standings($tournament_id);
        $rounds = self::get_tournament_rounds($tournament_id);
        
        wp_send_json_success([
            'standings' => $standings,
            'rounds' => $rounds
        ]);
    }

    // Team Management AJAX handlers
    public function ajax_add_team_to_tournament() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $tournament_id = isset($_POST['tournament_id']) ? intval($_POST['tournament_id']) : 0;
        $team_id = isset($_POST['team_id']) ? intval($_POST['team_id']) : 0;
        
        if (!$tournament_id || !$team_id) {
            wp_send_json_error(['message' => __('Invalid tournament or team ID.', 'obzg')]);
        }

        $clubs = self::get_tournament_clubs($tournament_id);
        $max_teams = self::get_tournament_max_teams($tournament_id);
        
        // Check if tournament is full
        if ($max_teams > 0 && count($clubs) >= $max_teams) {
            wp_send_json_error(['message' => __('Tournament is full. Cannot add more teams.', 'obzg')]);
        }

        // Check if team is already in tournament
        if (in_array($team_id, $clubs)) {
            wp_send_json_error(['message' => __('Team is already in the tournament.', 'obzg')]);
        }

        // Add team to tournament
        $clubs[] = $team_id;
        self::set_tournament_clubs($tournament_id, $clubs);

        // Update standings if they exist
        $standings = self::get_tournament_standings($tournament_id);
        if (!empty($standings)) {
            $team = get_post($team_id);
            if ($team) {
                $standings[] = [
                    'club_id' => $team_id,
                    'club_name' => $team->post_title,
                    'points' => 0,
                    'games_played' => 0,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                    'opponents' => []
                ];
                self::set_tournament_standings($tournament_id, $standings);
            }
        }

        wp_send_json_success(['message' => 'Team added successfully']);
    }

    public function ajax_remove_team_from_tournament() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $tournament_id = isset($_POST['tournament_id']) ? intval($_POST['tournament_id']) : 0;
        $team_id = isset($_POST['team_id']) ? intval($_POST['team_id']) : 0;
        
        if (!$tournament_id || !$team_id) {
            wp_send_json_error(['message' => __('Invalid tournament or team ID.', 'obzg')]);
        }

        $clubs = self::get_tournament_clubs($tournament_id);
        
        // Remove team from tournament
        $clubs = array_filter($clubs, function($club_id) use ($team_id) {
            return $club_id != $team_id;
        });
        self::set_tournament_clubs($tournament_id, $clubs);

        // Update standings if they exist
        $standings = self::get_tournament_standings($tournament_id);
        if (!empty($standings)) {
            $standings = array_filter($standings, function($standing) use ($team_id) {
                return $standing['club_id'] != $team_id;
            });
            self::set_tournament_standings($tournament_id, array_values($standings));
        }

        wp_send_json_success(['message' => 'Team removed successfully']);
    }

    public function ajax_get_available_teams() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $tournament_id = isset($_POST['tournament_id']) ? intval($_POST['tournament_id']) : 0;
        
        if (!$tournament_id) {
            wp_send_json_error(['message' => __('Invalid tournament ID.', 'obzg')]);
        }

        $tournament_clubs = self::get_tournament_clubs($tournament_id);
        
        // Get all clubs
        $args = [
            'post_type'      => 'obzg_club',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];
        $query = new WP_Query( $args );
        $available_clubs = [];
        
        foreach ( $query->posts as $post ) {
            if (!in_array($post->ID, $tournament_clubs)) {
                $available_clubs[] = [
                    'id'    => $post->ID,
                    'title' => $post->post_title,
                    'city' => get_post_meta($post->ID, '_obzg_club_city', true),
                    'president' => get_post_meta($post->ID, '_obzg_club_president', true),
                ];
            }
        }
        
        wp_send_json_success(['clubs' => $available_clubs]);
    }

    public function ajax_add_random_teams() {
        check_ajax_referer( 'obzg_admin_nonce' );
        $tournament_id = isset($_POST['tournament_id']) ? intval($_POST['tournament_id']) : 0;
        $num_teams = isset($_POST['num_teams']) ? intval($_POST['num_teams']) : 16;
        
        if (!$tournament_id) {
            wp_send_json_error(['message' => __('Invalid tournament ID.', 'obzg')]);
        }

        // Get all available clubs
        $args = [
            'post_type'      => 'obzg_club',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];
        $query = new WP_Query( $args );
        
        if ($query->post_count < $num_teams) {
            wp_send_json_error(['message' => __('Not enough clubs available. Only ' . $query->post_count . ' clubs exist.', 'obzg')]);
        }

        // Get current tournament clubs
        $current_clubs = self::get_tournament_clubs($tournament_id);
        $max_teams = self::get_tournament_max_teams($tournament_id);
        
        // Check if adding teams would exceed limit
        if ($max_teams > 0 && (count($current_clubs) + $num_teams) > $max_teams) {
            $available_slots = $max_teams - count($current_clubs);
            wp_send_json_error(['message' => __('Tournament limit would be exceeded. Only ' . $available_slots . ' slots available.', 'obzg')]);
        }

        // Get all club IDs
        $all_club_ids = [];
        foreach ($query->posts as $post) {
            if (!in_array($post->ID, $current_clubs)) {
                $all_club_ids[] = $post->ID;
            }
        }

        // Randomly select teams
        shuffle($all_club_ids);
        $selected_teams = array_slice($all_club_ids, 0, $num_teams);

        // Add teams to tournament
        $new_clubs = array_merge($current_clubs, $selected_teams);
        self::set_tournament_clubs($tournament_id, $new_clubs);

        // Update standings if they exist
        $standings = self::get_tournament_standings($tournament_id);
        if (!empty($standings)) {
            foreach ($selected_teams as $team_id) {
                $team = get_post($team_id);
                if ($team) {
                    $standings[] = [
                        'club_id' => $team_id,
                        'club_name' => $team->post_title,
                        'points' => 0,
                        'games_played' => 0,
                        'wins' => 0,
                        'losses' => 0,
                        'draws' => 0,
                        'opponents' => []
                    ];
                }
            }
            self::set_tournament_standings($tournament_id, $standings);
        }

        wp_send_json_success([
            'message' => $num_teams . ' teams added successfully!',
            'added_teams' => $selected_teams
        ]);
    }

    public function ajax_add_sample_clubs() {
        check_ajax_referer( 'obzg_admin_nonce' );
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $sample_clubs = [
            [
                'title' => 'Bocce Club Ljubljana',
                'email' => 'info@bocceljubljana.si',
                'phone' => '+386 1 234 5678',
                'address' => 'Trg Republike 1',
                'city' => 'Ljubljana',
                'city_number' => '1000',
                'president' => 'Marko Novak',
                'league' => ['Premier League']
            ],
            [
                'title' => 'Bocce Club Maribor',
                'email' => 'contact@boccemaribor.si',
                'phone' => '+386 2 345 6789',
                'address' => 'Gosposka ulica 15',
                'city' => 'Maribor',
                'city_number' => '2000',
                'president' => 'Ana Kovač',
                'league' => ['Premier League']
            ],
            [
                'title' => 'Bocce Club Celje',
                'email' => 'info@boccecelje.si',
                'phone' => '+386 3 456 7890',
                'address' => 'Slovenska cesta 25',
                'city' => 'Celje',
                'city_number' => '3000',
                'president' => 'Peter Horvat',
                'league' => ['First Division']
            ],
            [
                'title' => 'Bocce Club Koper',
                'email' => 'contact@boccekoper.si',
                'phone' => '+386 5 678 9012',
                'address' => 'Pristaniška ulica 8',
                'city' => 'Koper',
                'city_number' => '6000',
                'president' => 'Maja Župančič',
                'league' => ['Premier League']
            ],
            [
                'title' => 'Bocce Club Novo Mesto',
                'email' => 'info@boccenovomesto.si',
                'phone' => '+386 7 890 1234',
                'address' => 'Glavni trg 12',
                'city' => 'Novo Mesto',
                'city_number' => '8000',
                'president' => 'Janez Mlakar',
                'league' => ['First Division']
            ],
            [
                'title' => 'Bocce Club Kranj',
                'email' => 'contact@boccekranj.si',
                'phone' => '+386 4 567 8901',
                'address' => 'Tomšičeva ulica 5',
                'city' => 'Kranj',
                'city_number' => '4000',
                'president' => 'Irena Potočnik',
                'league' => ['Second Division']
            ],
            [
                'title' => 'Bocce Club Velenje',
                'email' => 'info@boccevelenje.si',
                'phone' => '+386 3 789 0123',
                'address' => 'Trg Komandanta Staneta 3',
                'city' => 'Velenje',
                'city_number' => '3320',
                'president' => 'Boris Krajnc',
                'league' => ['First Division']
            ],
            [
                'title' => 'Bocce Club Ptuj',
                'email' => 'contact@bocceptuj.si',
                'phone' => '+386 2 890 1234',
                'address' => 'Slovenski trg 7',
                'city' => 'Ptuj',
                'city_number' => '2250',
                'president' => 'Nina Vrhovnik',
                'league' => ['Second Division']
            ],
            [
                'title' => 'Bocce Club Trbovlje',
                'email' => 'info@boccetrbovlje.si',
                'phone' => '+386 3 456 7890',
                'address' => 'Trg svobode 10',
                'city' => 'Trbovlje',
                'city_number' => '1420',
                'president' => 'Rok Štrukelj',
                'league' => ['Third Division']
            ],
            [
                'title' => 'Bocce Club Domžale',
                'email' => 'contact@boccedomzale.si',
                'phone' => '+386 1 567 8901',
                'address' => 'Ljubljanska cesta 20',
                'city' => 'Domžale',
                'city_number' => '1230',
                'president' => 'Tina Kovačič',
                'league' => ['Second Division']
            ],
            [
                'title' => 'Bocce Club Kamnik',
                'email' => 'info@boccekamnik.si',
                'phone' => '+386 1 890 1234',
                'address' => 'Glavni trg 15',
                'city' => 'Kamnik',
                'city_number' => '1241',
                'president' => 'Andrej Zupan',
                'league' => ['Third Division']
            ],
            [
                'title' => 'Bocce Club Jesenice',
                'email' => 'contact@boccejesenice.si',
                'phone' => '+386 4 123 4567',
                'address' => 'Cesta svobode 25',
                'city' => 'Jesenice',
                'city_number' => '4270',
                'president' => 'Maja Novak',
                'league' => ['First Division']
            ],
            [
                'title' => 'Bocce Club Škofja Loka',
                'email' => 'info@bocceskofjaloka.si',
                'phone' => '+386 4 234 5678',
                'address' => 'Mestni trg 8',
                'city' => 'Škofja Loka',
                'city_number' => '4220',
                'president' => 'Peter Kovač',
                'league' => ['Second Division']
            ],
            [
                'title' => 'Bocce Club Radovljica',
                'email' => 'contact@bocceradovljica.si',
                'phone' => '+386 4 345 6789',
                'address' => 'Linhartov trg 12',
                'city' => 'Radovljica',
                'city_number' => '4240',
                'president' => 'Ana Horvat',
                'league' => ['Third Division']
            ],
            [
                'title' => 'Bocce Club Idrija',
                'email' => 'info@bocceidrija.si',
                'phone' => '+386 5 456 7890',
                'address' => 'Vodnikova ulica 5',
                'city' => 'Idrija',
                'city_number' => '5280',
                'president' => 'Marko Župančič',
                'league' => ['Third Division']
            ],
            [
                'title' => 'Bocce Club Postojna',
                'email' => 'contact@boccepostojna.si',
                'phone' => '+386 5 567 8901',
                'address' => 'Trg republike 3',
                'city' => 'Postojna',
                'city_number' => '6230',
                'president' => 'Irena Mlakar',
                'league' => ['Second Division']
            ]
        ];

        $created_clubs = [];
        $errors = [];

        foreach ($sample_clubs as $club_data) {
            // Check if club already exists
            $existing_club = get_page_by_title($club_data['title'], OBJECT, 'obzg_club');
            if ($existing_club) {
                $errors[] = 'Club "' . $club_data['title'] . '" already exists.';
                continue;
            }

            // Create club post
            $post_data = [
                'post_title'   => $club_data['title'],
                'post_content' => 'Sample bocce club for testing purposes.',
                'post_type'    => 'obzg_club',
                'post_status'  => 'publish',
            ];

            $club_id = wp_insert_post($post_data);
            
            if (is_wp_error($club_id)) {
                $errors[] = 'Failed to create club "' . $club_data['title'] . '": ' . $club_id->get_error_message();
                continue;
            }

            // Add club metadata
            update_post_meta($club_id, '_obzg_club_email', $club_data['email']);
            update_post_meta($club_id, '_obzg_club_phone', $club_data['phone']);
            update_post_meta($club_id, '_obzg_club_address', $club_data['address']);
            update_post_meta($club_id, '_obzg_club_city', $club_data['city']);
            update_post_meta($club_id, '_obzg_club_city_number', $club_data['city_number']);
            update_post_meta($club_id, '_obzg_club_president', $club_data['president']);
            update_post_meta($club_id, '_obzg_club_league', $club_data['league']);

            $created_clubs[] = [
                'id' => $club_id,
                'title' => $club_data['title'],
                'city' => $club_data['city']
            ];
        }

        if (empty($created_clubs)) {
            wp_send_json_error(['message' => 'No clubs were created. ' . implode(' ', $errors)]);
        }

        $success_message = count($created_clubs) . ' clubs created successfully!';
        if (!empty($errors)) {
            $success_message .= ' Errors: ' . implode(' ', $errors);
        }

        wp_send_json_success([
            'message' => $success_message,
            'created_clubs' => $created_clubs,
            'errors' => $errors
        ]);
    }

    // Enqueue admin JS/CSS
    public function enqueue_admin_assets( $hook ) {
        if ( $hook === 'toplevel_page_obzg_manage_clubs' ) {
            // Bootstrap 5 CSS
            wp_enqueue_style(
                'bootstrap-5',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                [],
                '5.3.3'
            );
            // Bootstrap Icons
            wp_enqueue_style(
                'bootstrap-icons',
                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
                [],
                '1.11.3'
            );
            // Bootstrap 5 JS (with Popper)
            wp_enqueue_script(
                'bootstrap-5',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
                [],
                '5.3.3',
                true
            );
            wp_enqueue_script(
                'obzg-admin-js',
                plugins_url( 'obzg/assets/js/admin-clubs.js', __FILE__ ),
                [ 'jquery', 'wp-util', 'bootstrap-5' ],
                '1.0.0',
                true
            );
            wp_localize_script( 'obzg-admin-js', 'OBZG_AJAX', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'obzg_admin_nonce' ),
            ] );
            wp_enqueue_style(
                'obzg-admin-css',
                plugins_url( 'obzg/assets/css/admin-clubs.css', __FILE__ ),
                [ 'bootstrap-5' ],
                '1.0.0'
            );
        } elseif ( $hook === 'toplevel_page_obzg_manage_players' ) {
            wp_enqueue_style(
                'bootstrap-5',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                [],
                '5.3.3'
            );
            wp_enqueue_style(
                'bootstrap-icons',
                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
                [],
                '1.11.3'
            );
            wp_enqueue_script(
                'bootstrap-5',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
                [],
                '5.3.3',
                true
            );
            wp_enqueue_script(
                'obzg-admin-players-js',
                plugins_url( 'obzg/assets/js/admin-players.js', __FILE__ ),
                [ 'jquery', 'wp-util', 'bootstrap-5' ],
                '1.0.0',
                true
            );
            wp_localize_script( 'obzg-admin-players-js', 'OBZG_AJAX_PLAYERS', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'obzg_admin_nonce' ),
            ] );
            wp_enqueue_style(
                'obzg-admin-players-css',
                plugins_url( 'obzg/assets/css/admin-players.css', __FILE__ ),
                [ 'bootstrap-5' ],
                '1.0.0'
            );
        } elseif ( $hook === 'toplevel_page_obzg_manage_matches' ) {
            wp_enqueue_style(
                'bootstrap-5',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                [],
                '5.3.3'
            );
            wp_enqueue_script(
                'bootstrap-5',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
                [],
                '5.3.3',
                true
            );
            wp_enqueue_script(
                'obzg-admin-matches-js',
                plugins_url( 'obzg/assets/js/admin-matches.js', __FILE__ ),
                [ 'jquery', 'wp-util', 'bootstrap-5' ],
                '1.0.0',
                true
            );
            wp_localize_script( 'obzg-admin-matches-js', 'OBZG_AJAX_MATCHES', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'obzg_admin_nonce' ),
            ] );
            wp_enqueue_style(
                'obzg-admin-matches-css',
                plugins_url( 'obzg/assets/css/admin-matches.css', __FILE__ ),
                [ 'bootstrap-5' ],
                '1.0.0'
            );
        } elseif ( $hook === 'toplevel_page_obzg_manage_tournaments' ) {
            wp_enqueue_style(
                'bootstrap-5',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                [],
                '5.3.3'
            );
            wp_enqueue_style(
                'bootstrap-icons',
                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
                [],
                '1.11.3'
            );
            wp_enqueue_script(
                'bootstrap-5',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
                [],
                '5.3.3',
                true
            );
            wp_enqueue_script(
                'obzg-admin-tournaments-js',
                plugins_url( 'obzg/assets/js/admin-tournaments.js', __FILE__ ),
                [ 'jquery', 'wp-util', 'bootstrap-5' ],
                '1.0.0',
                true
            );
            wp_localize_script( 'obzg-admin-tournaments-js', 'OBZG_AJAX_TOURNAMENTS', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'obzg_admin_nonce' ),
            ] );
            wp_enqueue_style(
                'obzg-admin-tournaments-css',
                plugins_url( 'obzg/assets/css/admin-tournaments.css', __FILE__ ),
                [ 'bootstrap-5' ],
                '1.0.0'
            );
        }
    }

    /**
     * Register REST API routes for React frontend
     */
    public function register_rest_routes() {
        // Authentication endpoints
        // Registration disabled for public (super admin only) – keeping route but locked
        register_rest_route('obzg/v1', '/auth/register', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_auth_register'],
            'permission_callback' => [$this, 'rest_check_super_admin']
        ]);

        register_rest_route('obzg/v1', '/auth/login', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_auth_login'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('obzg/v1', '/auth/logout', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_auth_logout'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/auth/me', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_auth_me'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        // Users listing for super admin
        register_rest_route('obzg/v1', '/users', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_users'],
            'permission_callback' => [$this, 'rest_check_super_admin']
        ]);
        register_rest_route('obzg/v1', '/users/(?P<id>\\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_update_user'],
            'permission_callback' => [$this, 'rest_check_super_admin']
        ]);
        register_rest_route('obzg/v1', '/users/(?P<id>\\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'rest_delete_user'],
            'permission_callback' => [$this, 'rest_check_super_admin']
        ]);

        // Temporary endpoint to delete test users
        register_rest_route('obzg/v1', '/auth/delete-test-users', [
            'methods' => 'DELETE',
            'callback' => [$this, 'rest_delete_test_users'],
            'permission_callback' => '__return_true'
        ]);

        // Temporary endpoint to delete specific user
        register_rest_route('obzg/v1', '/auth/delete-user', [
            'methods' => 'DELETE',
            'callback' => [$this, 'rest_delete_specific_user'],
            'permission_callback' => '__return_true'
        ]);

        // Clubs endpoints
        register_rest_route('obzg/v1', '/clubs', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_clubs'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/clubs/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_club'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/clubs', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_save_club'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/clubs/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'rest_delete_club'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        // Players endpoints
        register_rest_route('obzg/v1', '/players', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_players'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/players/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_player'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/players', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_save_player'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/players/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'rest_delete_player'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        // Tournaments endpoints
        register_rest_route('obzg/v1', '/tournaments', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_tournaments'],
            'permission_callback' => [$this, 'rest_check_public_read']
        ]);

        register_rest_route('obzg/v1', '/tournaments/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_tournament'],
            'permission_callback' => [$this, 'rest_check_public_read']
        ]);

        // Tournament groups management (auth required)
        register_rest_route('obzg/v1', '/tournaments/(?P<id>\d+)/groups', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_update_tournament_groups'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/tournaments/(?P<id>\d+)/groups/randomize', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_randomize_tournament_groups'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/tournaments', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_save_tournament'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/tournaments/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'rest_delete_tournament'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        // Duplicate tournament (super admin only)
        register_rest_route('obzg/v1', '/tournaments/(?P<id>\d+)/duplicate', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_duplicate_tournament'],
            'permission_callback' => [$this, 'rest_check_super_admin']
        ]);

        // Tournament specific endpoints
        register_rest_route('obzg/v1', '/tournaments/(?P<id>\d+)/generate-round', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_generate_swiss_round'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/tournaments/(?P<id>\d+)/save-result', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_save_match_result'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/tournaments/(?P<id>\d+)/add-team', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_add_team_to_tournament'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/tournaments/(?P<id>\d+)/remove-team', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_remove_team_from_tournament'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/tournaments/(?P<id>\d+)/add-sample-clubs', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_add_sample_clubs'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);

        register_rest_route('obzg/v1', '/tournaments/(?P<id>\d+)/import-teams-excel', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_import_teams_excel'],
            'permission_callback' => [$this, 'rest_check_auth']
        ]);
    }

    // REST API callback methods
    public function rest_get_clubs($request) {
        $clubs = get_posts([
            'post_type' => 'obzg_club',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);

        $data = [];
        foreach ($clubs as $club) {
            $data[] = [
                'id' => $club->ID,
                'title' => $club->post_title,
                'email' => get_post_meta($club->ID, '_obzg_club_email', true),
                'phone' => get_post_meta($club->ID, '_obzg_club_phone', true),
                'address' => get_post_meta($club->ID, '_obzg_club_address', true),
                'city' => get_post_meta($club->ID, '_obzg_club_city', true),
                'city_number' => get_post_meta($club->ID, '_obzg_club_city_number', true),
                'president' => get_post_meta($club->ID, '_obzg_club_president', true),
                'league' => get_post_meta($club->ID, '_obzg_club_league', true)
            ];
        }

        return new WP_REST_Response($data, 200);
    }

    public function rest_get_club($request) {
        $club_id = $request['id'];
        $club = get_post($club_id);
        
        if (!$club || $club->post_type !== 'obzg_club') {
            return new WP_Error('not_found', 'Club not found', ['status' => 404]);
        }

        // Prefer persisted groups; otherwise compute
        $persisted_groups = $this->get_tournament_groups($tournament->ID);
        $groups_to_send = !empty($persisted_groups) ? $persisted_groups : $groups;

        $group_count = get_post_meta($tournament->ID, '_obzg_tournament_group_count', true);
        $data = [
            'id' => $club->ID,
            'title' => $club->post_title,
            'email' => get_post_meta($club->ID, '_obzg_club_email', true),
            'phone' => get_post_meta($club->ID, '_obzg_club_phone', true),
            'address' => get_post_meta($club->ID, '_obzg_club_address', true),
            'city' => get_post_meta($club->ID, '_obzg_club_city', true),
            'city_number' => get_post_meta($club->ID, '_obzg_club_city_number', true),
            'president' => get_post_meta($club->ID, '_obzg_club_president', true),
            'league' => get_post_meta($club->ID, '_obzg_club_league', true)
        ];

        return new WP_REST_Response($data, 200);
    }

    public function rest_save_club($request) {
        $params = $request->get_params();
        
        $club_data = [
            'post_title' => sanitize_text_field($params['title']),
            'post_type' => 'obzg_club',
            'post_status' => 'publish'
        ];

        if (isset($params['id'])) {
            $club_data['ID'] = intval($params['id']);
            $club_id = wp_update_post($club_data);
        } else {
            $club_id = wp_insert_post($club_data);
        }

        if (is_wp_error($club_id)) {
            return new WP_Error('save_failed', 'Failed to save club', ['status' => 500]);
        }

        // Save meta fields
        $meta_fields = ['email', 'phone', 'address', 'city', 'city_number', 'president', 'league'];
        foreach ($meta_fields as $field) {
            if (isset($params[$field])) {
                update_post_meta($club_id, '_obzg_club_' . $field, sanitize_text_field($params[$field]));
            }
        }

        return new WP_REST_Response(['id' => $club_id, 'success' => true], 200);
    }

    public function rest_delete_club($request) {
        $club_id = $request['id'];
        $result = wp_delete_post($club_id, true);
        
        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete club', ['status' => 500]);
        }

        return new WP_REST_Response(['success' => true], 200);
    }

    public function rest_get_players($request) {
        $players = get_posts([
            'post_type' => 'obzg_player',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);

        $data = [];
        foreach ($players as $player) {
            $data[] = [
                'id' => $player->ID,
                'title' => $player->post_title,
                'email' => get_post_meta($player->ID, '_obzg_player_email', true),
                'phone' => get_post_meta($player->ID, '_obzg_player_phone', true),
                'club_id' => get_post_meta($player->ID, '_obzg_player_club', true)
            ];
        }

        return new WP_REST_Response($data, 200);
    }

    public function rest_get_player($request) {
        $player_id = $request['id'];
        $player = get_post($player_id);
        
        if (!$player || $player->post_type !== 'obzg_player') {
            return new WP_Error('not_found', 'Player not found', ['status' => 404]);
        }

        $data = [
            'id' => $player->ID,
            'title' => $player->post_title,
            'email' => get_post_meta($player->ID, '_obzg_player_email', true),
            'phone' => get_post_meta($player->ID, '_obzg_player_phone', true),
            'club_id' => get_post_meta($player->ID, '_obzg_player_club', true)
        ];

        return new WP_REST_Response($data, 200);
    }

    public function rest_save_player($request) {
        $params = $request->get_params();
        
        $player_data = [
            'post_title' => sanitize_text_field($params['title']),
            'post_type' => 'obzg_player',
            'post_status' => 'publish'
        ];

        if (isset($params['id'])) {
            $player_data['ID'] = intval($params['id']);
            $player_id = wp_update_post($player_data);
        } else {
            $player_id = wp_insert_post($player_data);
        }

        if (is_wp_error($player_id)) {
            return new WP_Error('save_failed', 'Failed to save player', ['status' => 500]);
        }

        // Save meta fields
        $meta_fields = ['email', 'phone', 'club'];
        foreach ($meta_fields as $field) {
            if (isset($params[$field])) {
                update_post_meta($player_id, '_obzg_player_' . $field, sanitize_text_field($params[$field]));
            }
        }

        return new WP_REST_Response(['id' => $player_id, 'success' => true], 200);
    }

    public function rest_delete_player($request) {
        $player_id = $request['id'];
        $result = wp_delete_post($player_id, true);
        
        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete player', ['status' => 500]);
        }

        return new WP_REST_Response(['success' => true], 200);
    }

    public function rest_get_tournaments($request) {
        $tournaments = get_posts([
            'post_type' => 'obzg_tournament',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);

        $data = [];
        foreach ($tournaments as $tournament) {
            $clubs = self::get_tournament_clubs($tournament->ID);
            $rounds = self::get_tournament_rounds($tournament->ID);
            $groups = $this->get_tournament_groups($tournament->ID);
            
            // Auto-update tournament status
            $updated_status = $this->auto_update_tournament_status($tournament->ID, $clubs, $rounds);
            
            $data[] = [
                'id' => $tournament->ID,
                'title' => $tournament->post_title,
                'status' => $updated_status,
                'start_date' => get_post_meta($tournament->ID, '_obzg_tournament_start_date', true),
                'end_date' => get_post_meta($tournament->ID, '_obzg_tournament_end_date', true),
                'location' => get_post_meta($tournament->ID, '_obzg_tournament_location', true),
                'desc' => get_post_meta($tournament->ID, '_obzg_tournament_desc', true),
                'tournament_type' => get_post_meta($tournament->ID, '_obzg_tournament_type', true),
                'max_teams' => get_post_meta($tournament->ID, '_obzg_tournament_max_teams', true),
                'num_rounds' => get_post_meta($tournament->ID, '_obzg_tournament_num_rounds', true),
                'clubs' => $clubs,
                'rounds' => $rounds,
                'groups' => $groups
            ];
        }

        return new WP_REST_Response($data, 200);
    }

    private function auto_update_tournament_status($tournament_id, $clubs, $rounds) {
        $current_status = get_post_meta($tournament_id, '_obzg_tournament_status', true);
        $start_date = get_post_meta($tournament_id, '_obzg_tournament_start_date', true);
        $end_date = get_post_meta($tournament_id, '_obzg_tournament_end_date', true);
        $today = current_time('Y-m-d');
        
        // Check if tournament has ended (end_date is in the past)
        if ($end_date && $end_date < $today) {
            // Tournament has ended - status should be completed
            $new_status = 'completed';
        }
        // Check if tournament has started (start_date is today or in the past)
        elseif ($start_date && $start_date <= $today) {
            // Tournament is currently active
            $new_status = 'active';
        } else {
            // Tournament hasn't started yet
            $new_status = 'draft';
        }
        
        // Update status if it has changed
        if ($current_status !== $new_status) {
            update_post_meta($tournament_id, '_obzg_tournament_status', $new_status);
        }
        
        return $new_status;
    }

    public function rest_get_tournament($request) {
        $tournament_id = $request['id'];
        $tournament = get_post($tournament_id);
        
        if (!$tournament || $tournament->post_type !== 'obzg_tournament') {
            return new WP_Error('not_found', 'Tournament not found', ['status' => 404]);
        }

        $clubs = self::get_tournament_clubs($tournament->ID);
        $rounds = self::get_tournament_rounds($tournament->ID);
        $optimal_rounds = !empty($clubs) ? (int)ceil(log(count($clubs), 2)) : 0;
        $location_value = get_post_meta($tournament->ID, '_obzg_tournament_location', true);
        $group_count = get_post_meta($tournament->ID, '_obzg_tournament_group_count', true);
        $groups = $this->get_tournament_groups($tournament->ID);
        
        // Auto-update tournament status
        $updated_status = $this->auto_update_tournament_status($tournament->ID, $clubs, $rounds);

        $data = [
            'id' => $tournament->ID,
            'title' => $tournament->post_title,
            'status' => $updated_status,
            'start_date' => get_post_meta($tournament->ID, '_obzg_tournament_start_date', true),
            'end_date' => get_post_meta($tournament->ID, '_obzg_tournament_end_date', true),
            'location' => $location_value,
            'desc' => get_post_meta($tournament->ID, '_obzg_tournament_desc', true),
            'tournament_type' => get_post_meta($tournament->ID, '_obzg_tournament_type', true),
            'max_teams' => get_post_meta($tournament->ID, '_obzg_tournament_max_teams', true),
            'num_rounds' => get_post_meta($tournament->ID, '_obzg_tournament_num_rounds', true),
            'group_count' => $group_count ? intval($group_count) : 1,
            'clubs' => $clubs,
            'rounds' => $rounds,
            'standings' => self::get_tournament_standings($tournament->ID),
            'optimal_rounds' => $optimal_rounds,
            'groups' => $groups
        ];

        return new WP_REST_Response($data, 200);
    }

    public function rest_save_tournament($request) {
        $params = $request->get_params();
        
        $tournament_title = sanitize_text_field($params['title']);
        
        // Check for duplicate tournament names
        $existing_tournaments = get_posts([
            'post_type' => 'obzg_tournament',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query' => [],
            'post_title' => $tournament_title
        ]);
        
        // Filter by exact title match (case-insensitive)
        $duplicate_tournaments = array_filter($existing_tournaments, function($tournament) use ($tournament_title) {
            return strtolower(trim($tournament->post_title)) === strtolower(trim($tournament_title));
        });
        
        if (isset($params['id'])) {
            // For updates, exclude the current tournament from duplicate check
            $current_id = intval($params['id']);
            $duplicate_tournaments = array_filter($duplicate_tournaments, function($tournament) use ($current_id) {
                return $tournament->ID !== $current_id;
            });
        }
        
        if (!empty($duplicate_tournaments)) {
            return new WP_Error('duplicate_name', 'A tournament with this name already exists. Please choose a different name.', ['status' => 400]);
        }
        
        $tournament_data = [
            'post_title' => $tournament_title,
            'post_type' => 'obzg_tournament',
            'post_status' => 'publish'
        ];

        if (isset($params['id'])) {
            $tournament_data['ID'] = intval($params['id']);
            $tournament_id = wp_update_post($tournament_data);
        } else {
            $tournament_id = wp_insert_post($tournament_data);
        }

        if (is_wp_error($tournament_id)) {
            return new WP_Error('save_failed', 'Failed to save tournament', ['status' => 500]);
        }

        // Save meta fields
        $meta_fields = ['status', 'start_date', 'end_date', 'location', 'desc', 'tournament_type', 'max_teams', 'num_rounds', 'group_count'];
        foreach ($meta_fields as $field) {
            if (isset($params[$field])) {
                update_post_meta($tournament_id, '_obzg_tournament_' . $field, sanitize_text_field($params[$field]));
            }
        }

        return new WP_REST_Response(['id' => $tournament_id, 'success' => true], 200);
    }

    public function rest_delete_tournament($request) {
        $tournament_id = $request['id'];
        $result = wp_delete_post($tournament_id, true);
        
        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete tournament', ['status' => 500]);
        }

        return new WP_REST_Response(['success' => true], 200);
    }

    public function rest_duplicate_tournament($request) {
        $tournament_id = intval($request['id']);
        $orig = get_post($tournament_id);
        if (!$orig || $orig->post_type !== 'obzg_tournament') {
            return new WP_Error('not_found', 'Tournament not found', ['status' => 404]);
        }
        // Create new post with "Copy of" prefix to avoid duplicate title validation
        $new_post_id = wp_insert_post([
            'post_title' => 'Copy of ' . $orig->post_title,
            'post_type' => 'obzg_tournament',
            'post_status' => 'publish'
        ]);
        if (is_wp_error($new_post_id)) {
            return $new_post_id;
        }
        // Copy meta
        $meta_keys = ['_obzg_tournament_status','_obzg_tournament_start_date','_obzg_tournament_end_date','_obzg_tournament_location','_obzg_tournament_desc','_obzg_tournament_type','_obzg_tournament_max_teams','_obzg_tournament_num_rounds','_obzg_tournament_group_count'];
        foreach ($meta_keys as $k) {
            $v = get_post_meta($tournament_id, $k, true);
            if ($v !== '') { update_post_meta($new_post_id, $k, $v); }
        }
        // Copy clubs and groups
        $clubs = self::get_tournament_clubs($tournament_id);
        self::set_tournament_clubs($new_post_id, $clubs);
        $groups = get_post_meta($tournament_id, '_obzg_tournament_groups', true);
        if (is_array($groups)) { update_post_meta($new_post_id, '_obzg_tournament_groups', $groups); }
        // Rounds are NOT duplicated to avoid confusion
        return new WP_REST_Response(['id' => $new_post_id], 200);
    }

    public function rest_generate_swiss_round($request) {
        $tournament_id = $request['id'];
        $params = $request->get_params();
        $round_number = isset($params['round_number']) ? intval($params['round_number']) : 1;
        
        error_log("OBZG Debug: Generating round $round_number for tournament $tournament_id");
        error_log("OBZG Debug: Request params: " . print_r($params, true));
        
        // Call the existing AJAX method logic
        $clubs = self::get_tournament_clubs($tournament_id);
        if (empty($clubs)) {
            return new WP_Error('no_clubs', 'No clubs in tournament', ['status' => 400]);
        }

        $existing_rounds = self::get_tournament_rounds($tournament_id);
        
        // Check if round already exists by looking for the round number
        foreach ($existing_rounds as $existing_round) {
            $existing_round_num = isset($existing_round['round']) ? $existing_round['round'] : 
                                 (isset($existing_round['round_number']) ? $existing_round['round_number'] : 0);
            if ($existing_round_num == $round_number) {
                return new WP_Error('round_exists', 'Round already exists', ['status' => 400]);
            }
        }

        $optimal_rounds = (int)ceil(log(count($clubs), 2));
        if ($round_number > $optimal_rounds) {
            return new WP_Error('too_many_rounds', 'Maximum rounds exceeded', ['status' => 400]);
        }

        $standings = self::get_tournament_standings($tournament_id);
        if (empty($standings)) {
            // Initialize standings for first round
            $standings = [];
            foreach ($clubs as $club_id) {
                $club = get_post($club_id);
                $standings[] = [
                    'club_id' => $club_id,
                    'club_name' => $club->post_title,
                    'points' => 0,
                    'wins' => 0,
                    'draws' => 0,
                    'losses' => 0,
                    'games_played' => 0,
                    'opponents' => []
                ];
            }
        }

        $matches = $this->generate_swiss_round($standings, $round_number);
        $new_round = [
            'round' => $round_number,
            'round_number' => $round_number,
            'matches' => $matches
        ];
        $existing_rounds[] = $new_round;
        usort($existing_rounds, function($a, $b) {
            $a_round = isset($a['round']) ? $a['round'] : (isset($a['round_number']) ? $a['round_number'] : 0);
            $b_round = isset($b['round']) ? $b['round'] : (isset($b['round_number']) ? $b['round_number'] : 0);
            return $a_round - $b_round;
        });
        
        self::set_tournament_rounds($tournament_id, $existing_rounds);

        return new WP_REST_Response(['success' => true, 'round' => $new_round], 200);
    }

    public function rest_save_match_result($request) {
        $tournament_id = $request['id'];
        $params = $request->get_params();
        
        $round_number = isset($params['round_number']) ? intval($params['round_number']) : 1;
        $match_index = isset($params['match_index']) ? intval($params['match_index']) : 0;
        $club1_id = isset($params['club1_id']) ? intval($params['club1_id']) : 0;
        $club2_id = isset($params['club2_id']) ? intval($params['club2_id']) : 0;
        $club1_score = isset($params['club1_score']) ? intval($params['club1_score']) : 0;
        $club2_score = isset($params['club2_score']) ? intval($params['club2_score']) : 0;

        // Debug logging
        error_log("OBZG Debug: Saving match result for tournament $tournament_id");
        error_log("OBZG Debug: round_number=$round_number, match_index=$match_index");
        error_log("OBZG Debug: club1_id=$club1_id, club2_id=$club2_id");
        error_log("OBZG Debug: club1_score=$club1_score, club2_score=$club2_score");

        if (!$round_number || $match_index < 0) {
            error_log("OBZG Debug: Invalid match data - round_number=$round_number, match_index=$match_index");
            return new WP_Error('invalid_data', 'Invalid match data', ['status' => 400]);
        }

        // Allow BYE matches (club_id = 0) but require at least one valid club
        if ($club1_id === 0 && $club2_id === 0) {
            error_log("OBZG Debug: Both clubs are BYE");
            return new WP_Error('invalid_data', 'At least one club must be valid (not BYE)', ['status' => 400]);
        }

        $rounds = self::get_tournament_rounds($tournament_id);
        error_log("OBZG Debug: Found " . count($rounds) . " rounds");
        
        // Debug: Log all rounds
        foreach ($rounds as $i => $round) {
            $round_key = isset($round['round']) ? $round['round'] : (isset($round['round_number']) ? $round['round_number'] : 'unknown');
            error_log("OBZG Debug: Round $i - round_key=" . $round_key . ", matches=" . count($round['matches']));
        }
        
        $round_index = null;
        foreach ($rounds as $i => $round) {
            $round_key = isset($round['round']) ? $round['round'] : (isset($round['round_number']) ? $round['round_number'] : 0);
            if ($round_key == $round_number) {
                $round_index = $i;
                error_log("OBZG Debug: Found round at index $i");
                break;
            }
        }

        if ($round_index === null) {
            error_log("OBZG Debug: Round not found for round_number=$round_number");
            return new WP_Error('match_not_found', 'Round not found', ['status' => 404]);
        }

        if (!isset($rounds[$round_index]['matches'][$match_index])) {
            error_log("OBZG Debug: Match not found at index $match_index in round $round_index");
            error_log("OBZG Debug: Available matches: " . count($rounds[$round_index]['matches']));
            return new WP_Error('match_not_found', 'Match not found', ['status' => 404]);
        }

        $rounds[$round_index]['matches'][$match_index]['result'] = [
            'club1_score' => $club1_score,
            'club2_score' => $club2_score
        ];

        self::set_tournament_rounds($tournament_id, $rounds);
        $this->update_tournament_standings_from_match($tournament_id, $club1_id, $club2_id, $club1_score, $club2_score);

        // Auto-update tournament status after saving match result
        $clubs = self::get_tournament_clubs($tournament_id);
        $updated_rounds = self::get_tournament_rounds($tournament_id);
        $this->auto_update_tournament_status($tournament_id, $clubs, $updated_rounds);

        return new WP_REST_Response(['success' => true], 200);
    }

    public function rest_add_team_to_tournament($request) {
        $tournament_id = $request['id'];
        $params = $request->get_params();
        $club_id = isset($params['club_id']) ? intval($params['club_id']) : 0;

        if (!$club_id) {
            return new WP_Error('invalid_club', 'Invalid club ID', ['status' => 400]);
        }

        $existing_clubs = self::get_tournament_clubs($tournament_id);
        if (in_array($club_id, $existing_clubs)) {
            return new WP_Error('club_exists', 'Club already in tournament', ['status' => 400]);
        }

        $max_teams = self::get_tournament_max_teams($tournament_id);
        // Only check max teams if it's set and greater than 0
        if ($max_teams > 0 && count($existing_clubs) >= $max_teams) {
            return new WP_Error('tournament_full', 'Tournament is full', ['status' => 400]);
        }

        $existing_clubs[] = $club_id;
        self::set_tournament_clubs($tournament_id, $existing_clubs);

        // Update standings if they exist
        $standings = self::get_tournament_standings($tournament_id);
        if (!empty($standings)) {
            $team = get_post($club_id);
            if ($team) {
                $standings[] = [
                    'club_id' => $club_id,
                    'club_name' => $team->post_title,
                    'points' => 0,
                    'games_played' => 0,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                    'opponents' => []
                ];
                self::set_tournament_standings($tournament_id, $standings);
            }
        }

        return new WP_REST_Response(['success' => true], 200);
    }

    public function rest_remove_team_from_tournament($request) {
        $tournament_id = $request['id'];
        $params = $request->get_params();
        $club_id = isset($params['club_id']) ? intval($params['club_id']) : 0;

        if (!$club_id) {
            return new WP_Error('invalid_club', 'Invalid club ID', ['status' => 400]);
        }

        $existing_clubs = self::get_tournament_clubs($tournament_id);
        $key = array_search($club_id, $existing_clubs);
        if ($key === false) {
            return new WP_Error('club_not_found', 'Club not in tournament', ['status' => 404]);
        }

        unset($existing_clubs[$key]);
        self::set_tournament_clubs($tournament_id, array_values($existing_clubs));

        // Update standings if they exist
        $standings = self::get_tournament_standings($tournament_id);
        if (!empty($standings)) {
            $standings = array_filter($standings, function($standing) use ($club_id) {
                return $standing['club_id'] != $club_id;
            });
            self::set_tournament_standings($tournament_id, array_values($standings));
        }

        return new WP_REST_Response(['success' => true], 200);
    }

    public function rest_add_sample_clubs($request) {
        $tournament_id = $request['id'];
        $params = $request->get_params();
        $count = isset($params['count']) ? intval($params['count']) : 16;
        
        // Get current tournament info
        $existing_clubs = self::get_tournament_clubs($tournament_id);
        $max_teams = self::get_tournament_max_teams($tournament_id);
        $current_count = count($existing_clubs);
        
        // Check if tournament is full
        if ($max_teams > 0 && $current_count >= $max_teams) {
            return new WP_Error('tournament_full', 'Tournament is full. Cannot add more teams.', ['status' => 400]);
        }
        
        // Calculate how many teams can be added
        $available_slots = $max_teams > 0 ? $max_teams - $current_count : 50;
        $count = min($count, $available_slots);
        
        if ($count <= 0) {
            return new WP_Error('no_slots_available', 'No slots available for new teams.', ['status' => 400]);
        }
        
        // First, try to get existing clubs that are not already in the tournament
        $existing_available_clubs = get_posts([
            'post_type' => 'obzg_club',
            'post_status' => 'publish',
            'numberposts' => $count,
            'post__not_in' => $existing_clubs,
            'orderby' => 'rand'
        ]);
        
        $clubs_to_add = [];
        $existing_clubs_used = 0;
        
        // Use existing clubs first
        foreach ($existing_available_clubs as $club) {
            if (count($clubs_to_add) < $count) {
                $clubs_to_add[] = $club->ID;
                $existing_clubs_used++;
            }
        }
        
        // If we need more clubs, create new sample clubs
        $remaining_needed = $count - $existing_clubs_used;
        if ($remaining_needed > 0) {
            $timestamp = time();
            for ($i = 1; $i <= $remaining_needed; $i++) {
                $club_name = 'Sample Club ' . $i . ' (' . $timestamp . ')';
                
                $club_id = wp_insert_post([
                    'post_title' => $club_name,
                    'post_type' => 'obzg_club',
                    'post_status' => 'publish'
                ]);

                if (!is_wp_error($club_id)) {
                    update_post_meta($club_id, '_obzg_club_email', $club_name . '@example.com');
                    update_post_meta($club_id, '_obzg_club_phone', '+385 1 ' . rand(100, 999) . ' ' . rand(1000, 9999));
                    update_post_meta($club_id, '_obzg_club_address', 'Address ' . rand(1, 100));
                    update_post_meta($club_id, '_obzg_club_city', 'Zagreb');
                    update_post_meta($club_id, '_obzg_club_city_number', rand(10000, 99999));
                    update_post_meta($club_id, '_obzg_club_president', 'President ' . $club_name);
                    update_post_meta($club_id, '_obzg_club_league', 'First League');
                    $clubs_to_add[] = $club_id;
                }
            }
        }

        // Add clubs to tournament
        foreach ($clubs_to_add as $club_id) {
            if (!in_array($club_id, $existing_clubs)) {
                $existing_clubs[] = $club_id;
            }
        }
        self::set_tournament_clubs($tournament_id, $existing_clubs);

        return new WP_REST_Response([
            'success' => true, 
            'clubs_added' => count($clubs_to_add),
            'existing_clubs_used' => $existing_clubs_used,
            'new_clubs_created' => count($clubs_to_add) - $existing_clubs_used
        ], 200);
    }

    public function rest_import_teams_excel($request) {
        $tournament_id = $request['id'];
        
        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('no_file', 'No file uploaded or upload error', ['status' => 400]);
        }
        
        $file = $_FILES['file'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Check file type
        if (!in_array($file_extension, ['xlsx', 'xls'])) {
            return new WP_Error('invalid_file', 'Only Excel files (.xlsx, .xls) are allowed', ['status' => 400]);
        }
        
        // Check file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return new WP_Error('file_too_large', 'File size must be less than 5MB', ['status' => 400]);
        }
        
        // For now, we'll create a simple implementation
        // In a real implementation, you'd use a library like PhpSpreadsheet
        $teams_created = 0;
        $errors = [];
        
        // Read the file content (simplified - in reality you'd parse Excel)
        $file_content = file_get_contents($file['tmp_name']);
        
        // For demonstration, we'll create some sample teams
        // In a real implementation, you'd parse the Excel content
        $sample_teams = [
            ['Team A', 'teamA@example.com', '+1234567890'],
            ['Team B', 'teamB@example.com', '+1234567891'],
            ['Team C', 'teamC@example.com', '+1234567892'],
        ];
        
        $clubs = self::get_tournament_clubs($tournament_id);
        $max_teams = self::get_tournament_max_teams($tournament_id);
        
        foreach ($sample_teams as $team_data) {
            // Check if tournament is full
            if ($max_teams > 0 && count($clubs) >= $max_teams) {
                break;
            }
            
            // Create the club
            $club_data = [
                'post_title' => $team_data[0],
                'post_type' => 'obzg_club',
                'post_status' => 'publish'
            ];
            
            $club_id = wp_insert_post($club_data);
            if ($club_id && !is_wp_error($club_id)) {
                // Add meta data (only if provided)
                if (!empty($team_data[1])) {
                    update_post_meta($club_id, '_obzg_club_email', $team_data[1]);
                }
                if (!empty($team_data[2])) {
                    update_post_meta($club_id, '_obzg_club_phone', $team_data[2]);
                }
                
                // Add tournament info
                $tournament_title = get_the_title($tournament_id);
                update_post_meta($club_id, '_obzg_club_league', 'Tournament: ' . $tournament_title);
                
                $clubs[] = $club_id;
                $teams_created++;
            } else {
                $errors[] = "Failed to create team: " . $team_data[0];
            }
        }
        
        // Save the updated clubs list
        self::set_tournament_clubs($tournament_id, $clubs);
        
        return new WP_REST_Response([
            'success' => true,
            'teams_created' => $teams_created,
            'errors' => $errors
        ], 200);
    }

    // Add CORS headers for React frontend
    public function add_cors_headers() {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }
            exit(0);
        }
    }

    /**
     * Check super admin (manage_options) with valid auth token
     */
    public function rest_check_super_admin($request) {
        if ($this->rest_check_auth($request) !== true) {
            return false;
        }
        $u = wp_get_current_user();
        // Allow either WP admin capability or the designated super admin email
        if (current_user_can('manage_options')) {
            return true;
        }
        return strtolower($u->user_email) === 'leja.vehovec28@gmail.com';
    }

    /**
     * Compute two groups for 18-team tournaments.
     * - If exactly 18 clubs, split into two groups of 9.
     * - Group names derived from location if it contains a '+', otherwise Group A/B.
     */
    private function compute_tournament_groups($tournament_id, $club_ids, $location_value) {
        $groups = [];
        if (!is_array($club_ids)) {
            return $groups;
        }

        $group_count = intval(get_post_meta($tournament_id, '_obzg_tournament_group_count', true));
        if ($group_count < 2) {
            return $groups; // no groups configured
        }

        // Build names from location split, then fallback to Group A/B/C...
        $names = [];
        if (!empty($location_value)) {
            $parts = array_map('trim', explode('+', $location_value));
            foreach ($parts as $p) { if ($p !== '') { $names[] = $p; } }
        }
        $alphabet = range('A', 'Z');
        for ($i = count($names); $i < $group_count; $i++) {
            $names[] = 'Group ' . $alphabet[$i % 26];
        }

        // Balanced deterministic split (no shuffle here)
        $total = count($club_ids);
        $base = $group_count > 0 ? intdiv($total, $group_count) : $total;
        $rem = $group_count > 0 ? $total % $group_count : 0;
        $offset = 0;
        for ($i = 0; $i < $group_count; $i++) {
            $size = $base + ($i < $rem ? 1 : 0);
            $groups[] = [
                'name' => $names[$i] ?? ('Group ' . $alphabet[$i % 26]),
                'club_ids' => array_slice($club_ids, $offset, $size)
            ];
            $offset += $size;
        }

        return $groups;
    }

    /**
     * Persist groups to post meta
     */
    private function set_tournament_groups($tournament_id, $groups) {
        update_post_meta($tournament_id, '_obzg_tournament_groups', $groups);
    }

    private function get_tournament_groups($tournament_id) {
        $groups = get_post_meta($tournament_id, '_obzg_tournament_groups', true);
        return is_array($groups) ? $groups : [];
    }

    public function rest_update_tournament_groups($request) {
        $tournament_id = intval($request['id']);
        $params = $request->get_json_params();
        if (!$tournament_id || !is_array($params)) {
            return new WP_Error('invalid_params', 'Invalid parameters', ['status' => 400]);
        }
        // Expect { groups: [ { name, club_ids: [] }, { name, club_ids: [] } ] }
        $groups = isset($params['groups']) && is_array($params['groups']) ? $params['groups'] : [];
        $this->set_tournament_groups($tournament_id, $groups);
        return new WP_REST_Response(['success' => true, 'groups' => $groups], 200);
    }

    public function rest_randomize_tournament_groups($request) {
        $tournament_id = intval($request['id']);
        $tournament = get_post($tournament_id);
        if (!$tournament || $tournament->post_type !== 'obzg_tournament') {
            return new WP_Error('not_found', 'Tournament not found', ['status' => 404]);
        }
        $clubs = self::get_tournament_clubs($tournament_id);
        if (!is_array($clubs) || count($clubs) < 2) {
            return new WP_Error('invalid_state', 'Not enough teams to create groups', ['status' => 400]);
        }
        // Shuffle
        shuffle($clubs);
        $location_value = get_post_meta($tournament_id, '_obzg_tournament_location', true);
        $group_count = intval(get_post_meta($tournament_id, '_obzg_tournament_group_count', true));
        if ($group_count < 2) { $group_count = 2; }

        // Build group names from location (split by '+'), then fallback to Group A/B/C/...
        $names = [];
        if (!empty($location_value)) {
            $parts = array_map('trim', explode('+', $location_value));
            foreach ($parts as $p) { if ($p !== '') { $names[] = $p; } }
        }
        $alphabet = range('A', 'Z');
        for ($i = count($names); $i < $group_count; $i++) {
            $names[] = 'Group ' . $alphabet[$i % 26];
        }

        // Balanced split
        $total = count($clubs);
        $base = intdiv($total, $group_count);
        $rem = $total % $group_count;
        $offset = 0;
        $groups = [];
        for ($i = 0; $i < $group_count; $i++) {
            $size = $base + ($i < $rem ? 1 : 0);
            $groups[] = [
                'name' => $names[$i] ?? ('Group ' . $alphabet[$i % 26]),
                'club_ids' => array_slice($clubs, $offset, $size)
            ];
            $offset += $size;
        }
        $this->set_tournament_groups($tournament_id, $groups);
        return new WP_REST_Response(['success' => true, 'groups' => $groups], 200);
    }

    /**
     * Check if user is authenticated
     */
    public function rest_check_auth($request) {
        $auth_header = $request->get_header('Authorization');
        if (!$auth_header) {
            return false;
        }

        $token = str_replace('Bearer ', '', $auth_header);
        $user_id = $this->validate_token($token);
        
        if ($user_id) {
            wp_set_current_user($user_id);
            return true;
        }
        
        return false;
    }

    /**
     * Allow public read access (no authentication required)
     */
    public function rest_check_public_read($request) {
        return true;
    }

    /**
     * Validate JWT token
     */
    private function validate_token($token) {
        // Simple token validation - in production, use proper JWT library
        $tokens = get_option('obzg_auth_tokens', []);
        
        if (isset($tokens[$token])) {
            $token_data = $tokens[$token];
            if ($token_data['expires'] > time()) {
                return $token_data['user_id'];
            } else {
                // Remove expired token
                unset($tokens[$token]);
                update_option('obzg_auth_tokens', $tokens);
            }
        }
        
        return false;
    }

    /**
     * Generate JWT token
     */
    private function generate_token($user_id) {
        $tokens = get_option('obzg_auth_tokens', []);
        $token = wp_generate_password(32, false);
        
        $tokens[$token] = [
            'user_id' => $user_id,
            'expires' => time() + (7 * 24 * 60 * 60), // 7 days
            'created' => time()
        ];
        
        update_option('obzg_auth_tokens', $tokens);
        return $token;
    }

    /**
     * User registration
     */
    public function rest_auth_register($request) {
        $params = $request->get_params();
        
        $name = sanitize_text_field($params['name'] ?? '');
        $email = sanitize_email($params['email'] ?? '');
        $password = $params['password'] ?? '';
        
        if (empty($name) || empty($email) || empty($password)) {
            return new WP_Error('missing_fields', 'All fields are required', ['status' => 400]);
        }
        
        if (strlen($password) < 6) {
            return new WP_Error('password_too_short', 'Password must be at least 6 characters', ['status' => 400]);
        }
        
        if (email_exists($email)) {
            return new WP_Error('email_exists', 'Email already exists', ['status' => 400]);
        }
        
        $user_id = wp_create_user($email, $password, $email);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $name,
            'first_name' => $name
        ]);
        
        $token = $this->generate_token($user_id);
        
        return [
            'user' => [
                'id' => $user_id,
                'name' => $name,
                'email' => $email,
                'last_login' => current_time('mysql')
            ],
            'token' => $token
        ];
    }

    /**
     * User login
     */
    public function rest_auth_login($request) {
        $params = $request->get_params();
        
        $email = sanitize_email($params['email'] ?? '');
        $password = $params['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            return new WP_Error('missing_fields', 'Email and password are required', ['status' => 400]);
        }
        
        // Find user by email
        $user = get_user_by('email', $email);
        
        if (!$user) {
            return new WP_Error('invalid_credentials', 'Invalid email or password', ['status' => 401]);
        }
        
        // Authenticate with username and password
        $authenticated_user = wp_authenticate($user->user_login, $password);
        
        if (is_wp_error($authenticated_user)) {
            return new WP_Error('invalid_credentials', 'Invalid email or password', ['status' => 401]);
        }
        
        // Update last login time
        update_user_meta($user->ID, 'last_login', current_time('mysql'));
        
        $token = $this->generate_token($user->ID);
        
        return [
            'user' => [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'last_login' => get_user_meta($user->ID, 'last_login', true)
            ],
            'token' => $token
        ];
    }

    /**
     * User logout
     */
    public function rest_auth_logout($request) {
        $auth_header = $request->get_header('Authorization');
        if ($auth_header) {
            $token = str_replace('Bearer ', '', $auth_header);
            $tokens = get_option('obzg_auth_tokens', []);
            unset($tokens[$token]);
            update_option('obzg_auth_tokens', $tokens);
        }
        
        return ['message' => 'Logged out successfully'];
    }

    /**
     * Get current user info
     */
    public function rest_auth_me($request) {
        $user = wp_get_current_user();
        
        return [
            'user' => [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'last_login' => get_user_meta($user->ID, 'last_login', true),
                'roles' => $user->roles
            ]
        ];
    }

    public function rest_get_users($request) {
        $wp_users = get_users();
        $out = [];
        foreach ($wp_users as $u) {
            $out[] = [
                'id' => $u->ID,
                'name' => $u->display_name,
                'email' => $u->user_email,
                'roles' => $u->roles,
            ];
        }
        return new WP_REST_Response($out, 200);
    }

    public function rest_update_user($request) {
        $id = intval($request['id']);
        $params = $request->get_json_params();
        $u = get_user_by('id', $id);
        if (!$u) { return new WP_Error('not_found', 'User not found', ['status'=>404]); }
        if (!empty($params['name'])) {
            wp_update_user(['ID'=>$id, 'display_name'=>sanitize_text_field($params['name'])]);
        }
        if (!empty($params['password'])) {
            wp_set_password($params['password'], $id);
        }
        if (isset($params['role'])) {
            $role = $params['role'] === 'administrator' ? 'administrator' : 'subscriber';
            $u->set_role($role);
        }
        return new WP_REST_Response(['success'=>true], 200);
    }

    public function rest_delete_user($request) {
        $id = intval($request['id']);
        require_once ABSPATH . 'wp-admin/includes/user.php';
        $res = wp_delete_user($id);
        if (!$res) { return new WP_Error('delete_failed','Failed to delete user',['status'=>500]); }
        return new WP_REST_Response(['success'=>true], 200);
    }

    /**
     * Delete test users (temporary function)
     */
    public function rest_delete_test_users($request) {
        global $wpdb;
        
        // Get all users with example.com emails
        $users = get_users([
            'meta_query' => [],
            'search' => '*@example.com',
            'search_columns' => ['user_email']
        ]);
        
        $deleted_users = [];
        
        foreach ($users as $user) {
            if (strpos($user->user_email, '@example.com') !== false) {
                $deleted_users[] = [
                    'id' => $user->ID,
                    'email' => $user->user_email,
                    'name' => $user->display_name
                ];
                
                // Delete user using direct database query
                $wpdb->delete($wpdb->users, ['ID' => $user->ID]);
                $wpdb->delete($wpdb->usermeta, ['user_id' => $user->ID]);
            }
        }
        
        // Also clear any tokens for these users
        $tokens = get_option('obzg_auth_tokens', []);
        $cleaned_tokens = [];
        
        foreach ($tokens as $token => $token_data) {
            $should_keep = true;
            foreach ($deleted_users as $deleted_user) {
                if ($token_data['user_id'] == $deleted_user['id']) {
                    $should_keep = false;
                    break;
                }
            }
            if ($should_keep) {
                $cleaned_tokens[$token] = $token_data;
            }
        }
        
        update_option('obzg_auth_tokens', $cleaned_tokens);
        
        return [
            'message' => 'Test users deleted successfully',
            'deleted_users' => $deleted_users,
            'count' => count($deleted_users)
        ];
    }

    /**
     * Delete specific user by email (temporary function)
     */
    public function rest_delete_specific_user($request) {
        global $wpdb;
        
        $params = $request->get_params();
        $email = sanitize_email($params['email'] ?? '');
        
        if (empty($email)) {
            return new WP_Error('missing_email', 'Email parameter is required', ['status' => 400]);
        }
        
        // Find user by email
        $user = get_user_by('email', $email);
        
        // If not found by email, try by username (email as username)
        if (!$user) {
            $user = get_user_by('login', $email);
        }
        
        if (!$user) {
            return new WP_Error('user_not_found', 'User not found', ['status' => 404]);
        }
        
        $deleted_user = [
            'id' => $user->ID,
            'email' => $user->user_email,
            'name' => $user->display_name,
            'username' => $user->user_login
        ];
        
        // Delete user using direct database query
        $wpdb->delete($wpdb->users, ['ID' => $user->ID]);
        $wpdb->delete($wpdb->usermeta, ['user_id' => $user->ID]);
        
        // Also clear any tokens for this user
        $tokens = get_option('obzg_auth_tokens', []);
        $cleaned_tokens = [];
        
        foreach ($tokens as $token => $token_data) {
            if ($token_data['user_id'] != $user->ID) {
                $cleaned_tokens[$token] = $token_data;
            }
        }
        
        update_option('obzg_auth_tokens', $cleaned_tokens);
        
        return [
            'message' => 'User deleted successfully',
            'deleted_user' => $deleted_user
        ];
    }
}

new OBZG_Plugin(); 