<?php

define('AJAX_SCRIPT', true);
require_once('../../../config.php');
require_once($CFG->dirroot . '/blocks/formal_langs/block_formal_langs.php');
require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->libdir  . '/filelib.php');
require_once($CFG->libdir  . '/formslib.php');
require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/correctwriting/edit_correctwriting_form.php');

$PAGE->set_context(context_system::instance());
require_login();

$langid  =  required_param('lang', PARAM_INT);
$text = required_param('scannedtext', PARAM_RAW);
$shouldperformparse = optional_param('parse', 0, PARAM_INT);

$language = block_formal_langs::lang_object($langid);

if ($language == null) {
    echo '{"tokens": [], "errors": ""}';
} else {
    $string = $language->create_from_string($text);
    $stream = $string->stream;
    $tokens = $stream->tokens;
    if(count($tokens)) {
        $tokenvalues = array();
        $form = 'qtype_correctwriting_edit_form';
        $errormessages = $form::convert_tokenstream_errors_to_formatted_messages($text, $stream);
        if (!$shouldperformparse || $language->could_parse() == false) {
            foreach($tokens as $token) {
                $tokenvalues[] = (string)($token->value());
            }
        } else {
            $tree = $string->syntaxtree;
            if (count($tree) > 1) {
                $errormessages[] = get_string('parseerror', 'qtype_correctwiriting');
            }
            $treelist = $string->tree_to_list();
            foreach($treelist as $node) {
               
                $string = $node->value();
                if (is_object($string)) {
                    $string = $string->string();
                }
                $tokenvalues[] = $string;
            }
        }
        $result = (object)array('tokens' => $tokenvalues, "errors" => $errormessages);
        echo json_encode($result);
    } else {
        echo '{"tokens": [], "errors": ""}';
    }
}