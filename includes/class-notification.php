<?php 

class Notification {
    /**
     * Displays an error notification message.
     *
     * This static method is used to display an error notification message in the WordPress admin area.
     * It takes a string parameter representing the error message and echoes a formatted HTML div element
     * containing the error message.
     *
     * @param string $message The error message to be displayed.
     * @return void
     */
    static function error(string $message) {
        echo '<div class="notice notice-error is-dismissible"><p>' . $message . '</p></div>';
    }
    /**
     * Displays a success notification message.
     *
     * @param string $message The message to be displayed.
     * @return void
     */
    static function success(string $message) {
        echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
    }
    /**
     * Displays a warning message in the WordPress admin area.
     *
     * @param string $message The warning message to be displayed.
     * @return void
     */
    static function warning(string $message) {
        echo '<div class="notice notice-warning is-dismissible"><p>' . $message . '</p></div>';
    }
    /**
     * Displays an information notification message.
     *
     * This method is used to display an information notification message in the WordPress admin area.
     *
     * @param string $message The message to be displayed.
     * @return void
     */
    static function info(string $message) {
        echo '<div class="notice notice-info is-dismissible"><p>' . $message . '</p></div>';
    }
}