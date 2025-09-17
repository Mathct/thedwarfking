<?php

namespace Bga\Games\thedwarfking;    // ATTENTION

trait ActionsTrait  // ATTENTION
{
    public function argAction1($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} pass');
        $ret['titleyou'] = clienttranslate('${you} pass');

        

        return $ret;
    }

    public function Action1($parg1, $parg2, $varg1, $varg2)
    {
        
    }
}