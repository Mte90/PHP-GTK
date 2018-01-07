<?php
# Notepad With GtkSourceView
# Description: A desktop Notepad application similar to window's Notepad.exe but with highlighted text for php
# Author: kksou & Mte90
# June 16, 2008, June 24, 2009
//error_reporting(E_ALL);
$app = new NotePad();

Gtk::main();

class NotePad {
	protected $glade;      // glade
	protected $filename;   // filename
	protected $vieww;       // buffer
	protected $bufferr;     // the corresponding text buffer
	protected $clipboard;  // clipboard
	protected $gtksource; // sourceview

	function __construct() {
		// setup glade
		$glade = new GladeXML(dirname(__FILE__).'/notepad.glade');
		$glade->signal_autoconnect_instance($this);
		$this->glade = $glade;

// setup gtksourceview and sourcebuffer
$this->buffer = new GtkSourceBuffer();
$gtksource = new GtkSourceView();
$lang_mgr = new GtkSourceLanguagesManager();
$lang = $lang_mgr->get_language_from_mime_type("application/x-php");
$this->gtksourcebufferl = $this->buffer->new_with_language($lang); 
//this property is more importante to change or get the text
$bufferr = $this->gtksourcebufferl;
$this->gtksource = $gtksource->new_with_buffer($bufferr); 
$vieww = $this->gtksource;
$bufferr->set_text('<? 
//comment
unlink();
?>');
$vieww->set_show_line_numbers(1);
$vieww->set_auto_indent(1);
$bufferr->set_highlight(1);
$bufferr->set_check_brackets(1);
$this->view = $vieww;
$this->filename = '';
$this->set_title();
$this->clipboard = new GtkClipboard($this->view->get_display(), Gdk::atom_intern('CLIPBOARD'));
$textadd= $glade->get_widget('scrolledwindow1');  
$textadd->add($vieww);
$this->gtksourcebufferl->connect('modified-changed',  'on_modified_changed');
$textadd->show_all();
}

	function set_title($modified = false) {
		$filename = $this->filename;
		if ($filename=='') $filename = 'Untitled';
		if ($modified) $title = $filename.' (*) - php-gtk2 Notepad';
		else $title = $filename.' - php-gtk2 Notepad';
		$this->glade->get_widget('window1')->set_title($title);
	}

	// process menu item selection
	function on_quit($menu_item) {
		$item = $menu_item->child->get_label();
		echo "menu selected: $item\n";
		gtk::main_quit();
	}
	public function on_menu_info($menu_item) {
		$item = $menu_item->child->get_label();
		$item2 = strtolower($item);
		echo "menu selected: $item ($item2)\n";
		$dialog = new GtkAboutDialog();
		$dialog->set_name('PHP-GTK2 Notepad With GtkSourceView');
		$dialog->set_version('1.0');
		$dialog->set_comments("A desktop Notepad With GtkSourceView application using PHP-GTK2\n\nwritten by kksou\nJune 16, 2008\n\nwritten by Mte90\nJune 24, 2009");
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
	function file_new() {
	echo "menu selected:  file_new\n";
$this->gtksourcebufferl->begin_not_undoable_action();
		$this->gtksourcebufferl->set_text('<? ?>');
$this->gtksourcebufferl->end_not_undoable_action();
		$this->gtksourcebufferl->set_modified(true);
		$this->filename = '';
		$this->set_title(true);
	}

	function file_open() {
		$dialog = new GtkFileChooserDialog("File Open", null, Gtk::FILE_CHOOSER_ACTION_OPEN, array(Gtk::STOCK_OK, Gtk::RESPONSE_OK), null);
		if ($dialog->run() == Gtk::RESPONSE_OK) {
			$this->filename = $dialog->get_filename();
			echo "selected_file = $this->filename\n";
			$contents = file_get_contents($this->filename);
			$this->gtksourcebufferl->set_text($contents);
			$this->gtksourcebufferl->set_modified(false);
			$this->set_title();
		}
		$dialog->destroy();
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
			$this->set_title(false);
		}
		$dialog->destroy();
	}

	function save_buffer() {
		$buffer_str = $this->gtksourcebufferl->get_text($this->gtksourcebufferl->get_start_iter(), $this->gtksourcebufferl->get_end_iter());
		file_put_contents($this->filename, $buffer_str);
		$this->gtksourcebufferl->set_modified(false);
	}

	function cut() {
		$this->gtksourcebufferl->cut_clipboard($this->clipboard, $this->view->get_editable());
	}

	function copy() {
		$this->gtksourcebufferl->copy_clipboard($this->clipboard);
	}

	function paste() {
		$this->gtksourcebufferl->paste_clipboard($this->clipboard, null, true);
	}

	function delete() {
		$this->gtksourcebufferl->delete_selection(true, $this->view->get_editable());
	}

	function find() {
		$dialog = $this->glade->get_widget('find_dialog');
		$this->glade->get_widget('search_entry')->set_text('');
		if ($dialog->run() == 101) {
			$this->search_str = $this->glade->get_widget('search_entry')->get_text();
			$current_insert_position = $this->gtksourcebufferl->get_iter_at_mark($this->gtksourcebufferl->get_insert());
			$this->search($this->search_str, $current_insert_position);
		}
		$dialog->hide();
	}

	function find_next() {
		$last_search_pos = $this->gtksourcebufferl->get_mark('last_search_pos');
		if ($last_search_pos==null) {print "last_search_iter undefined\n"; return;}
		$last_search_iter = $this->gtksourcebufferl->get_iter_at_mark($last_search_pos);
		$this->search($this->search_str, $last_search_iter);
	}

	function search($str, $current_insert_pos) {
		$buffer = $this->gtksourcebufferl;
		$found = $this->search_range($str, $current_insert_pos, $buffer->get_end_iter());
		if (!$found) $this->search_range($str, $buffer->get_start_iter(), $current_insert_pos);
	}

	function search_range($str, $start_iter, $end_iter) {
		$buffer = $this->gtksourcebufferl;
		$match_start = $start_iter;
		$match_end = $end_iter;
		$found = $start_iter->forward_search($str, 0, $match_start, $match_end, null);
		if ($found) {
			$buffer->select_range($match_start, $match_end);
			$buffer->create_mark('last_search_pos', $match_end, false);
		}
		return $found;
	}

	public function on_search_entry_activate($entry) {
		$this->glade->get_widget('search_button')->clicked();
	}

	public function on_search_button_clicked($button) {
		$this->glade->get_widget('find_dialog')->response(101);
	}

	function select_all() {
		$this->gtksourcebufferl->select_range($this->gtksourcebufferl->get_start_iter(), $this->gtksourcebufferl->get_end_iter());
	}

	function time_date() {
		$this->gtksourcebufferl->insert_at_cursor(date('h:i A n/j/Y'));
	}

	function font() {
		$dialog = new GtkFontSelectionDialog('Select Font');
         if ($dialog->run() == Gtk::RESPONSE_OK) {
            $fontname = $dialog->get_font_name();
            $this->view->modify_font(new PangoFontDescription($fontname));
        }
        $dialog->destroy();
	}

	public function on_modified_changed($buffer) {
		$this->set_title(true);
	}

}

?>