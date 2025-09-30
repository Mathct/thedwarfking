<?php

namespace Bga\Games\thedwarfking;   // ATTENTION NOM DU JEU
use APP_GameClass;

require_once 'actions/Actions.php'; // Inclure le fichier contenant les fonctions

class Pending extends APP_GameClass
{
    use ActionsTrait; // ATTENTION

    public function __construct($player_id)
    {
        $this->player_id = $player_id;
        $p = self::getObjectFromDB("SELECT * FROM player WHERE player_id = {$player_id}");        
        $this->player_no = $p['player_no'];
        $this->player_id = $p['player_id'];
        $this->player_name = $p['player_name'];
        $this->player_score = $p['player_score'];
        $this->player_color = $p['player_color'];
        $this->player_turn = $p['player_turn'];
    }





    function argDeal($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('');
        $ret['titleyou'] = clienttranslate('');

                       
        return $ret;
    }

    function Deal($parg1, $parg2, $varg1, $varg2)
    {
        

        game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} deals the cards and reveals the special card of the round'),
                array(
                    'player_name' => $this->player_name,

                )
            );
        
        
    }

    function argQuest($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} blabla2');
        $ret['titleyou'] = clienttranslate('${you} blabla1');

                       
        return $ret;
    }

    function Quest($parg1, $parg2, $varg1, $varg2)
    {
         game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} chooses the quest'),
                array(
                    'player_name' => $this->player_name,

                )
            );
        
        
    }

    function argFirstPlay($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} blabla2');
        $ret['titleyou'] = clienttranslate('${you} blabla1');

                       
        return $ret;
    }

    function FirstPlay($parg1, $parg2, $varg1, $varg2)
    {
        game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} starts the round'),
                array(
                    'player_name' => $this->player_name,

                )
            );
        
        
    }
    
    function argPlayCard($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} blabla2');
        $ret['titleyou'] = clienttranslate('${you} blabla1');

        $all_cards = self::getObjectListFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}'" );
        $color_request = game::$instance->getGameStateValue("color_request");

        $cards_1 = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 1", true);
        $cards_2 = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 2", true);
        $cards_3 = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 3", true);
        $cards_4 = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 4", true);

        if ($this->player_turn != 1) // si le joueur n'a pas joué son tour
        {

            if($color_request == 0) 
            {
                foreach ($all_cards as $card) 
                {
                    $ret["selectable"][] = 'my_cards_item_' . $card['id'];
                }
            }

            else 
            {
                $cards = [];
                
                if(${'cards_'.$color_request} != null)
                {
                    $cards = array_merge(${'cards_'.$color_request}, $cards_4); 

                    foreach ($cards as $card) 
                    {
                        $ret["selectable"][] = 'my_cards_item_' . $card;
                    }
                    
                    $player_color_request = 1; //le joueur a la couleur demandée
                }

                else //le joueur n'a pas la couleur demandée, il peut jouer ce qu'il veut dans ses all_cards
                {
                    foreach ($all_cards as $card) 
                    {
                        $ret["selectable"][] = 'my_cards_item_' . $card['id'];
                    }
                }

            }
        }

                
        return $ret;
    }

    function PlayCard($parg1, $parg2, $varg1, $varg2)
    {
        if($varg1 == null){
            
            game::$instance->addPending($this->player_id, "EndOfTurn");
            
        }

        else
        {

            $explode = explode('_', $varg1);
            $card_id = end($explode);

            $type = self::getUniqueValueFromDB("SELECT card_type FROM cards WHERE card_id='{$card_id}'");
            $type_arg = self::getUniqueValueFromDB("SELECT card_type_arg FROM cards WHERE card_id='{$card_id}'");

            $card_play = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_id = {$card_id}");

            game::$instance->cards->moveCard($card_id, 'table', $this->player_id);
            
            game::$instance->notifyAllPlayers(
                        'playCard',
                        clienttranslate('${player_name} plays card'),
                        array(
                            'player_name' => $this->player_name,
                            'card_play' => $card_play,
                             
                        )
                    );

        
            // Test si color_request doit être modifiée
            if (game::$instance->getGameStateValue("color_request") == 0)
            {
                if (($type == 1) || ($type == 2) || ($type == 3))
                {
                    game::$instance->setGameStateValue("color_request", $type);
                }

            }

            
            game::$instance->DbQuery("UPDATE player set player_turn = 1 WHERE player_id = '{$this->player_id}'");
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "PlayCard");

        }
        
    }

    function argEndOfTurn($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('End of Turn');
        $ret['titleyou'] = clienttranslate('End of Turn');

                       
        return $ret;
    }

    function EndOfTurn($parg1, $parg2, $varg1, $varg2)
    {

        // WINNER
        $winner = game::$instance->winnerOfTurn();

        $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$winner}'");

        game::$instance->notifyAllPlayers(
                'endTurn',
                clienttranslate('${player_name} wins the trick and begins the next trick'),
                array(
                    'winner_id' => $winner,
                    'player_name' => $winner_name,
                )
        );

        $cards_played = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_location = 'table'", true);
        foreach ($cards_played as $card_played) {
            game::$instance->cards->moveCard($card_played, 'discard', $winner);
        }

        //INIT TURN
        game::$instance->DbQuery("UPDATE player set player_turn = 0 ");
        game::$instance->setGameStateValue("color_request", 0);

        // CHANGE FIRST PLAYER TRICK
        game::$instance->setGameStateValue("first_player_play", $winner);
        
        // NOUVEL ORDRE PENDING
        $nextplayer = $winner;
        self::DbQuery("DELETE FROM `pending`;");
        $count_players = count(self::getObjectListFromDB("SELECT player_id id FROM player", true));
        for ($i = 1; $i <= $count_players; $i++) {
            game::$instance->addPendingFirst($nextplayer, "PlayCard");
            $nextplayer = game::$instance->getPlayerAfter($nextplayer);
        }

        
        
    }







    function argEndOfRound($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('End of Round');
        $ret['titleyou'] = clienttranslate('End of Round');

                       
        return $ret;
    }

    function EndOfRound($parg1, $parg2, $varg1, $varg2)
    {
        
        
    }

















}