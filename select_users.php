<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * google_hangout block caps.
 *
 * @package    block_google_hangout
 * @copyright  @copyright Nadav Kavalerchik <nadavkav@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once('../../config.php');
global $PAGE, $COURSE, $OUTPUT, $DB, $CFG;
require_once($CFG->libdir . '/formslib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);

require_login();

$context = context_course::instance($courseid);
$PAGE->set_context($context);

//$users = get_enrolled_users($context);
//$usersemails = '';
//foreach ($users as $user) {
//    $usersemails .= "{ id : '$user->email', invite_type : 'EMAIL' },";
//}
//echo $usersemails;

class selectusers_to_hanghout_with_form extends moodleform {

    function definition() {
        $courseid = optional_param('courseid', 0, PARAM_INT);
        $mform    =& $this->_form;
        $context = context_course::instance($courseid);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $users = get_enrolled_users($context);
        foreach ($users as $user) {
            $mform->addElement('advcheckbox', 'usersids[]', $user->firstname . ' ' . $user->lastname , ' email: ' . $user->email, array('group' => 1), array(0,$user->id));
            //$mform->setDefault('usersids', $user->id);
        }
         $buttons = array();
        $buttons[] =& $mform->createElement('submit', 'send', get_string('email_send','block_configurable_reports'));
        $buttons[] =& $mform->createElement('cancel');

        $mform->addGroup($buttons, 'buttons', get_string('actions'), array(' '), false);
    }
}

$form = new selectusers_to_hanghout_with_form();
$userslist = '';

if ($form->is_cancelled()) {
    redirect(new moodle_url('/course/view.php?id='.$data->courseid));
} else if ($data = $form->get_data()) {
    //print_r($data);
    $usersids = $_POST['usersids'];
    $usersemails = '';
    //$userslist = '';
    foreach($usersids as $userid){
        //echo "userid=".$userid."<br/>";
        if ($userid != 0){
            $hangout_user = $DB->get_record('user', array('id'=>$userid));
            $userslist .= '<br>'. $hangout_user->firstname . ' ' . $hangout_user->lastname . ' email: ' . $hangout_user->email.'<br>';
            $usersemails .= "{ id : '$hangout_user->email', invite_type : 'EMAIL' },";
        }
    }
    $hangout_button = "<script src=\"https://apis.google.com/js/platform.js\" async defer></script>
            <g:hangout render=\"createhangout\" invites=\"[$usersemails]\"></g:hangout>";
}

$PAGE->set_title(get_string('pluginname', 'block_google_hangout'));
$PAGE->set_heading(format_string($COURSE->fullname));
$PAGE->navbar->add(format_string($COURSE->fullname), new moodle_url('/course/view.php', array('id'=>$courseid)));
$PAGE->navbar->add(get_string('pluginname', 'block_google_hangout'));
$PAGE->set_url(new moodle_url('/course/view.php', array('id'=>$courseid)));

echo $OUTPUT->header() ; //  header();

echo html_writer::start_tag('div', array('class' => 'no-overflow'));
if (isset($hangout_button)) {
    echo html_writer::tag('h2', get_string('starthangoutwithfollowing', 'block_google_hangout'));
    echo $userslist."<br>";
    echo html_writer::tag('h4', get_string('starthangoutsession', 'block_google_hangout'));
    echo $hangout_button;
} else {
    $form->display();
}
echo html_writer::end_tag('div');

echo $OUTPUT->footer();