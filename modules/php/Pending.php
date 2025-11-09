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

        if($round <= 7)
        {       
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
        }

        if( $round > 7)
        {
            game::$instance->notifyAllPlayers('message', clienttranslate('${message}'), [
                'message' => [
                    'log' => '<div class="log_newRound">${round}</div>',
                    'args' => [
                        'round' => clienttranslate('Tiebreaker Round'),
                        'i18n' => ['round']
                    ],
                ]
            ]);

        }

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

        $players_id_quest = game::$instance->getGameStateValue("first_player_quest");
        $player_name_quest = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$players_id_quest}'");

        game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} has ${log} and must choose the quest for this round'),
                array(
                    'player_name' => $player_name_quest,
                    'log' => game::$instance->getLogIcon('2_5'),

                )
            );

        //SORCIER
        $player_sorcier = self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type = 4 AND card_type_arg = 4 AND card_location = 'hand'");
        if($player_sorcier != null)
        {
            game::$instance->setGameStateValue("sorcier_player", $player_sorcier);
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
                clienttranslate('${player_name} chooses this quest: ${log}'),
                array(
                    'player_name' => $this->player_name,
                    'questremove' => $questremove,
                    'log' => $imageQuestLog,
                   

                )
            );

        game::$instance->giveExtraTime($this->player_id);

        // PE_ROUGE
        $player_pe_rouge = self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type = 3 AND card_type_arg = 11 AND card_type_arg_2 = 2 AND card_location = 'hand'");
        if($player_pe_rouge != null)
        {
            game::$instance->setGameStateValue("pe_rouge_player", $player_pe_rouge);
            game::$instance->addPending($player_pe_rouge, "PeRed");
        }
        
        
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
                clienttranslate('${player_name} has ${log} and starts the round'),
                array(
                    'player_name' => $this->player_name,
                    'log' => game::$instance->getLogIcon('3_5'),

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
        // NB ROUND ACTUEL
        $round = game::$instance->getGameStateValue('no_round');

        // MOMIE
        $momie = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 2 AND card_location = 'hand' AND card_location_arg = '{$this->player_id}'");
        $last_trick = $trick_no - 1;
        $type_last_card_win = self::getUniqueValueFromDB("SELECT type FROM tricks WHERE round = '{$round}' AND trick = '{$last_trick}' AND card_win = 1");
        
        // CLONE
        $clone = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 3 AND card_location = 'hand' AND card_location_arg = '{$this->player_id}'");
        $count_cards_table = count(self::getObjectListFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location = 'table'" ));

        // ESACLIBUR
        $excalibur = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 5 AND card_location = 'hand' AND card_location_arg = '{$this->player_id}'");

        // SORCIER
        $sorcier = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 4 AND card_location = 'hand' AND card_location_arg = '{$this->player_id}'");
        

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

                if($momie != null && $trick_no == 1)
                { 
                    $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $momie]);   
                }

                if($clone != null && $trick_no == 1 && $count_cards_table == 0)
                {
                    $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $clone]);
                }

                if($sorcier != null)
                {
                    $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $sorcier]);
                }
            }

            else //si y a une couleur demandée
            {
                $cards = [];

                if($momie != null && $trick_no == 1)
                {
                    $cards = ${'cards_'.$color_request};
                }

                elseif($momie != null && $trick_no >= 2)
                {
                    if($color_request == $type_last_card_win)
                    {
                        $cards = array_merge(${'cards_'.$color_request}, $cards_4);
                    }
                    else {
                        $cards = ${'cards_'.$color_request};
                    }
                }

                elseif($sorcier !=null)
                {
                    $cards = ${'cards_'.$color_request};
                }

                elseif($excalibur !=null)
                {
                    $cards = ${'cards_'.$color_request};
                }

                else 
                {
                    $cards = array_merge(${'cards_'.$color_request}, $cards_4);
                }


                
                if($cards != null) //le joueur a la couleur demandée
                {

                    foreach ($cards as $card) 
                    {
                        $ret["selectable"][] = 'my_cards_item_' . $card;

                    }                    
                    
                }

                else //le joueur n'a pas la couleur demandée, il peut jouer ce qu'il veut
                {
                    
                    foreach ($all_cards as $card) 
                    {
                        $ret["selectable"][] = 'my_cards_item_' . $card['id'];
                    }

                    if($momie != null && $trick_no == 1)
                    {   
                        $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $momie]);
                    }

                    if($sorcier != null)
                    {
                        $ret["selectable"] = array_diff($ret["selectable"], ['my_cards_item_' . $sorcier]);
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
            $card_id = intval(end($explode));

            $type = self::getUniqueValueFromDB("SELECT card_type FROM cards WHERE card_id='{$card_id}'");
            $type_arg = self::getUniqueValueFromDB("SELECT card_type_arg FROM cards WHERE card_id='{$card_id}'");
            $type_arg_2 = self::getUniqueValueFromDB("SELECT card_type_arg_2 FROM cards WHERE card_id='{$card_id}'");

            $card_play = self::getObjectFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_id = {$card_id}");

            game::$instance->cards->moveCard($card_id, 'table', $this->player_id);

            
            // pour la Momie
            $momie = 0;
            if (($type == 4) && ($type_arg == 2))
            {
                $momie = 1;
            }

            $trick_no = game::$instance->getGameStateValue('no_trick');
            $round = game::$instance->getGameStateValue('no_round');
            $last_trick = $trick_no - 1;
            $type_last_card_win = self::getUniqueValueFromDB("SELECT type FROM tricks WHERE round = '{$round}' AND trick = '{$last_trick}' AND card_win = 1");
            $typearg_last_card_win = self::getUniqueValueFromDB("SELECT type_arg FROM tricks WHERE round = '{$round}' AND trick = '{$last_trick}' AND card_win = 1");

            // pour le Clone: sauvergarde de la derniere card_id jouée (n'est pas mise à jour dès que le Clone est joué)
            $last_type = 0;
            $last_type_arg = 0;
            $clone = 0;
            if (($type == 4) && ($type_arg == 3))
            {
                $clone = 1;
                game::$instance->setGameStateValue("clone_play", 1);
                $last_card_id = game::$instance->getGameStateValue("last_card_play");
                $last_type = self::getUniqueValueFromDB("SELECT card_type FROM cards WHERE card_id='{$last_card_id}'");
                $last_type_arg = self::getUniqueValueFromDB("SELECT card_type_arg FROM cards WHERE card_id='{$last_card_id}'");
            }

            if(game::$instance->getGameStateValue("clone_play") != 1)
            {
                game::$instance->setGameStateValue("last_card_play", $card_id);
                $clone_id = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 3 AND card_location = 'hand'");
                if($clone_id != null)
                    {
                        
                        $clone_player = self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type = 4 AND card_type_arg = 3 AND card_location = 'hand'");
                        $last_type = self::getUniqueValueFromDB("SELECT card_type FROM cards WHERE card_id='{$card_id}'");
                        $last_type_arg = self::getUniqueValueFromDB("SELECT card_type_arg FROM cards WHERE card_id='{$card_id}'");

                        game::$instance->notifyPlayer(
                        $clone_player,
                        'cloneChange',
                        '',
                        array(
                            'clone_id' => $clone_id,
                            'clone_player' => $clone_player,
                            'type_last_card' => $last_type,
                            'typearg_last_card' => $last_type_arg

                        )
                    );

                }
               

                
            }



            $log = $type.'_'.$type_arg;
            $image_log = game::$instance->getLogIcon($log);
            
            game::$instance->notifyAllPlayers(
                        'playCard',
                        clienttranslate('${player_name} plays ${log}'),
                        array(
                            'player_name' => $this->player_name,
                            'card_play' => $card_play,
                            'log' => $image_log,
                            'momie' => $momie,
                            'type_last_card_win' => $type_last_card_win,
                            'typearg_last_card_win' => $typearg_last_card_win,
                            'clone' => $clone,
                            'type_last_card' => $last_type,
                            'typearg_last_card' => $last_type_arg,

                             
                        )
                    );

            
            // message clone
            if (($type == 4) && ($type_arg == 3))
            {
                $log2 = $last_type.'_'.$last_type_arg;
                $image_log2 = game::$instance->getLogIcon($log2);

                game::$instance->notifyAllPlayers(
                        'message',
                        clienttranslate('${log} is the same color as ${log2} (with a slightly higher value)'),
                        array(
                            
                            'log' => $image_log,
                            'log2' => $image_log2,
                             
                        )
                    );

            }
           

            

                    
            // Test si color_request doit être modifiée
            if (game::$instance->getGameStateValue("color_request") == 0)
            {
                if (($type == 1) || ($type == 2) || ($type == 3))
                {
                    game::$instance->setGameStateValue("color_request", $type);
                }

                if (($type == 4) && ($type_arg == 2))
                {
                    game::$instance->setGameStateValue("color_request", $type_last_card_win);
                }

                if (($type == 4) && ($type_arg == 3))
                {
                    game::$instance->setGameStateValue("color_request", $last_type);
                }


            }


            

            
            game::$instance->DbQuery("UPDATE player set player_turn = 1 WHERE player_id = '{$this->player_id}'");
            game::$instance->giveExtraTime($this->player_id);
            game::$instance->addPendingFirst($this->player_id, "PlayCard");


            //DRUIDE OU PE_BLEU

            $ticks_max = 0;
            $nbreplayers = count(self::getObjectListFromDB( "SELECT player_id FROM player", true ));

            if($nbreplayers == 3)
            {
                $ticks_max = 13;
            }
            if($nbreplayers == 4)
            {
                $ticks_max = 10;
            }
            if($nbreplayers == 5)
            {
                $ticks_max = 8;
            }

            if ($trick_no != $ticks_max && $type == 1 && $type_arg == 1)
            {
                game::$instance->setGameStateValue("druide_player", $this->player_id);
            }
            
            if ($trick_no != $ticks_max && $type == 2 && $type_arg == 11 && $type_arg_2 == 1)
            {
                game::$instance->setGameStateValue("pe_bleu_player", $this->player_id);
            } 

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

        // SCORE TRICK PANNEL

        $round = game::$instance->getGameStateValue('no_round');
        $before_tricks_win = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$winner}'", true ));
        game::$instance->notifyAllPlayers(
                            'majTricksWin',
                            '',
                            array(
                                'player' => $winner,
                                'newtricks' => $before_tricks_win + 1
                                
                                
                            )
                        );


        // MOVE CARD ET INSCRIPTION DU TRICK EN BD

        $cards_played = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location_arg location_arg FROM cards WHERE card_location = 'table'");
        $card_id_win = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_location = 'table' AND card_location_arg = '{$winner}'");
        $trick_no = game::$instance->getGameStateValue('no_trick');
        foreach ($cards_played as $card_played) {
            game::$instance->cards->moveCard($card_played['id'], 'discard', $winner);

            self::DbQuery(
            "INSERT INTO tricks (round, trick, player_id, player_win, card_id, type, type_arg, type_arg_2) VALUES ('"
            . $this->round_nb . "', "
            . $trick_no . ", '"
            . $card_played['location_arg'] . "', "
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
        game::$instance->setGameStateValue('first_player_play', $winner);


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



            // MOMIE DISPLAY SI PAS JOUEE
            $momie_id = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 2 AND card_location = 'hand'");
            $momie_player = self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type = 4 AND card_type_arg = 2 AND card_location = 'hand'");
            $round = game::$instance->getGameStateValue('no_round');
            $trick = game::$instance->getGameStateValue('no_trick');
            $last_trick = game::$instance->getGameStateValue('no_trick') - 1;
            $type_last_card_win = self::getUniqueValueFromDB("SELECT type FROM tricks WHERE round = '{$round}' AND trick = '{$last_trick}' AND card_win = 1");
            $typearg_last_card_win = self::getUniqueValueFromDB("SELECT type_arg FROM tricks WHERE round = '{$round}' AND trick = '{$last_trick}' AND card_win = 1");
            
            if($momie_id != null)
            {
                game::$instance->notifyPlayer(
                    $momie_player,
                    'momieChange',
                    '',
                    array(
                        'trick' => $trick,
                        'momie_id' => $momie_id,
                        'momie_player' => $momie_player,
                        'type_last_card_win' => $type_last_card_win,
                        'typearg_last_card_win' => $typearg_last_card_win

                    )
                );


            }

            //DRUIDE

            if(game::$instance->getGameStateValue("druide_player") != 0)
            {
                game::$instance->addPending(game::$instance->getGameStateValue("druide_player"), "Druide");
            }


            //PE BLEU

            if(game::$instance->getGameStateValue("pe_bleu_player") != 0)
            {
                game::$instance->addPending(game::$instance->getGameStateValue("pe_bleu_player"), "PeBlue");
            }

            //SORCIER

            if(game::$instance->getGameStateValue("sorcier_player") != 0)
            {
                game::$instance->addPending(game::$instance->getGameStateValue("sorcier_player"), "Sorcier");
            }
            

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

        // calcul score du round
        game::$instance->calculScore();


        $round = game::$instance->getGameStateValue('no_round');

        $count_players_max_score = count(self::getObjectListFromDB(
            "SELECT player_id AS id
            FROM player
            WHERE player_score = (SELECT MAX(player_score) FROM player)",
            true
        ));


        if (($round < 7)||($round >=7 && $count_players_max_score >=2))
        {
            //// INIT NEW ROUND + INIT TRICK ////

            // MAJ ROUND
            game::$instance->incGameStateValue('no_round', 1);
            $new_round = game::$instance->getGameStateValue('no_round');
            game::$instance->setGameStateValue('no_trick', 1);
            game::$instance->setGameStateValue("druide_player", 0);
            game::$instance->setGameStateValue("druide_active", 0);
            game::$instance->setGameStateValue("pe_bleu_player", 0);
            game::$instance->setGameStateValue("pe_bleu_active", 0);

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

            if(game::$instance->gamestate->table_globals[100] != 3)
            {
                while (in_array($rand, $all_rand))
                {
                    $rand = bga_rand(1, 14);
                }
            }

            if(game::$instance->gamestate->table_globals[100] == 3)
            {
                while (in_array($rand, $all_rand) && $rand == 4)
                {
                    $rand = bga_rand(1, 14);
                }
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

                game::$instance->notifyAllPlayers(
                    'initPannel',
                    '',
                    array(

                        'player' => $player,

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
            game::$instance->setGameStateValue("first_player_quest", $first_player_quest);

            $first_player_play = intval(self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type=3 AND card_type_arg = 5"));
            game::$instance->setGameStateValue("first_player_play", $first_player_play);

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

        else
        {
            game::$instance->addPending($this->player_id, "EndGame");
        }
         
        
    }


    function argEndGame($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('End of Game');
        $ret['titleyou'] = clienttranslate('End of Game');

        $ret['buttons'][] ='continue';
        
        return $ret;
    }

    function EndGame($parg1, $parg2, $varg1, $varg2)
    {
        game::$instance->notifyAllPlayers( 'simplePause', '', [ 'time' => 1000] ); 

        game::$instance->gamestate->nextState('end');
    }

    ////////////////////////// DRUIDE /////////////////////

    function argDruide($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('');
        $ret['titleyou'] = clienttranslate('');

        
        return $ret;
    }

    function Druide($parg1, $parg2, $varg1, $varg2)
    {
        game::$instance->setGameStateValue("druide_active", $this->player_id);

        $players = self::getObjectListFromDB( "SELECT player_id id, player_name name, player_color color FROM player WHERE player_id != '{$this->player_id}'" );
        $player = $this->player_id;


        game::$instance->notifyPlayer(
                $player,
                'showPlayersModal',
                '',
                array(

                   'players'=> $players,
                   'player' => $player, 

                )
        );


        game::$instance->addPending($this->player_id, "DruideStep2");
    }


    function argDruideStep2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must exchange their hand with that of another player');
        $ret['titleyou'] = clienttranslate('${you} must exchange your hand with that of another player');

        $players = self::getObjectListFromDB( "SELECT player_id FROM player", true );
        foreach($players as $player)
        {
            if( $player != $this->player_id)
            {
                $ret["selectable"][] = 'modal_player_'. $player;
            }
        }

        return $ret;
    }

    function DruideStep2($parg1, $parg2, $varg1, $varg2)
    {
        $explode = explode('_', $varg1);
        $nextplayer = $explode[2];
        $player = $this->player_id;

        $cards_before_1 = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_location_arg='{$this->player_id}'" );
        $cards_before_2 = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_location_arg='{$nextplayer}'" );

        game::$instance->notifyPlayer(
                $player,
                'removeCards',
                '',
                array(

                    'cards' => $cards_before_1,

                )
        );

        game::$instance->notifyPlayer(
                $nextplayer,
                'removeCards',
                '',
                array(

                    'cards' => $cards_before_2,

                )
        );

        
        self::DbQuery("UPDATE cards set card_location_arg = 1234 WHERE card_location ='hand' AND card_location_arg='{$this->player_id}'");
        self::DbQuery("UPDATE cards set card_location_arg = $player WHERE card_location ='hand' AND card_location_arg='{$nextplayer}'");
        self::DbQuery("UPDATE cards set card_location_arg = $nextplayer WHERE card_location ='hand' AND card_location_arg=1234");

        $cards_after_1 = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_location_arg='{$this->player_id}'" );
        $cards_after_2 = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_location_arg='{$nextplayer}'" );


        game::$instance->notifyPlayer(
                $player,
                'drawCards',
                '',
                array(

                    'cards' => $cards_after_1,

                )
            );

        game::$instance->notifyPlayer(
                $nextplayer,
                'drawCards',
                '',
                array(

                    'cards' => $cards_after_2,

                )
            );


        
        game::$instance->notifyPlayer(
                $player,
                'removePlayersModal',
                '',
                array(

                    

                )
        ); 
        
        $player_name_opponent = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id={$nextplayer}");
        $player_color_opponent = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id={$nextplayer}");

        game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} exchanges their hand with ${opponent}'),
                array(
                    'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                        'args'=> ['opponent_name' => $player_name_opponent, 'color'=>$player_color_opponent]
                                    ],

                    'player_name' => $this->player_name,
                    
                )
            );


        // INIT DRUIDE
        game::$instance->setGameStateValue("druide_player", 0);
        game::$instance->setGameStateValue("druide_active", 0);

        game::$instance->giveExtraTime($this->player_id);

    }



     ////////////////////////// PE BLEU /////////////////////

    function argPeBlue($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('');
        $ret['titleyou'] = clienttranslate('');

      
        return $ret;
    }

    function PeBlue($parg1, $parg2, $varg1, $varg2)
    {
        game::$instance->setGameStateValue("pe_bleu_active", $this->player_id);

         game::$instance->notifyPlayer(
                $this->player_id,
                'showFlecheModal',
                '',
                array(


                )
        );

        game::$instance->addPending($this->player_id, "PeBlueStep2");

    }

    function argPeBlueStep2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must choose which neighbor each player will pass their hand to');
        $ret['titleyou'] = clienttranslate('${you} must choose which neighbor each player will pass their hand to');

        $ret["selectable"][] = 'container_fleche_1';
        $ret["selectable"][] = 'container_fleche_2';
        
        return $ret;
    }

    function PeBlueStep2($parg1, $parg2, $varg1, $varg2)
    {
        $explode = explode('_', $varg1);
        $players = self::getObjectListFromDB( "SELECT player_id name FROM player", true );
        
        foreach($players as $player)
        {
            $cards_before = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_location_arg='{$player}'" );
            
            game::$instance->notifyPlayer(
            $player,
            'removeCards',
            '',
            array(

                'cards' => $cards_before,

            )
            );
        }

        $nb_players = count($players);

        $player1 = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no = 1");
        $player2 = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no = 2");
        $player3 = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no = 3");
        $player4 = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no = 4");
        $player5 = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no = 5");
        
        self::DbQuery("UPDATE cards set card_location_arg = 1 WHERE card_location ='hand' AND card_location_arg='{$player1}'");
        self::DbQuery("UPDATE cards set card_location_arg = 2 WHERE card_location ='hand' AND card_location_arg='{$player2}'");
        self::DbQuery("UPDATE cards set card_location_arg = 3 WHERE card_location ='hand' AND card_location_arg='{$player3}'");

        if($nb_players == 4)
        {
            self::DbQuery("UPDATE cards set card_location_arg = 4 WHERE card_location ='hand' AND card_location_arg='{$player4}'");
        }

        if($nb_players == 5)
        {
            self::DbQuery("UPDATE cards set card_location_arg = 5 WHERE card_location ='hand' AND card_location_arg='{$player5}'");
        }




        if($explode[2] == 1)
        {  
            if($nb_players == 3)
            {
                self::DbQuery("UPDATE cards set card_location_arg = $player2 WHERE card_location ='hand' AND card_location_arg= 1");
                self::DbQuery("UPDATE cards set card_location_arg = $player3 WHERE card_location ='hand' AND card_location_arg= 2");
                self::DbQuery("UPDATE cards set card_location_arg = $player1 WHERE card_location ='hand' AND card_location_arg= 3");
            }

            if($nb_players == 4)
            {
                self::DbQuery("UPDATE cards set card_location_arg = $player2 WHERE card_location ='hand' AND card_location_arg= 1");
                self::DbQuery("UPDATE cards set card_location_arg = $player3 WHERE card_location ='hand' AND card_location_arg= 2");
                self::DbQuery("UPDATE cards set card_location_arg = $player4 WHERE card_location ='hand' AND card_location_arg= 3");
                self::DbQuery("UPDATE cards set card_location_arg = $player1 WHERE card_location ='hand' AND card_location_arg= 4");
            }

            if($nb_players == 5)
            {
                self::DbQuery("UPDATE cards set card_location_arg = $player2 WHERE card_location ='hand' AND card_location_arg= 1");
                self::DbQuery("UPDATE cards set card_location_arg = $player3 WHERE card_location ='hand' AND card_location_arg= 2");
                self::DbQuery("UPDATE cards set card_location_arg = $player4 WHERE card_location ='hand' AND card_location_arg= 3");
                self::DbQuery("UPDATE cards set card_location_arg = $player5 WHERE card_location ='hand' AND card_location_arg= 4");
                self::DbQuery("UPDATE cards set card_location_arg = $player1 WHERE card_location ='hand' AND card_location_arg= 5");
            }


            game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} with ${log}: All players pass their hand to the NEXT player'),
                array(
                    
                    'player_name' => $this->player_name,
                    'log' => game::$instance->getLogIcon('2_11'),
                )
            );
          
        
            
        }

        if($explode[2] == 2)
        {

            if($nb_players == 3)
            {
                self::DbQuery("UPDATE cards set card_location_arg = $player3 WHERE card_location ='hand' AND card_location_arg= 1");
                self::DbQuery("UPDATE cards set card_location_arg = $player1 WHERE card_location ='hand' AND card_location_arg= 2");
                self::DbQuery("UPDATE cards set card_location_arg = $player2 WHERE card_location ='hand' AND card_location_arg= 3");
            }

            if($nb_players == 4)
            {
                self::DbQuery("UPDATE cards set card_location_arg = $player4 WHERE card_location ='hand' AND card_location_arg= 1");
                self::DbQuery("UPDATE cards set card_location_arg = $player1 WHERE card_location ='hand' AND card_location_arg= 2");
                self::DbQuery("UPDATE cards set card_location_arg = $player2 WHERE card_location ='hand' AND card_location_arg= 3");
                self::DbQuery("UPDATE cards set card_location_arg = $player3 WHERE card_location ='hand' AND card_location_arg= 4");
            }

            if($nb_players == 5)
            {
                self::DbQuery("UPDATE cards set card_location_arg = $player5 WHERE card_location ='hand' AND card_location_arg= 1");
                self::DbQuery("UPDATE cards set card_location_arg = $player1 WHERE card_location ='hand' AND card_location_arg= 2");
                self::DbQuery("UPDATE cards set card_location_arg = $player2 WHERE card_location ='hand' AND card_location_arg= 3");
                self::DbQuery("UPDATE cards set card_location_arg = $player3 WHERE card_location ='hand' AND card_location_arg= 4");
                self::DbQuery("UPDATE cards set card_location_arg = $player4 WHERE card_location ='hand' AND card_location_arg= 5");
            }

            game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} with ${log}: All players pass their hand to the PREVIOUS player'),
                array(
                    
                    'player_name' => $this->player_name,
                    'log' => game::$instance->getLogIcon('2_11'),
                )
            );


            
        }

        foreach($players as $player)
        {
            $cards_after = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_location_arg='{$player}'" );
            
            game::$instance->notifyPlayer(
            $player,
            'drawCards',
            '',
            array(

                'cards' => $cards_after,

            )
            );
        }

         game::$instance->notifyPlayer(
                $this->player_id,
                'removeFlecheModal',
                '',
                array(

                    

                )
        );


        // INIT PE BLEU
        game::$instance->setGameStateValue("pe_bleu_player", 0);
        game::$instance->setGameStateValue("pe_bleu_active", 0);

        game::$instance->giveExtraTime($this->player_id);
        

    }



    ////////////////////////// PE ROUGE /////////////////////

    function argPeRed($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('');
        $ret['titleyou'] = clienttranslate('');

        
        return $ret;
    }

    function PeRed($parg1, $parg2, $varg1, $varg2)
    {
        game::$instance->setGameStateValue("pe_rouge_active", $this->player_id);

        $players = self::getObjectListFromDB( "SELECT player_id id, player_name name, player_color color FROM player WHERE player_id != '{$this->player_id}'" );
        $player = $this->player_id;


        game::$instance->notifyPlayer(
                $player,
                'showPlayersModal',
                '',
                array(

                   'players'=> $players,
                   'player' => $player, 

                )
        );

        game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} has ${log} and must exchange 2 cards with another player'),
                array(
                    'player_name' => $this->player_name,
                    'log' => game::$instance->getLogIcon('3_11'),

                )
            );


        game::$instance->addPending($this->player_id, "PeRedStep2");
    }


    function argPeRedStep2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must must exchange 2 cards with another player');
        $ret['titleyou'] = clienttranslate('${you} must choose a player from whom to take 2 cards');

        $players = self::getObjectListFromDB( "SELECT player_id FROM player", true );
        foreach($players as $player)
        {
            if( $player != $this->player_id)
            {
                $ret["selectable"][] = 'modal_player_'. $player;
            }
        }

        return $ret;
    }

    function PeRedStep2($parg1, $parg2, $varg1, $varg2)
    {
        $max_cards = 0;
        $nbreplayers = count(self::getObjectListFromDB( "SELECT player_id FROM player", true ));

        if($nbreplayers == 3)
        {
            $max_cards = 13;
        }
        if($nbreplayers == 4)
        {
            $max_cards = 10;
        }
        if($nbreplayers == 5)
        {
            $max_cards = 8;
        }
        
        $rand1 = bga_rand(1, $max_cards);
        $rand2 = bga_rand(1, $max_cards);

        while($rand2 == $rand1)
        {
            $rand2 = bga_rand(1, $max_cards);
        }


        $explode = explode('_', $varg1);
        $nextplayer = $explode[2];
        $player = $this->player_id;

        $cards = [];

        $cards_id_before = self::getObjectListFromDB( "SELECT card_id FROM cards WHERE card_location ='hand' AND card_location_arg='{$nextplayer}'", true );
        $cards_before = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_location_arg='{$nextplayer}'" );
        
        $cards[] = $cards_before[$cards_id_before[$rand1-1]];
        $cards[] = $cards_before[$cards_id_before[$rand2-1]];

        game::$instance->DbQuery("UPDATE cards set card_location_arg = $player WHERE card_id = '{$cards_id_before[$rand1-1]}'");
        game::$instance->DbQuery("UPDATE cards set card_location_arg = $player WHERE card_id = '{$cards_id_before[$rand2-1]}'");


       
        game::$instance->notifyPlayer(
                $nextplayer,
                'removeCards',
                '',
                array(

                    'cards' => $cards,

                )
        );

        game::$instance->notifyPlayer(
                $player,
                'drawCards',
                '',
                array(

                    'cards' => $cards,

                )
            );

        $player_name_opponent = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id={$nextplayer}");
        $player_color_opponent = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id={$nextplayer}");

        game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} takes 2 cards from ${opponent}\'s hand'),
                array(
                    'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                        'args'=> ['opponent_name' => $player_name_opponent, 'color'=>$player_color_opponent]
                                    ],

                    'player_name' => $this->player_name,
                    
                )
            );

        game::$instance->notifyPlayer(
                $player,
                'removePlayersModal',
                '',
                array(

                    

                )
        ); 

        
        // INIT PE ROUGE
        game::$instance->setGameStateValue("pe_rouge_player", 0);
        game::$instance->setGameStateValue("pe_rouge_active", 0);

        game::$instance->addPending($this->player_id, "PeRedStep3", $nextplayer);

    }


    function argPeRedStep3($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret["selectablemulti"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('${actplayer} must exchange 2 cards with another player');
        
        $nextplayer_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id={$parg1}");
        $nextplayer_color = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id={$parg1}");
        $ret['opponent'] = '<span style="color: #' . $nextplayer_color . ';">' . $nextplayer_name . '</span>';

        $ret['titleyou'] = clienttranslate('${you} must choose 2 cards to pass to #opponent#');

        $all_cards = self::getObjectListFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location = 'hand' AND card_location_arg = '{$this->player_id}'" );
        
        foreach ($all_cards as $card) 
        {
            $ret["selectablemulti"][] = 'my_cards_item_' . $card['id'];

        }

        $ret['buttons'][] = 'validatemulti';

        return $ret;
    }

    function PeRedStep3($parg1, $parg2, $varg1, $varg2)
    {

        $explode = explode('_', $varg1);
        $player = $this->player_id;
        $nextplayer = $parg1;

        $cards_before1 = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_id='{$explode[0]}'" );
        $cards_before2 = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_id='{$explode[1]}'" );
        
        $cards = [];
        $cards[] = $cards_before1[$explode[0]];
        $cards[] = $cards_before2[$explode[1]];

        game::$instance->DbQuery("UPDATE cards set card_location_arg = $nextplayer WHERE card_id = '{$explode[0]}'");
        game::$instance->DbQuery("UPDATE cards set card_location_arg = $nextplayer WHERE card_id = '{$explode[1]}'");

        game::$instance->notifyPlayer(
                $player,
                'removeCards',
                '',
                array(

                    'cards' => $cards,

                )
        );

        game::$instance->notifyPlayer(
                $nextplayer,
                'drawCards',
                '',
                array(

                    'cards' => $cards,

                )
            );

        $player_name_opponent = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id={$nextplayer}");
        $player_color_opponent = self::getUniqueValueFromDB("SELECT player_color FROM player WHERE player_id={$nextplayer}");

        game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} returns 2 cards to ${opponent}'),
                array(
                    'opponent' =>    [   'log' => '<b style="color: #${color};">${opponent_name}</b>',
                                        'args'=> ['opponent_name' => $player_name_opponent, 'color'=>$player_color_opponent]
                                    ],

                    'player_name' => $this->player_name,
                    
                )
            );

        game::$instance->giveExtraTime($this->player_id);
        
    }

    ////////////////////////// SORCIER /////////////////////

    function argSorcier($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        $ret['title'] = clienttranslate('');
        $ret['titleyou'] = clienttranslate('');


        return $ret;
    }

    function Sorcier($parg1, $parg2, $varg1, $varg2)
    {
        game::$instance->notifyAllPlayers( 'simplePause', '', [ 'time' => 1000] ); 

        game::$instance->setGameStateValue("sorcier_active", $this->player_id);

        // NB TRICK ACTUEL
        $trick_no = game::$instance->getGameStateValue('no_trick');
        $previous_trick_no = $trick_no - 1;
        // NB ROUND ACTUEL
        $round = game::$instance->getGameStateValue('no_round');

        $ticks_max = 0;
        $nbreplayers = count(self::getObjectListFromDB( "SELECT player_id FROM player", true ));

        if($nbreplayers == 3)
        {
            $ticks_max = 13;
        }
        if($nbreplayers == 4)
        {
            $ticks_max = 10;
        }
        if($nbreplayers == 5)
        {
            $ticks_max = 8;
        }


        $previous_trick_cards = self::getCollectionFromDB( "SELECT card_id id, type type, type_arg type_arg, type_arg_2 type_arg_2 FROM tricks WHERE round ='{$round}' AND trick='{$previous_trick_no}'" );
        
        game::$instance->notifyPlayer(
                $this->player_id,
                'showPreviousTrick',
                '',
                array(

                   'cards'=> $previous_trick_cards,

                )
        );

        if($trick_no < $ticks_max)
        {
            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} has ${log} and can exchange it for a card from the previous trick'),
                    array(
                        'player_name' => $this->player_name,
                        'log' => game::$instance->getLogIcon('4_4'),

                    )
                );
        }

        if($trick_no == $ticks_max)
        {
            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} has ${log} and must exchange it for a card from the previous trick'),
                    array(
                        'player_name' => $this->player_name,
                        'log' => game::$instance->getLogIcon('4_4'),

                    )
                );
        }



        game::$instance->addPending($this->player_id, "SorcierStep2");
    }


    function argSorcierStep2($parg1, $parg2)
    {
        $ret = array();
        $ret["selectable"] = array();
        $ret["selected"] = array();
        $ret['buttons'] = array();
        

        // NB TRICK ACTUEL
        $trick_no = game::$instance->getGameStateValue('no_trick');
        $previous_trick_no = $trick_no - 1;
        // NB ROUND ACTUEL
        $round = game::$instance->getGameStateValue('no_round');

        $ticks_max = 0;
        $nbreplayers = count(self::getObjectListFromDB( "SELECT player_id FROM player", true ));

        if($nbreplayers == 3)
        {
            $ticks_max = 13;
        }
        if($nbreplayers == 4)
        {
            $ticks_max = 10;
        }
        if($nbreplayers == 5)
        {
            $ticks_max = 8;
        }

        if($trick_no < $ticks_max)
        {
            $ret['title'] = clienttranslate('${actplayer} can exchange the Sorcerer for a card from the previous trick');
            $ret['titleyou'] = clienttranslate('${you} can exchange the Sorcerer for a card from the previous trick or');
            $ret['buttons'][] = 'pass';
        }

        if($trick_no == $ticks_max)
        {
            $ret['title'] = clienttranslate('${actplayer} must exchange the Sorcerer for a card from the previous trick');
            $ret['titleyou'] = clienttranslate('${you} must exchange the Sorcerer for a card from the previous trick');
        }

        $cards = self::getObjectListFromDB( "SELECT card_id FROM tricks WHERE round ='{$round}' AND trick = '{$previous_trick_no}'", true );

        foreach($cards as $card)
        {
            $ret["selectable"][] = "previous_card_".$card;
        }



        

        return $ret;
    }

    function SorcierStep2($parg1, $parg2, $varg1, $varg2)
    {

        game::$instance->notifyPlayer(
                $this->player_id,
                'removePreviousTrick',
                '',
                array(

                )
        );

        if($varg1 == 'pass')
        {
            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} does not use ${log}'),
                    array(
                        'player_name' => $this->player_name,
                        'log' => game::$instance->getLogIcon('4_4'),

                    )
                );
            
            // INIT SORCIER ACTIVE
            game::$instance->setGameStateValue("sorcier_active", 0);
            game::$instance->giveExtraTime($this->player_id);

        }

        else {

            $player = $this->player_id;
            $explode = explode('_', $varg1);
            $card_id = $explode[2];
            $sorcier_id = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 4");

            // INVERSION DES CARTES EN BD
            self::DbQuery("UPDATE tricks set type = 4 WHERE card_id = '{$card_id}'");
            self::DbQuery("UPDATE tricks set type_arg = 4 WHERE card_id = '{$card_id}'");
            self::DbQuery("UPDATE tricks set card_id = $sorcier_id WHERE card_id = '{$card_id}'");
            
            self::DbQuery("UPDATE cards set card_location = 'hand' WHERE card_id = '{$card_id}'");
            self::DbQuery("UPDATE cards set card_location_arg = $player WHERE card_id = '{$card_id}'");
            self::DbQuery("UPDATE cards set card_location = 'discard' WHERE card_id = '{$sorcier_id}'");
            
            
            $card = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_id = '{$card_id}'" );
            $card_sorcier = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_type = 4 AND card_type_arg = 4" );

            game::$instance->notifyPlayer(
                $player,
                'removeCards',
                '',
                array(

                    'cards' => $card_sorcier,

                )
            );

            game::$instance->notifyPlayer(
                    $player,
                    'drawCards',
                    '',
                    array(

                        'cards' => $card,

                    )
            );

            $type = self::getUniqueValueFromDB("SELECT card_type FROM cards WHERE card_id='{$card_id}'");
            $type_arg = self::getUniqueValueFromDB("SELECT card_type_arg FROM cards WHERE card_id='{$card_id}'");
            $log = $type.'_'.$type_arg;
            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} uses ${log} and retrieves ${log2}'),
                    array(
                        'player_name' => $this->player_name,
                        'log' => game::$instance->getLogIcon('4_4'),
                        'log2' => game::$instance->getLogIcon($log),

                    )
            );

            // INIT SORCIER
            game::$instance->setGameStateValue("sorcier_player", 0);
            game::$instance->setGameStateValue("sorcier_active", 0);
            game::$instance->giveExtraTime($this->player_id);

        }

        


        

        
    }
    
    



















}