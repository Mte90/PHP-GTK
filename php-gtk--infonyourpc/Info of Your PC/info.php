<?php
//load glade file
$glade = new GladeXML(dirname(__FILE__) . '/info.glade');
$glade->signal_autoconnect();
//language check
if (file_exists(dirname(__FILE__) . '/lang/'.substr($_SERVER["LANG"],0,2).'.str')) {
    include(dirname(__FILE__) . '/lang/'.substr($_SERVER["LANG"],0,2).'.str');
}else {
    include(dirname(__FILE__) . '/lang/en.str');
}
//Add in variables the object of the widget
$labeluno= $glade->get_widget('label1');  
$labeldue= $glade->get_widget('label2');  
$labeltre= $glade->get_widget('label3');  
$labelquattro= $glade->get_widget('label4');  
$labelcinque= $glade->get_widget('label5');  
$labelsei= $glade->get_widget('label6');  
$labelsette= $glade->get_widget('label7');  
$labelotto= $glade->get_widget('label8');  
$pulsante= $glade->get_widget('button1');  
//change the text of the widget
$labeluno ->set_text($lang[1].PHP_OS);
$labeldue ->set_text($lang[2].php_uname(n));
$labeltre ->set_text($lang[3].php_uname(m));
$labelquattro ->set_text($lang[4].(memory_get_peak_usage(true)/1024).' kb'.$lang[5]);
$labelcinque ->set_text(date("d-m-Y H:i:s",mktime()));
$labelsei ->set_text($lang[6].$_SERVER["SHELL"]);
$labelsette ->set_text($lang[7].substr($_SERVER["LANG"],0,2));
$labelotto ->set_text($lang[8].phpversion());
$pulsante->set_label('About');  
///add a function on button
function on_button1_clicked() {
    about();
}
function about() {
    $dialog = new GtkDialog('Alert', null, Gtk::DIALOG_MODAL);
    $dialog->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
    $top_area = $dialog->vbox;
    $top_area->pack_start($vbox = new GtkVBox());
    $vbox->pack_start(new GtkLabel("Mte90 Production\nInfo of your Pc"));
    $logo = GtkImage::new_from_file(dirname(__FILE__) .'/antipixel.png');
    $vbox->pack_start($logo);
    $link_button1 = new GtkLinkButton("http://www.mte90.net","About");
    $vbox->pack_start($link_button1);
    $logod = GtkImage::new_from_file(dirname(__FILE__) .'/no1984.png');
    $vbox->pack_start($logod);
    $link_button1 = new GtkLinkButton("http://www.no1984.org","No1984");
    $vbox->pack_start($link_button1);
    $dialog->add_button(Gtk::STOCK_OK, Gtk::RESPONSE_OK);
    $dialog->set_has_separator(false);
    $dialog->show_all();
    $dialog->run();
    $dialog->destroy();
}
Gtk::main();
?>