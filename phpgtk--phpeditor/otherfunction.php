<?php
function alert($msg) {
    $dialog = new GtkDialog('Alert', null, Gtk::DIALOG_MODAL);
    $dialog->set_position(Gtk::WIN_POS_CENTER_ALWAYS);
    $top_area = $dialog->vbox;
    $top_area->pack_start($hbox = new GtkHBox());
    $stock = GtkImage::new_from_stock(Gtk::STOCK_DIALOG_WARNING,Gtk::ICON_SIZE_DIALOG);
    $hbox->pack_start($stock, 0, 0);
    $hbox->pack_start(new GtkLabel($msg));
    $dialog->add_button(Gtk::STOCK_OK, Gtk::RESPONSE_OK);
    $dialog->set_has_separator(false);
    $dialog->show_all();
    $dialog->run();
    $dialog->destroy();
}
function highlightWords($text){
global $funclist,$funcdest;
$text = str_replace(")","",(str_replace("(","",$text)));
foreach ($funclist as $key => $value){
if ($text == $value ){
$arrayb = explode("<&%>", $funcdest[$key]); 
$textt =  "<b>".$arrayb[0]."</b>\n".$arrayb[1];
}
}
return $textt;
}
?>