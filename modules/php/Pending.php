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

        $this->round_nb = game::$instance->getGameStateValue('no_round');
        $this->trick_nb = game::$instance->getGameStateValue('no_trick');
        $this->special = self::getObjectListFromDB( "SELECT id id, type type, type_arg type_arg, type_arg_2 type_arg_2 FROM specialcard WHERE round = '{$this->round_nb}'");
        $this->quest = self::getObjectListFromDB( "SELECT rand rand, validate validate FROM quest WHERE round = '{$this->round_nb}'");
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
        $round = game::$instance->getGameStateValue("no_round");
        $specialcard = self::getObjectListFromDB( "SELECT round round, rand rand, type type, type_arg type_arg, type_arg_2 type_arg_2 FROM specialcard WHERE round ='{$round}'" );
        
        $imageSpecialLog = game::$instance->getLogSpecial($specialcard);

        game::$instance->notifyAllPlayers('message', clienttranslate('${message}'), [
            'message' => [
                'log' => '<div class="log_newRound">${round} ${nb}/7</div>',
                'args' => [
                    'round' => clienttranslate('Round'),
                    'nb' => $this->round_nb,
                    'i18n' => ['round']
                ],
            ]
        ]);

        game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} deals the cards and reveals the special card of the round: ${log}'),
                array(
                    'player_name' => $this->player_name,
                    'log' => $imageSpecialLog,

                )
            );

        $players = self::getObjectListFromDB( "SELECT player_id name FROM player", true );

        foreach ($players as $player) {

            self::DbQuery("INSERT INTO bonus (round, player_id, bonus) VALUES ($round, $player, 0)");
        
        }
        
        
    }

    function argQuest($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must choose the quest for the round');
        $ret['titleyou'] = clienttranslate('${you} must choose the quest for the round');

        $ret["selectable"][] = 'questcardmodal1';
        $ret["selectable"][] = 'questcardmodal2';
       
        return $ret;
    }

    function Quest($parg1, $parg2, $varg1, $varg2)
    {
        $questremove = 0;

        if($varg1 == 'questcardmodal1')
        {
            $questremove = 2;
            self::DbQuery("UPDATE quest set validate = 1 WHERE round = '{$this->round_nb}'");
        }

        if($varg1 == 'questcardmodal2')
        {
            $questremove = 1;
            self::DbQuery("UPDATE quest set validate = 2 WHERE round = '{$this->round_nb}'");
            
        }

        $quest = self::getObjectListFromDB( "SELECT rand rand, validate validate FROM quest WHERE round = '{$this->round_nb}'" );
        $imageQuestLog = game::$instance->getLogQuest($quest);

         game::$instance->notifyAllPlayers(
                'ChoiceQuest',
                clienttranslate('${player_name} chooses the quest: ${log}'),
                array(
                    'player_name' => $this->player_name,
                    'questremove' => $questremove,
                    'log' => $imageQuestLog,
                   

                )
            );
        
        
    }

    function argFirstPlay($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('');
        $ret['titleyou'] = clienttranslate('');

                       
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

        game::$instance->notifyAllPlayers('message', clienttranslate('${message}'), [
            'message' => [
                'log' => '<div class="log_newTrick">${trick} ${nb}</div>',
                'args' => [
                    'trick' => clienttranslate('Trick'),
                    'nb' => $this->trick_nb,
                    'i18n' => ['trick']
                ],
            ]
        ]);
        
        
    }
    
    function argPlayCard($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must play a card');
        $ret['titleyou'] = clienttranslate('${you} must play a card');

        // NB TRICK ACTUEL
        $trick_no = game::$instance->getGameStateValue('no_trick');

        // POUR MOMIE ET CLONE (POUR NE PAS ETRE JOUEES AU PREMIER PLI)
        $momie = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 2 AND card_location = 'hand'");
        $clone = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 3 AND card_location = 'hand'");

        // ESACLIBUR
        $escalibur = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 5 AND card_location = 'hand'");
        

        $all_cards = self::getObjectListFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}'" );
        $color_request = game::$instance->getGameStateValue("color_request");

        $cards_1 = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 1", true);
        $cards_2 = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 2", true);
        $cards_3 = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 3", true);
        $cards_4 = self::getObjectListFromDB("SELECT card_id FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}' AND card_type = 4", true);

        if ($this->player_turn != 1) // si le joueur n'a pas joué son tour 
        {

            if($color_request == 0) //si y a pas de couleur demandée il peut jouer ce qu'il veut dans ses all_cards
            {
                foreach ($all_cards as $card) 
                {
                    $ret["selectable"][] = 'my_cards_item_' . $card['id'];

                }

                if($trick_no == 1)
                {
                    if($momie != null)
                    {
                        $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $momie]);
                    }
                    if($clone != null)
                    {
                        $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $clone]);
                    }
                }
            }

            else //si y a une couleur demandée
            {
                $cards = [];
                
                if(${'cards_'.$color_request} != null) //le joueur a la couleur demandée
                {
                    $cards = array_merge(${'cards_'.$color_request}, $cards_4); 

                    foreach ($cards as $card) 
                    {
                        $ret["selectable"][] = 'my_cards_item_' . $card;

                        if($trick_no == 1)
                        {
                            if($momie != null)
                            {
                                $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $momie]);
                            }
                            if($clone != null)
                            {
                                $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $clone]);
                            }
                        }
                    }

                    if($escalibur != null)
                    {
                        $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $escalibur]);
                    }
                    
                    
                }

                else //le joueur n'a pas la couleur demandée, il peut jouer ce qu'il veut
                {
                    foreach ($all_cards as $card) 
                    {
                        $ret["selectable"][] = 'my_cards_item_' . $card['id'];

                        if($trick_no == 1)
                        {
                            if($momie != null)
                            {
                                $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $momie]);
                            }
                            if($clone != null)
                            {
                                $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $clone]);
                            }
                        }
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

        // MOVE CARD ET INSCRIPTION DU TRICK EN BD

        $cards_played = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2 FROM cards WHERE card_location = 'table'");
        $card_id_win = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_location = 'table' AND card_location_arg = '{$winner}'");
        $trick_no = game::$instance->getGameStateValue('no_trick');
        foreach ($cards_played as $card_played) {
            game::$instance->cards->moveCard($card_played['id'], 'discard', $winner);

            self::DbQuery(
            "INSERT INTO tricks (round, trick, player_id, card_id, type, type_arg, type_arg_2) VALUES ('"
            . $this->round_nb . "', "
            . $trick_no . ", "
            . $winner . ", '"
            . $card_played['id'] . "', '"
            . $card_played['type'] . "', '"
            . $card_played['type_arg'] . "', '"
            . $card_played['type_arg_2'] . "')"
            );
        }

        self::DbQuery("UPDATE tricks set card_win = 1 WHERE card_id = '{$card_id_win}'");


        //INIT TURN
        game::$instance->DbQuery("UPDATE player set player_turn = 0 ");
        game::$instance->setGameStateValue("color_request", 0);


        $all_cards = self::getObjectListFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}'" );

        if($all_cards != null)
        {

                
            // NOUVEL ORDRE PENDING
            $nextplayer = $winner;
            self::DbQuery("DELETE FROM `pending`;");
            $count_players = count(self::getObjectListFromDB("SELECT player_id id FROM player", true));
            for ($i = 1; $i <= $count_players; $i++) {
                game::$instance->addPendingFirst($nextplayer, "PlayCard");
                $nextplayer = game::$instance->getPlayerAfter($nextplayer);
            }


            $newtrick = game::$instance->incGameStateValue('no_trick', 1);
            game::$instance->notifyAllPlayers('message', clienttranslate('${message}'), [
                'message' => [
                    'log' => '<div class="log_newTrick">${trick} ${nb}</div>',
                    'args' => [
                        'trick' => clienttranslate('Trick'),
                        'nb' => $newtrick,
                        'i18n' => ['trick']
                    ],
                ]
            ]);

        }
        else
        {
            game::$instance->addPending($this->player_id, "EndOfRound");
            
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

        game::$instance->notifyAllPlayers( 'simplePause', '', [ 'time' => 1000] ); 

        // tout le calcul de score à faire et voir si ce n'est pas la fin de partie



        //// INIT NEW ROUND + INIT TRICK ////

        // MAJ ROUND
        game::$instance->incGameStateValue('no_round', 1);
        $new_round = game::$instance->getGameStateValue('no_round');
        game::$instance->setGameStateInitialValue('no_trick', 0);

        game::$instance->notifyAllPlayers(
                        'majRound',
                        '',
                        array(
                            'new_round' => $new_round,
                            
                             
                        )
                    );

        // MOVE DISCARD TO DECK
        $all_cards = self::getObjectListFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards" );
        foreach($all_cards as $card)
        {
            game::$instance->cards->moveCard($card['id'], 'deck');

        }

        // INIT SPECIAL CARD
        if($this->special[0]['type_arg'] == 1)
        {
            self::DbQuery("UPDATE cards set card_type = 4 WHERE card_type_arg = 1");
            self::DbQuery("UPDATE cards set card_type_arg = 0 WHERE card_type = 4");
            self::DbQuery("UPDATE cards set card_type_arg_2 = 0 WHERE card_type = 4");
        }

        if($this->special[0]['type_arg'] == 11)
        {
            self::DbQuery("UPDATE cards set card_type = 4 WHERE card_type_arg = 11");
            self::DbQuery("UPDATE cards set card_type_arg = 0 WHERE card_type = 4");
            self::DbQuery("UPDATE cards set card_type_arg_2 = 0 WHERE card_type = 4");
        }
        
        // SHUFFLE
        game::$instance->cards->shuffle('deck');

        //SELECT NEW SPECIAL CARD
        $all_rand = [];
        $all_cardspecial_play = self::getObjectListFromDB( "SELECT rand rand FROM specialcard" );
        
        foreach($all_cardspecial_play as $special)
        {
            $all_rand[] = $special['rand'];
        }

        $rand = bga_rand(1, 14);

        while (in_array($rand, $all_rand))
        {
            $rand = bga_rand(1, 14);
        }

        if($rand>=1 && $rand<=5)
        {
            self::DbQuery("UPDATE cards set card_type_arg = $rand WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES ($new_round, $rand, 4, $rand, 0)");
        }

        if($rand == 6)
        {
            self::DbQuery("UPDATE cards set card_type = 1, card_type_arg = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES ($new_round, $rand, 1, 1, 0)");
        }

        if($rand == 7)
        {
            self::DbQuery("UPDATE cards set card_type = 1, card_type_arg = 11, card_type_arg_2 = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES ($new_round, $rand, 1, 11, 1)");
        }

        if($rand==8)
        {
            self::DbQuery("UPDATE cards set card_type = 1, card_type_arg = 11, card_type_arg_2 = 2 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES ($new_round, $rand, 1, 11, 2)");
        }

        if($rand == 9)
        {
            self::DbQuery("UPDATE cards set card_type = 2, card_type_arg = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES ($new_round, $rand, 2, 1, 0)");
        }

         if($rand == 10)
        {
            self::DbQuery("UPDATE cards set card_type = 2, card_type_arg = 11, card_type_arg_2 = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES ($new_round, $rand, 2, 11, 1)");
        }

        if($rand == 11)
        {
            self::DbQuery("UPDATE cards set card_type = 2, card_type_arg = 11, card_type_arg_2 = 2 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES ($new_round, $rand, 2, 11, 2)");
        }

        if($rand == 12)
        {
            self::DbQuery("UPDATE cards set card_type = 3, card_type_arg = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES ($new_round, $rand, 3, 1, 0)");
        }

         if($rand == 13)
        {
            self::DbQuery("UPDATE cards set card_type = 3, card_type_arg = 11, card_type_arg_2 = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES ($new_round, $rand, 3, 11, 1)");
        }

        if($rand == 14)
        {
            self::DbQuery("UPDATE cards set card_type = 3, card_type_arg = 11, card_type_arg_2 = 2 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES ($new_round, $rand, 3, 11, 2)");
        }

        //INIT NEW QUEST

        $all_rand = [];
        $all_cardsquest_play = self::getObjectListFromDB( "SELECT rand rand FROM quest" );
        foreach($all_cardsquest_play as $quest)
        {
            $all_rand[] = $quest['rand'];
        }

        $rand = bga_rand(1, 20);

        while (in_array($rand, $all_rand))
        {
            $rand = bga_rand(1, 20);
            
        }
    
        self::DbQuery("INSERT INTO quest (round, rand) VALUES ($new_round, $rand)");

        //DISTRIBUTION DES CARTES

        $players = self::getObjectListFromDB( "SELECT player_id name FROM player", true );
        $nbreplayers = count($players);

        if ($nbreplayers == 3)
        {           
           foreach ($players as $player) {
                game::$instance->cards->pickCards(13, 'deck', $player);
            }
        }

        if ($nbreplayers == 4)
        {
            foreach ($players as $player) {
                game::$instance->cards->pickCards(10, 'deck', $player);
            }
        }

        if ($nbreplayers == 5)
        {
            foreach ($players as $player) {
                game::$instance->cards->pickCards(8, 'deck', $player);
            }
            
        }

        foreach ($players as $player) {

        $cards = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_location_arg='{$player}'" );

            game::$instance->notifyPlayer(
                $player,
                'drawCards',
                '',
                array(

                    'cards' => $cards,

                )
            );
        }

        $active_special_card = self::getObjectListFromDB( "SELECT type type, type_arg type_arg, type_arg_2 type_arg_2 FROM specialcard WHERE round = '{$new_round}'" );
        $active_quest_card = self::getObjectListFromDB( "SELECT rand rand, validate validate FROM quest WHERE round = '{$new_round}'" );

        game::$instance->notifyAllPlayers(
                        'majModal',
                        '',
                        array(
                            'active_special_card' => $active_special_card,
                            'active_quest_card' => $active_quest_card,
                            
                             
                        )
                    );

        
        // NOUVEL ORDRE PENDING
        $first_player_deal = game::$instance->getGameStateValue("first_player_deal");
        
        $first_player_quest = intval(self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type=2 AND card_type_arg = 5"));
        game::$instance->setGameStateInitialValue("first_player_quest", $first_player_quest);

        $first_player_play = intval(self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type=3 AND card_type_arg = 5"));
        game::$instance->setGameStateInitialValue("first_player_play", $first_player_play);

        self::DbQuery("DELETE FROM `pending`;");
        $nextplayer = $first_player_play;
        $count_players = count(self::getObjectListFromDB("SELECT player_id id FROM player", true));
        for ($i = 1; $i <= $count_players; $i++) {
            game::$instance->addPendingFirst($nextplayer, "PlayCard");
            $nextplayer = game::$instance->getPlayerAfter($nextplayer);
        }

        game::$instance->addPending($first_player_play, "FirstPlay");
        game::$instance->addPending($first_player_quest, "Quest");
        game::$instance->addPending($first_player_deal, "Deal");




              
        
    }
    



















}