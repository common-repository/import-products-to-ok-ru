<?php if (!defined('ABSPATH')) {exit;}
require_once IP2OK_PLUGIN_DIR_PATH.'includes/old-php-add-functions.php';
require_once IP2OK_PLUGIN_DIR_PATH.'includes/icopydoc-useful-functions.php';
require_once IP2OK_PLUGIN_DIR_PATH.'includes/wc-add-functions.php';
require_once IP2OK_PLUGIN_DIR_PATH.'includes/backward-compatibility.php';
require_once IP2OK_PLUGIN_DIR_PATH.'functions.php';
require_once IP2OK_PLUGIN_DIR_PATH.'extensions.php';

require_once IP2OK_PLUGIN_DIR_PATH.'classes/system/class-ip2ok.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/system/class-ip2ok-data-arr.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/system/class-ip2ok-debug-page.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/system/class-ip2ok-error-log.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/system/class-ip2ok-feedback.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/system/class-ip2ok-plugin-form-activate.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/system/class-ip2ok-plugin-upd.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/system/class-ip2ok-settings-page.php';

require_once IP2OK_PLUGIN_DIR_PATH.'classes/generation/traits/common/trait-ip2ok-t-common-get-catid.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/generation/traits/common/trait-ip2ok-t-common-skips.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/generation/traits/global/traits-ip2ok-global-variables.php';

require_once IP2OK_PLUGIN_DIR_PATH.'classes/generation/class-ip2ok-ok-ru-api.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/generation/class-ip2ok-ok-ru-api-helper.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/generation/class-ip2ok-ok-ru-api-helper-simple.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/generation/class-ip2ok-ok-ru-api-helper-variable.php';
require_once IP2OK_PLUGIN_DIR_PATH.'classes/generation/class-ip2ok-generation-xml.php';