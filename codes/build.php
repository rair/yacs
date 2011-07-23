<?php
/**
 * scan directory codes for formatting code declaration
 * build pattern/replace array
 * build sample files
 *
 * This page is used to build formatting code. Its usage is restricted to associates.
 *
 * [title]How to declare a formatting code?[/title]
 *
 * [title]Configuration information[/title]
 *
 * Configuration information is saved into [code]parameters/codes.include.php[/code].
 *
 * The file [code]parameters/codes.include.php.bak[/code] can be used to restore
 * the active configuration before the last change.
 *
 * This script does save information even in demonstration mode, because of
 * software updates. There is no known security issue with this way of proceeding anyway.
 *
 * @author Alexis Raimbault
 * @reference
 * @license http://www.gnu.org/copyleft/lesser.txt GNU Lesser General Public License
 */

// include explicitly some libraries
include_once '../shared/global.php';

// what to do
$action = '';
if(!file_exists('../parameters/codes.include.php'))
	$action = 'build';
if(!$action && isset($_REQUEST['action']))
	$action = $_REQUEST['action'];
if(!$action && isset($context['arguments'][0]))
	$action = $context['arguments'][0];
$action = strip_tags($action);

// load localized strings
i18n::bind('control');

// load the skin
load_skin('control');

// the path to this page
$context['path_bar'] = array( 'control/index.php' => i18n::s('Control Panel') );

// the title of the page
$context['page_title'] = i18n::s('Scan scripts for formatting codes extensions');

function include_codes() {
	global $context, $codeyacs;

	// ensure enough execution time
	Safe::set_time_limit(30);

	// open the directory
	$path = $context['path_to_root'].'codes'
	if(!$dir = Safe::opendir(path)) {
		$context['text'] .= sprintf(i18n::s('Impossible to read %s.'), $path).BR."\n";
		return;
	}

	// browse the directory
	while(($item = Safe::readdir($dir)) !== FALSE) {

		// skip some files
		if($item[0] == '.')
			continue;

		// load any 'code_*.php'
		$actual_item = str_replace('//', '/', $path.'/'.$item);
		if(preg_match('/^code_.*\.php$/i', $item)) {
			include_once $actual_item;
			$context['text'] .= sprintf(i18n::s('formatting code %s has been included'), $actual_item).BR."\n";
		}
	}

	// close the directory
	Safe::closedir($dir);
}

global $codeyacs, $action;

if(!Surfer::is_associate() && (file_exists('../parameters/switch.on') || file_exists('../parameters/switch.off'))) {

	// prevent access to this script
	Safe::header('Status: 401 Unauthorized', TRUE, 401);
	Logger::error(i18n::s('You are not allowed to perform this operation.'));

	// forward to the control panel
	$menu = array('control/' => i18n::s('Control Panel'));
	$context['text'] .= Skin::build_list($menu, 'menu_bar');

// review codes, but never on first install
} elseif(($action == 'check') && (file_exists('../parameters/switch.on') || file_exists('../parameters/switch.off'))) {

	// include all formatting codes
	include_codes();

	// no codes has been found
	if(!count($codesyacs)) {
		$context['text'] .= i18n::s('No item has been found.');

	// introduce each codes
	} else {

		$review = array();

		// consider each codes
		foreach($codesyacs as $code) {

			// bad script!
			if(!$code['id'] || !$code['pattern'] || !$code['replace']) {
				$context['text'] .= '<p><strong>'.i18n::s('Bad formatting code:').'</strong>'.BR."\n";
				foreach($code as $name => $value)
					$context['text'] .= $name.': '.$value.BR;
				$context['text'] .= "</p>\n";
				continue;
			}

			// script is defined but does not exist
			if($code['script'] && !file_exists($context['path_to_root'].$code['script'])) {
				$context['text'] .= '<p><strong>'.i18n::s('Script does not exist:').'</strong>'.BR."\n";
				foreach($code as $name => $value)
					$context['text'] .= $name.': '.$value.BR;
				$context['text'] .= "</p>\n";
				continue;
			}

			// set default values
			if(!isset($code['label_en']))
				$code['label_en'] = i18n::c('*** undefined label');
			if(!isset($code['sample_en']))
				$code['sample_en'] = '';

			// item id
			$id = $code['id'].'_'.md5($code['pattern']);

			// form item
			$input = '<input type="checkbox" name="'.$id.'" value="Y" checked="checked" />';

			// sample
			if($description) = i18n::l($code, 'sample'))
				$description .= BR;

			// user information
			$text = '<dt>'.$input.' <b>'.i18n::l($code, 'label').'</b></dt><dd>'.$description."\n";
			$text .= "</dd>\n\n";

			$review[] = $text;

		}

		$context['text'] .= '<p>'.i18n::s('Review formatting codes in the following list and uncheck unwanted extensions.')."</p>\n";
		$context['text'] .= '<form method="post" action="'.$context['script_url'].'"><div>'."\n";

		if(count($review)) {
			asort($review);
			$context['text'] .= Skin::build_block(i18n::s('formatting codes'), 'title').'<dl>'.implode('', array_values($review)).'</dl>';
		}

		// the submit button
		$context['text'] .= '<p>'
			.Skin::build_submit_button(i18n::s('Yes, I want to (re)build the set of formatting codes'))
			.'<input type="hidden" name="reviewed" value="yes" />'
			.'<input type="hidden" name="action" value="build" /></p>';

		$context['text'] .= '</div></form>';

	}

	// display the execution time
	$time = round(get_micro_time() - $context['start_time'], 2);
	$context['text'] .= '<p>'.sprintf(i18n::s('Script terminated in %.2f seconds.'), $time).'</p>';

	// back to the control panel
	$menu = array('control/' => i18n::s('Control Panel'));
	$context['text'] .= Skin::build_list($menu, 'menu_bar');

// rebuild codes or first installation
} elseif($action == 'build' || $action == 'check') {

	// feed-back to the user
	$context['text'] .= '<p>'.i18n::s('Following formatting codes have been detected and integrated into the file parameters/codes.include.php')."</p>\n";

	// first installation
	if(!file_exists('../parameters/switch.on') && !file_exists('../parameters/switch.off'))
		$context['text'] .= '<p>'.i18n::s('Review provided information and go to the bottom of the page to move forward.')."</p>\n";

	// include all formatting codes
	include_codes();

	// no codes has been found
	if(!count($codesyacs))
		$context['text'] .= i18n::s('No item has been found.');

	// compile all codes
	else {

		// backup the old version
		Safe::unlink('../parameters/codes.include.php.bak');
		Safe::rename('../parameters/codes.include.php', '../parameters/codes.include.php.bak');

		// consider each codes
		foreach($codesyacs as $code) {

			// bad script!
			if(!$code['id'] || !$code['pattern'] || !$code['replace']) {
				$context['text'] .= '<p><strong>'.i18n::s('Bad formatting code:').'</strong>'.BR."\n";
				foreach($code as $name => $value)
					$context['text'] .= $name.': '.$value.BR;
				$context['text'] .= "</p>\n";
				continue;
			}

			// script is defined but does not exist
			if($code['script'] && !file_exists($context['path_to_root'].$code['script'])) {
				$context['text'] .= '<p><strong>'.i18n::s('Script does not exist:').'</strong>'.BR."\n";
				foreach($code as $name => $value)
					$context['text'] .= $name.': '.$value.BR;
				$context['text'] .= "</p>\n";
				continue;
			}

			// set default values
			if(!isset($code['label_en']))
				$code['label_en'] = i18n::c('*** undefined label');
			if(!isset($code['sample_en']))
				$cpde['sample_en'] = '';

			// item id
			$id = $code['id'].'_'.md5($code['pattern']);

			// ensure this item has been selected
			if(isset($_REQUEST['reviewed']) && ($_REQUEST['reviewed'] == 'yes')) {
				if(!isset($_REQUEST[$id]) || ($_REQUEST[$id] != 'Y')) {
					$context['text'] .= sprintf(i18n::s('Disabling extension %s'), $id).BR."\n";
					continue;
				}
			}

			// user information
			$label = i18n::l($code, 'label');
			$context['text'] .= sprintf(i18n::s('formating codes %s labelised %s'), $code['id'], $label).BR."\n";

			//compilation
			// TODO
			// 		// label
			//		pattern[] =
			//		replace[] =
		}

		// the header section
		$content = '<?php'."\n"
			.'// This file has been created by the script codes/build.php'."\n"
			.'// on '.gmdate("F j, Y, g:i a").' GMT, for '.Surfer::get_name().'. Please do not modify it manually.'."\n"
			."\n";
			// TODO : declare pattern and replace array

		// the tail section
		$content .= '}'."\n"
			.'?>'."\n";

		// compile all codes into a single file
		if(!Safe::file_put_contents('parameters/codes.include.php', $content))
			$context['text'] .= sprintf(i18n::s('Impossible to write to %s.'), 'parameters/codes.include.php').BR."\n";
		else {
			$context['text'] .= i18n::s('formatting codes have been compiled in parameters/codes.include.php').BR."\n";

			// remember the change
			$label = sprintf(i18n::c('%s has been updated'), 'parameters/codes.include.php');
			Logger::remember('ccodes/build.php', $label);
		}

	}

	// display the execution time
	$time = round(get_micro_time() - $context['start_time'], 2);
	$context['text'] .= '<p>'.sprintf(i18n::s('Script terminated in %.2f seconds.'), $time).'</p>';

// display current codes
} else {

	// the splash message
	$context['text'] .= i18n::s('This script will scan your php scripts to build formatting codes list.');

	// the submit button
	$context['text'] .= '<form method="post" action="'.$context['script_url'].'" id="main_form"><p>'
		.Skin::build_submit_button(i18n::s('Scan scripts for formatting code extensions'), NULL, NULL, 'confirmed')
		.'<input type="hidden" name="action" value="check" />'
		.'</p></form>';

	// the script used for form handling at the browser
	$context['text'] .= JS_PREFIX
		.'// set the focus on first form field'."\n"
		.'$("confirmed").focus();'."\n"
		.JS_SUFFIX;

	// this may take several minutes
	$context['text'] .= '<p>'.i18n::s('When you will click on the button the server will be immediately requested to proceed. However, because of the so many things to do on the back-end, you may have to wait for minutes before getting a response displayed. Thank you for your patience.').'</p>';

	// display the existing hooks configuration file, if any
	$content = Safe::file_get_contents('../parameters/codes.include.php');
	if(strlen($content)) {
		$context['text'] .= Skin::build_box(sprintf(i18n::s('Current content of %s'), 'parameters/codes.include.php'), Safe::highlight_string($content), 'folded');

	}

}

 // render the skin
render_skin();

?>