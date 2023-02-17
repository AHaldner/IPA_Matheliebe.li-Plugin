<?php

/*
* Plugin Name: Custom Register Plugin
* Plugin URI: https://github.com/AHaldner/IPA_Matheliebe.li
* Description: Custom Register for Matheliebe.li
* Version: 1.1
* Requires at least: 6.1
* Requires PHP: 7.2
* Author: Andrin Haldner
* Author URI: https://github.com/AHaldner
* License: GPL v2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

// Set up Registration
function custom_registration_function()
{
    if (isset($_POST['submit'])) {
        registration_validation(
            $_POST['username'],
            $_POST['password'],
            $_POST['email'],
            $_POST['website'],
            $_POST['fname'],
            $_POST['lname']
        );

        // Sanitize the user import from Injections
        // Adds a Layer of Security
        global $username, $password, $email, $website, $first_name, $last_name;
        $username = sanitize_user($_POST['username']);
        $password = esc_attr($_POST['password']);
        $email = sanitize_email($_POST['email']);
        $website = esc_url($_POST['website']);
        $first_name = sanitize_text_field($_POST['fname']);
        $last_name = sanitize_text_field($_POST['lname']);

        // Complete Registration when no Error is found in the Input
        // Create new User in the Database
        complete_registration(
            $username,
            $password,
            $email,
            $website,
            $first_name,
            $last_name
        );
    }

    // Variables for the Registration-Form
    registration_form(
        $username,
        $password,
        $email,
        $website,
        $first_name,
        $last_name
    );
}

// Generate Forms
function registration_form($username, $password, $email, $website, $first_name, $last_name)
{
    if (get_locale() == 'de_CH') {
        // Generate Form in German
        echo '
            <form class="util-form register" id="register-form" action="' . $_SERVER['REQUEST_URI'] . '" method="post">
            <div class="form">
            <div class="form-field">
            <label for="username">Nutzername <strong>*</strong></label>
            <input type="text" name="username" value="' . (isset($_POST['username']) ? $username : null) . '">
            </div>
            
            <div class="form-field">
            <label for="password">Passwort <strong>*</strong></label>
            <input type="password" name="password" value="' . (isset($_POST['password']) ? $password : null) . '">
            </div>
            
            <div class="form-field">
            <label for="email">E-Mail <strong>*</strong></label>
            <input type="text" name="email" value="' . (isset($_POST['email']) ? $email : null) . '">
            </div>
            
            <div class="form-field">
            <label for="website">Website</label>
            <input type="text" name="website" value="' . (isset($_POST['website']) ? $website : null) . '">
            </div>
            
            <div class="form-field">
            <label for="firstname">Vorname</label>
            <input type="text" name="fname" value="' . (isset($_POST['fname']) ? $first_name : null) . '">
            </div>
            
            <div class="form-field">
            <label for="website">Nachname</label>
            <input type="text" name="lname" value="' . (isset($_POST['lname']) ? $last_name : null) . '">
            </div>

            <div class="form-button"> 
            <input type="submit" name="submit" value="Registrieren"/>
            </div>
            </div>
            </form>
        ';
    } else {
        // Generate Form in English
        echo '
            <form class="util-form register" id="register-form" action="' . $_SERVER['REQUEST_URI'] . '" method="post">
            <div class="form">
            <div class="form-field">
            <label for="username">Username <strong>*</strong></label>
            <input type="text" name="username" value="' . (isset($_POST['username']) ? $username : null) . '">
            </div>
            
            <div class="form-field">
            <label for="password">Password <strong>*</strong></label>
            <input type="password" name="password" value="' . (isset($_POST['password']) ? $password : null) . '">
            </div>
            
            <div class="form-field">
            <label for="email">Email <strong>*</strong></label>
            <input type="text" name="email" value="' . (isset($_POST['email']) ? $email : null) . '">
            </div>
            
            <div class="form-field">
            <label for="website">Website</label>
            <input type="text" name="website" value="' . (isset($_POST['website']) ? $website : null) . '">
            </div>
            
            <div class="form-field">
            <label for="firstname">First Name</label>
            <input type="text" name="fname" value="' . (isset($_POST['fname']) ? $first_name : null) . '">
            </div>
            
            <div class="form-field">
            <label for="website">Last Name</label>
            <input type="text" name="lname" value="' . (isset($_POST['lname']) ? $last_name : null) . '">
            </div>

            <div class="form-button"> 
            <input type="submit" name="submit" value="Register"/>
            </div>
            </div>
            </form>
        ';
    }
}

// Validate User Input
function registration_validation($username, $password, $email, $website, $first_name, $last_name)
{
    // Generate Errors Logic
    global $reg_errors;
    $reg_errors = new WP_Error;

    if (empty($username) || empty($password) || empty($email)) {
        if (get_locale() == 'de_CH') {
            $reg_errors->add('field', 'Erforderliches Formularfeld fehlt');
        } else {
            $reg_errors->add('field', 'Required form field is missing');
        }
    }

    if (strlen($username) < 4) {
        if (get_locale() == 'de_CH') {
            $reg_errors->add('username_length', 'Benutzername zu kurz. Mindestens 4 Zeichen sind erforderlich');
        } else {
            $reg_errors->add('username_length', 'Username too short. At least 4 characters is required');
        }
    }

    if (username_exists($username)) {
        if (get_locale() == 'de_CH') {
            $reg_errors->add('user_name', 'Dieser Benutzername existiert bereits');
        } else {
            $reg_errors->add('user_name', 'That username already exists');
        }
    }

    if (!validate_username($username)) {
        if (get_locale() == 'de_CH') {
            $reg_errors->add('username_invalid', 'Der von Ihnen eingegebene Benutzername ist ungültig');
        } else {
            $reg_errors->add('username_invalid', 'The username you entered is not valid');
        }
    }

    if (strlen($password) < 8) {
        if (get_locale() == 'de_CH') {
            $reg_errors->add('password', 'Passwortlänge muss länger als 8 Zeichen sein');
        } else {
            $reg_errors->add('password', 'Password length must be greater than 8');
        }
    }

    if (!is_email($email)) {
        if (get_locale() == 'de_CH') {
            $reg_errors->add('email_invalid', 'E-Mail ist nicht gültig');
        } else {
            $reg_errors->add('email_invalid', 'Email is not valid');
        }
    }

    if (email_exists($email)) {
        if (get_locale() == 'de_CH') {
            $reg_errors->add('email', 'E-Mail ist bereits in Gebrauch');
        } else {
            $reg_errors->add('email', 'Email already in use');
        }
    }

    if (!empty($website)) {
        if (!filter_var($website, FILTER_VALIDATE_URL)) {
            if (get_locale() == 'de_CH') {
                $reg_errors->add('website', 'Website ist keine gültige URL');
            } else {
                $reg_errors->add('website', 'Website is not a valid URL');
            }
        }
    }

    // Print out Errors
    if (is_wp_error($reg_errors)) {
        echo '<div class="error-box">';

        foreach ($reg_errors->get_error_messages() as $error) {
            echo '<div class="error-msg">';
            echo '<strong>ERROR</strong> : ';
            echo $error . '<br/>';

            echo '</div>';
        }

        echo '</div>';
    }
}

// Complete Registration if there are no Errors
function complete_registration()
{
    global $reg_errors, $username, $password, $email, $website, $first_name, $last_name;
    if (count($reg_errors->get_error_messages()) < 1) {
        $userdata = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'user_url' => $website,
            'first_name' => $first_name,
            'last_name' => $last_name,
        );
        $user = wp_insert_user($userdata);
        // Guide user to Login after Registration
        if (get_locale() == 'de_CH') {
            echo '<p>Registrierung abgeschlossen. Gehen Sie zur <a class="underline-link" href="' . get_site_url() . '/anmelden">Anmeldung</a>.</p>';
            echo '<div class="spacing-small"></div>';
        } else {
            echo '<p>Registration complete. Go to the <a class="underline-link" href="' . get_site_url() . '/login">Login Page</a>.</p>';
            echo '<div class="spacing-small"></div>';
        }
    }
}

// Register the Shortcode : [matheliebe_custom_registration]
add_shortcode('matheliebe_custom_registration', 'custom_registration_shortcode');

// Callback Function
function custom_registration_shortcode()
{
    ob_start();
    custom_registration_function();
    return ob_get_clean();
}
