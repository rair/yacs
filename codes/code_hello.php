<?php
/**
 * This script is a sample declaration of a formatting code (code yacs)
 *
 * @author Alexis Raimbault
 * @reference
 * @license http://www.gnu.org/copyleft/lesser.txt GNU Lesser General Public License
 */

// stop hackers
if(count(get_included_files()) < 3) {
	echo 'Script must be included';
	return;
}

// formatting code declaration
global $codesyacs;
$codesyacs[] = array(
	'pattern'		=> '[hello]',
	'replace'		=> '*hello world* from Yacs',
//	'script'	=> 'overlays/issue.php',
//	'function'	=> 'Issue::setup',
	'label_en'	=> 'A very simple formatting code',
	'label_fr'	=> 'Un code de formatage très simple');

?>