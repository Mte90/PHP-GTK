<?php
# Notepad With GtkSourceView
# Description: A desktop Notepad application similar to window's Notepad.exe but with highlighted text for php
# Author: kksou & Mte90
# June 16, 2008, June 24, 2009
//error_reporting(E_ALL);
include(dirname(__FILE__) . '/otherfunction.php');
include(dirname(__FILE__) . '/phpapi.php');

$app = new PHPEDITOR();
Gtk::main();
class PHPEDITOR {
protected $glade;  // glade
protected $filename;   // filename
protected $vieww;   // buffer
protected $bufferr; // the corresponding text buffer
protected $clipboard;  // clipboard
protected $gtksource; // sourceview

function __construct() {
// setup glade
$glade = new GtkBuilder();
$glade->add_from_file(dirname(__FILE__) . '/phpeditor.glade'); 
$glade->connect_signals_instance($this);
$this->glade = $glade;

// setup gtksourceview and sourcebuffer
for($i=1;$i<10;$i++){
$buffer = "buffer".$i;
$gtksource = "gtksource".$i;
$lang = "lang".$i;
$lang_mgr = "lang_mgr".$i;
$gtksourcebufferl = "gtksourcebufferl".$i;
$pagefilename = "pagefilename".$i;
$this->$buffer = new GtkSourceBuffer();
$this->$gtksource = new GtkSourceView();
$this->$lang_mgr = new GtkSourceLanguagesManager();
$this->$lang = $this->$lang_mgr->get_language_from_mime_type("application/x-php");
$this->$gtksourcebufferl = $this->$buffer->new_with_language($this->$lang); 
//this property is more importante to change or get the text
$bufferr = $this->$gtksourcebufferl;
$this->$gtksource = $this->$gtksource->new_with_buffer($bufferr); 
$vieww = $this->$gtksource;
$bufferr->set_text('');
$vieww->set_show_line_numbers(1);
$vieww->set_auto_indent(1);
$bufferr->set_highlight(1);
$bufferr->set_check_brackets(1);
$this->view = $vieww;
$this->filename = '';
$this->clipboard = new GtkClipboard($this->view->get_display(), Gdk::atom_intern('CLIPBOARD'));
$textadd= $glade->get_object('scrolledwindow'.$i);  
$textadd->add($vieww);
$textadd->show_all();
if ($i!='1') $this->glade->get_object('alignment'.$i)->hide();
$this->$pagefilename = '*';
$this->$gtksourcebufferl->connect('modified-changed',array($this, 'on_modified_changed'),$i);
$this->$gtksource->set_wrap_mode(Gtk::WRAP_WORD);
$targets = array( array( 'text/uri-list', 0, 0));  
$this->$gtksource->drag_dest_set( GTK_DEST_DEFAULT_MOTION, $targets, GDK_ACTION_MOVE );
$this->$gtksource->connect('drag-data-received',array($this,'on_drop'),$i);
$this->$gtksource->set_property("has-tooltip",true);
$this->$gtksource->connect('query-tooltip',array($this,'show_tooltip'),$i);
$this->glade->get_object('alignment'.$i)->trigger_tooltip_query();
}
$this->set_title(false,'*','');
$this->numpagep = 1;
$this->numpage = 0;
}
function show_tooltip($widget, $x, $y, $keyboard_mode, $tooltip,$i) {
$gtksourcebufferl = "gtksourcebufferl".$i;
$iter3 = $this->$gtksourcebufferl->get_start_iter();
$iter2 = $this->$gtksourcebufferl->get_end_iter();
$iter = $this->$gtksourcebufferl->get_iter_at_mark($this->$gtksourcebufferl->get_mark('insert'));
$iteriter2 = $this->$gtksourcebufferl->get_text($iter,$iter2);
$test = explode(" ",$iteriter2);
$test = explode("\n",$test[0]);
$testv = count($test);
$par = "(";$parr = ")";
$text = trim($test[$testv-1]);
$last = $text{strlen($test[$testv-1])-1};
if ($last == $par||$parr){
echo $tooltip->width;
$base = explode("\n", $this->$gtksourcebufferl->get_text($iter3,$iter));
$basev = count($base);
preg_match("/[^ ]*$/", $base[$basev -1], $matchess);
preg_match("/([\w\d ]+)\((.*)/", $iteriter2, $matches);
$matches = explode(")",$matches[1]);
$check = highlightWords($matchess[0].$matches[0]);
if ($check != ""){
$tooltip->set_markup($check);
return true;} else {return false;} 
} else {
return false;
} 
}
function tab_remove() {
$tab = $this->glade->get_object('notebook1')->get_current_page() +1;
$gtksourcebufferl = "gtksourcebufferl".$tab;
$this->$gtksourcebufferl->set_text('');
$this->$gtksourcebufferl->end_not_undoable_action();
$this->$gtksourcebufferl->set_modified(false);
$pagefilename = 'pagefilename'.$tab;
$this->$pagefilename = '*';
$this->glade->get_object('alignment'.$tab)->hide();
$poggio = $this->numpage;
$this->numpage = $poggio - 1;
$this->set_title(false,'*',$this->glade->get_object('notebook1')->get_current_page() +1);
}
function set_title($modified = false,$titlet,$numPage) {
if ($modified == true) { 
$poggiom = " (*)";
}
if ($titlet =='*') { 
$filename = 'Untitled';
$title = $filename.$poggiom.' - PHP-Editor';
} elseif ($titlet !='*') {
$title = $titlet.$poggiom.' - PHP-Editor';
}
$poggio = $this->numpage;
if ($this->numpage == 0) {
$poggio = 1;
}
$title = $title." [".($this->glade->get_object('notebook1')->get_current_page() +1)." of ".$poggio."]";
echo "\n title:".$titlet."\n";
$this->glade->get_object('window1')->set_title($title);
}

// process menu item selection
function file_new() {
$this->numpage = $this->numpagep + $this->numpage;
$this->add_page('',false,'*');
$this->filename = '*';
}
function add_page($text,$smodified,$filen) {
if ($this->numpage == 1) {
$this->numpage = $this->numpage +1;
}
if ($this->numpage == 0) {
$this->numpage = 1;
}
if ($this->numpage < 10) {
$gtksourcebufferl = "gtksourcebufferl".$this->numpage;
$this->glade->get_object('notebook1')->next_page();
$this->$gtksourcebufferl->begin_not_undoable_action();
$this->$gtksourcebufferl->set_text($text);
$this->$gtksourcebufferl->end_not_undoable_action();
$this->$gtksourcebufferl->set_modified($smodified);
$this->glade->get_object('alignment'.$this->numpage)->show();
$this->glade->get_object('label'.$this->numpage)->set_text(basename($filen));
$pagefilename = 'pagefilename'.$this->numpage;
$this->$pagefilename = $filen;
$this->set_title(true,$filen,$this->numpage);
} else {
$this->numpage = 9;
alert("Max Quote Of Tab...");
}
}
function change_page($ntbk, $pointer, $pageNum) {
$pagefilename = 'pagefilename'.($this->glade->get_object('notebook1')->get_current_page() +1);
$this->set_title(false,$this->$pagefilename,$this->glade->get_object('notebook1')->get_current_page() +1);
echo "switchpage:".($this->glade->get_object('notebook1')->get_current_page() +1)."\n";
}
function file_open() {
if ($this->numpage < 10) {
$dialog = new GtkFileChooserDialog("File Open", null, Gtk::FILE_CHOOSER_ACTION_OPEN, array(Gtk::STOCK_OK, Gtk::RESPONSE_OK), null);
if ($dialog->run() == Gtk::RESPONSE_OK) {
$this->filename = $dialog->get_filename();
echo "selected_file = $this->filename\n";
$contents = file_get_contents($this->filename);
if ($this->gtksourcebufferl1->get_text($this->gtksourcebufferl1->get_start_iter(), $this->gtksourcebufferl1->get_end_iter()) == "") {
$this->gtksourcebufferl1->set_text($contents);
$this->set_title(false,$this->filename,1);
$this->glade->get_object('label1')->set_text(basename($this->filename));
$pagefilename = 'pagefilename1';
$this->$pagefilename = $this->filename;
} else {
$this->numpage = $this->numpagep + $this->numpage;
$this->add_page($contents,false,$this->filename);
$pagefilename = 'pagefilename'.$this->numpage;
$this->$pagefilename = $this->filename;
$this->glade->get_object('notebook1')->next_page();
}
echo "open:".$this->$pagefilename."\n";
}
$dialog->destroy();
} else {
$this->numpage = 9;
alert("Max Quote Of Tab...");
}
}
function undo() {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$this->$gtksourcebufferl->undo();
}
function redo() {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$this->$gtksourcebufferl->redo();
}
function file_save() {
print "File Saved\n";
if ($this->filename!='') $this->save_buffer();
else $this->save_as();
}

function save_as() {
$dialog = new GtkFileChooserDialog("File Save", null, Gtk::FILE_CHOOSER_ACTION_SAVE, array(Gtk::STOCK_OK, Gtk::RESPONSE_OK), null);
if ($dialog->run() == Gtk::RESPONSE_OK) {
$this->filename = $dialog->get_filename();
$this->save_buffer();
$labeln = ($this->glade->get_object('notebook1')->get_current_page() + 1);
$this->glade->get_object('label'.$labeln)->set_text(basename($this->filename));
}
$dialog->destroy();
}

function save_buffer() {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() +1);
$buffer_str = $this->$gtksourcebufferl->get_text($this->$gtksourcebufferl->get_start_iter(), $this->$gtksourcebufferl->get_end_iter());
$this->$gtksourcebufferl->set_modified(false);
file_put_contents($this->filename, $buffer_str);
$numpage = $this->glade->get_object('notebook1')->get_current_page() + 1;
$pagefilename = 'pagefilename'.$numpage;
$this->$pagefilename = $this->filename;
$this->set_title(false,$this->$pagefilename,$numpage);
}
function cut() {
$gtksource = "gtksource".($this->glade->get_object('notebook1')->get_current_page() + 1);
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$this->$gtksourcebufferl->cut_clipboard($this->clipboard, $this->$gtksource->get_editable());
}
function copyy() {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$this->$gtksourcebufferl->copy_clipboard($this->clipboard);
}

function paste() {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$this->$gtksourcebufferl->paste_clipboard($this->clipboard, null, true);
}

function deletee() {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$this->$gtksourcebufferl->delete_selection(true, $this->view->get_editable());
}

function find() {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$dialog = $this->glade->get_object('find_dialog');
$this->glade->get_object('search_entry')->set_text('');
if ($dialog->run() == 101) {
$this->search_str = $this->glade->get_object('search_entry')->get_text();
$current_insert_position = $this->$gtksourcebufferl->get_iter_at_mark($this->$gtksourcebufferl->get_insert());
$this->search($this->search_str, $current_insert_position);
}
$dialog->hide();
}

function find_next() {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$last_search_pos = $this->$gtksourcebufferl->get_mark('last_search_pos');
if ($last_search_pos==null) {print "last_search_iter undefined\n"; return;}
$last_search_iter = $this->$gtksourcebufferl->get_iter_at_mark($last_search_pos);
$this->search($this->search_str, $last_search_iter);
}

function search($str, $current_insert_pos) {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$buffer = $this->$gtksourcebufferl;
$found = $this->search_range($str, $current_insert_pos, $buffer->get_end_iter());
if (!$found) $this->search_range($str, $buffer->get_start_iter(), $current_insert_pos);
}

function search_range($str, $start_iter, $end_iter) {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$buffer = $this->$gtksourcebufferl;
$match_start = $start_iter;
$match_end = $end_iter;
$found = $start_iter->forward_search($str, 0, $match_start, $match_end, null);
if ($found) {
$buffer->select_range($match_start, $match_end);
$buffer->create_mark('last_search_pos', $match_end, false);
}
return $found;
}

function on_search_entry_activate($entry) {
$this->glade->get_object('search_button')->clicked();
}

function on_search_button_clicked($button) {
$this->glade->get_object('find_dialog')->response(101);
}

function select_all() {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$this->$gtksourcebufferl->select_range($this->$gtksourcebufferl->get_start_iter(), $this->$gtksourcebufferl->get_end_iter());
}
function on_drop($object, $context, $x, $y, $data,$info, $time,$i) {
$uri_list = explode("\n",$data->data);
$pagefilename = "pagefilename".$i;
$this->$pagefilename = $uri_list[0];
$filename = str_replace("file://", "", $this->$pagefilename);
$filename = str_replace("\r", "", $this->$pagefilename);
$contents = file_get_contents($this->$pagefilename);
$this->add_page($contents,false,$this->$pagefilename);
}
function time_date() {
$gtksourcebufferl = "gtksourcebufferl".($this->glade->get_object('notebook1')->get_current_page() + 1);
$this->$gtksourcebufferl->insert_at_cursor(date('h:i A n/j/Y'));
}

function font() {
$dialog = new GtkFontSelectionDialog('Select Font');
 if ($dialog->run() == Gtk::RESPONSE_OK) {
$fontname = $dialog->get_font_name();
$this->view->modify_font(new PangoFontDescription($fontname));
}
$dialog->destroy();
}

function on_modified_changed($buffer,$i) {
$pagefilename = 'pagefilename'.$i;
$this->set_title(true,$this->$pagefilename,$i);
}
function on_quit($menu_item) {
$item = $menu_item->child->get_label();
echo "menu selected: $item\n";
gtk::main_quit();
}
function on_menu_info($menu_item) {
$dialog = new GtkAboutDialog();
$dialog->set_program_name('PHP-Editor In Php-Gtk2');
$dialog->set_version('1.0');
$dialog->set_comments("PHP-Editor In Php-Gtk2, with Api Generator\n\nwritten by Mte90\nJuly 11, 2009");
$top_area = $dialog->vbox;
$top_area->pack_start($vbox = new GtkVBox());
$logo = GtkImage::new_from_file(dirname(__FILE__) .'/antipixel.png');
$vbox->pack_start($logo);
$link_button1 = new GtkLinkButton("http://www.mte90.net","About");
$vbox->pack_start($link_button1);
$logod = GtkImage::new_from_file(dirname(__FILE__) .'/no1984.png');
$vbox->pack_start($logod);
$link_button1 = new GtkLinkButton("http://www.no1984.org","No1984");
$vbox->pack_start($link_button1);
$dialog->show_all();
$dialog->run();
$dialog->destroy();
}
}
?>