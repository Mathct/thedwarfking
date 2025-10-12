<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * thedwarfking implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */
declare(strict_types=1);

namespace Bga\Games\thedwarfking;

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");

include('Pending.php'); // ATTENTION


class Game extends \Table
{
    public static $instance = null; //ATTENTION

   
    public function __construct()
    {
        parent::__construct();

        require 'material.inc.php';

        // EXPERIMENTAL to avoid deadlocks.  This locks the global table early in the game constructor.
        $this->bSelectGlobalsForUpdate = true;


        $this->initGameStateLabels([

            "first_player_deal" => 10,
            "first_player_quest" => 11,
            "first_player_play" => 12,
            "no_round" => 13,
            "no_trick" => 14, //no trick pour le round actuel
            "color_request" => 15,

            "bricoleur" => 16,
           
        ]);  
        
        
        self::$instance = $this; // ATTENTION

        $this->cards = self::getNew("module.common.deck");
        $this->cards->init("cards");

        
    }


    protected function getGameName()
    {
        return "thedwarfking";
    }

/////////////////////////////////////////////////////////////////////////////////  
//       _____                        _____       _ _   _       _ _          _   _             
//      / ____|                      |_   _|     (_) | (_)     | (_)        | | (_)            
//     | |  __  __ _ _ __ ___   ___    | |  _ __  _| |_ _  __ _| |_ ______ _| |_ _  ___  _ __  
//     | | |_ |/ _` | '_ ` _ \ / _ \   | | | '_ \| | __| |/ _` | | |_  / _` | __| |/ _ \| '_ \ 
//     | |__| | (_| | | | | | |  __/  _| |_| | | | | |_| | (_| | | |/ / (_| | |_| | (_) | | | |
//      \_____|\__,_|_| |_| |_|\___| |_____|_| |_|_|\__|_|\__,_|_|_/___\__,_|\__|_|\___/|_| |_|
//                                                                                               
/////////////////////////////////////////////////////////////////////////////////    


    protected function setupNewGame($players, $options = [])
    {
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);
        }

        // Create players based on generic information.
        //
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        static::DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
                implode(",", $query_values)
            )
        );

        $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        
        // Init global values with their initial values.
        $this->setGameStateInitialValue("no_round", 1);
        $this->setGameStateInitialValue("no_trick", 1);
        $this->setGameStateInitialValue("color_request", 0);

        $this->setGameStateInitialValue("bricoleur", 0);

        // STATS
        

        // INIT GAME

        $nbreplayers = count(self::getObjectListFromDB( "SELECT player_id FROM player", true ));

        // DECK
        $colors = [1, 2, 3];
        $cards = [];
        foreach($colors as $color)
        {
            if (($nbreplayers == 3)&&($color == 1))
            {
                for ($value = 3; $value <= 10; $value++) {
                $cards[] = array("type" => $color, "type_arg" => $value, "nbr" => 1);
                }

            }

            else{

                for ($value = 2; $value <= 10; $value++) {
                $cards[] = array("type" => $color, "type_arg" => $value, "nbr" => 1);
            }

            }
            

            for ($value = 12; $value <= 15; $value++) {
                $cards[] = array("type" => $color, "type_arg" => $value, "nbr" => 1);
            }

        }

        $cards[] = array("type" => 4, "type_arg" => 0, "nbr" => 1);
        
        $this->cards->createCards($cards, 'deck');
        $this->cards->shuffle('deck');

        //SPECAL CARD FIRST ROUND

        $rand = bga_rand(1, 14);

        if($rand>=1 && $rand<=5)
        {
            self::DbQuery("UPDATE cards set card_type_arg = $rand WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES (1, $rand, 4, $rand, 0)");
        }

        if($rand == 6)
        {
            self::DbQuery("UPDATE cards set card_type = 1, card_type_arg = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES (1, $rand, 1, 1, 0)");
        }

        if($rand == 7)
        {
            self::DbQuery("UPDATE cards set card_type = 1, card_type_arg = 11, card_type_arg_2 = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES (1, $rand, 1, 11, 1)");
        }

        if($rand==8)
        {
            self::DbQuery("UPDATE cards set card_type = 1, card_type_arg = 11, card_type_arg_2 = 2 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES (1, $rand, 1, 11, 2)");
        }

        if($rand == 9)
        {
            self::DbQuery("UPDATE cards set card_type = 2, card_type_arg = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES (1, $rand, 2, 1, 0)");
        }

         if($rand == 10)
        {
            self::DbQuery("UPDATE cards set card_type = 2, card_type_arg = 11, card_type_arg_2 = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES (1, $rand, 2, 11, 1)");
        }

        if($rand == 11)
        {
            self::DbQuery("UPDATE cards set card_type = 2, card_type_arg = 11, card_type_arg_2 = 2 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES (1, $rand, 2, 11, 2)");
        }

        if($rand == 12)
        {
            self::DbQuery("UPDATE cards set card_type = 3, card_type_arg = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES (1, $rand, 3, 1, 0)");
        }

         if($rand == 13)
        {
            self::DbQuery("UPDATE cards set card_type = 3, card_type_arg = 11, card_type_arg_2 = 1 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES (1, $rand, 3, 11, 1)");
        }

        if($rand == 14)
        {
            self::DbQuery("UPDATE cards set card_type = 3, card_type_arg = 11, card_type_arg_2 = 2 WHERE card_type = 4");
            self::DbQuery("INSERT INTO specialcard (round, rand, type, type_arg, type_arg_2) VALUES (1, $rand, 3, 11, 2)");
        }

        //QUEST FIRST ROUND

        $rand2 = bga_rand(1, 20);
        self::DbQuery("INSERT INTO quest (round, rand) VALUES (1, $rand2)");


        // distribution des cartes pour le first round

        if ($nbreplayers == 3)
        {           
           foreach ($players as $player_id => $player) {
                $this->cards->pickCards(13, 'deck', $player_id);
            }
        }

        if ($nbreplayers == 4)
        {
            foreach ($players as $player_id => $player) {
                $this->cards->pickCards(10, 'deck', $player_id);
            }
        }

        if ($nbreplayers == 5)
        {
            foreach ($players as $player_id => $player) {
                $this->cards->pickCards(8, 'deck', $player_id);
            }
            
        }

        // init global values first round

        $first_player_deal = self::getUniqueValueFromDB("SELECT player_id FROM player WHERE player_no=1");
        $this->setGameStateInitialValue("first_player_deal", $first_player_deal);

        $first_player_quest = intval(self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type=2 AND card_type_arg = 5"));
        $this->setGameStateInitialValue("first_player_quest", $first_player_quest);

        $first_player_play = intval(self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type=3 AND card_type_arg = 5"));
        $this->setGameStateInitialValue("first_player_play", $first_player_play);



        /************ Init Pending *****/
     
           
        // NOUVEL ORDRE PENDING
        $nextplayer = $first_player_play;
        $count_players = count(self::getObjectListFromDB("SELECT player_id id FROM player", true));
        for ($i = 1; $i <= $count_players; $i++) {
            game::$instance->addPendingFirst($nextplayer, "PlayCard");
            $nextplayer = game::$instance->getPlayerAfter($nextplayer);
        }

        $this->addPending($first_player_play, "FirstPlay");
        $this->addPending($first_player_quest, "Quest");
        $this->addPending($first_player_deal, "Deal");

    }

/////////////////////////////////////////////////////////////////////////////////  
//               _            _ _ _____        _            
//              | |     /\   | | |  __ \      | |           
//     __ _  ___| |_   /  \  | | | |  | | __ _| |_ __ _ ___ 
//    / _` |/ _ \ __| / /\ \ | | | |  | |/ _` | __/ _` / __|
//   | (_| |  __/ |_ / ____ \| | | |__| | (_| | || (_| \__ \
//    \__, |\___|\__/_/    \_\_|_|_____/ \__,_|\__\__,_|___/
//     __/ |                                                
//    |___/                                                 
/////////////////////////////////////////////////////////////////////////////////  

protected function getAllDatas()
{
    $result = [];

    // WARNING: We must only return information visible by the current player.
    $current_player_id = (int) $this->getCurrentPlayerId();

    // Get information about players.
    // NOTE: you can retrieve some extra field you added for "player" table in `dbmodel.sql` if you need it.
    $result["players"] = $this->getCollectionFromDb(
        "SELECT `player_id` `id`, `player_score` `score` FROM `player`"
    );

    // TODO: Gather all information about current game situation (visible by player $current_player_id).

    $result['no_round'] = $this->getGameStateValue('no_round');
    $result['no_trick'] = $this->getGameStateValue('no_trick');
    $result['first_player_play'] = $this->getGameStateValue('first_player_play');
    $result['my_hand'] = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_location_arg='{$current_player_id}'" );
    $result['table'] = $this->getCardsOnTableOrdered();
    $result['active_special_card'] = self::getObjectListFromDB( "SELECT type type, type_arg type_arg, type_arg_2 type_arg_2 FROM specialcard WHERE round = '{$result['no_round']}'" );
    $result['active_quest_card'] = self::getObjectListFromDB( "SELECT rand rand, validate validate FROM quest WHERE round = '{$result['no_round']}'" );


    return $result;
}


/////////////////////////////////////////////////////////////////////////////////  
//     _____                      _____                                   _             
//    / ____|                    |  __ \                                 (_)            
//   | |  __  __ _ _ __ ___   ___| |__) | __ ___   __ _ _ __ ___  ___ ___ _  ___  _ __  
//   | | |_ |/ _` | '_ ` _ \ / _ \  ___/ '__/ _ \ / _` | '__/ _ \/ __/ __| |/ _ \| '_ \ 
//   | |__| | (_| | | | | | |  __/ |   | | | (_) | (_| | | |  __/\__ \__ \ | (_) | | | |
//    \_____|\__,_|_| |_| |_|\___|_|   |_|  \___/ \__, |_|  \___||___/___/_|\___/|_| |_|
//                                                 __/ |                                
//                                                |___/                                 
/////////////////////////////////////////////////////////////////////////////////  

public function getGameProgression()
{
    // TODO: compute and return the game progression

    return 0;
}


/////////////////////////////////////////////////////////////////////////////////  
//     _    _ _   _ _ _ _            __                  _   _                 
//    | |  | | | (_) (_) |          / _|                | | (_)                
//    | |  | | |_ _| |_| |_ _   _  | |_ _   _ _ __   ___| |_ _  ___  _ __  ___ 
//    | |  | | __| | | | __| | | | |  _| | | | '_ \ / __| __| |/ _ \| '_ \/ __|
//    | |__| | |_| | | | |_| |_| | | | | |_| | | | | (__| |_| | (_) | | | \__ \
//     \____/ \__|_|_|_|\__|\__, | |_|  \__,_|_| |_|\___|\__|_|\___/|_| |_|___/
//                           __/ |                                             
//                          |___/                                              
/////////////////////////////////////////////////////////////////////////////////  

function addPending($player_id, $function, $arg = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL) {
    $sql = "INSERT INTO pending (player_id, function, arg, arg2, arg3, arg4) VALUES (".$player_id.", '".$function."', '".$arg."', '".$arg2."', '".$arg3."', '".$arg4."')";
    self::DbQuery( $sql );
}


function addPendingFirst($player_id, $function, $arg = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL) {
    $minid = self::getUniqueValueFromDB( "select min(id) from pending")-1;
    $sql = "INSERT INTO pending (id, player_id, function, arg, arg2) VALUES (".$minid.",".$player_id.", '".$function."', '".$arg."', '".$arg2."')";
    self::DbQuery( $sql );
}

function checkArgs($arg1)
    {
        $ret = self::argPlayerTurn();

        if(!in_array($arg1,$ret['selectable']) && !in_array($arg1,$ret['buttons']))
        {
            throw new \BgaSystemException("Not a valid selection");
        }
        
    }

public function getCardsOnTableOrdered()
    {
        $current = $this->getGameStateValue('first_player_play');
        $current_no = self::getUniqueValueFromDB("SELECT player_no FROM player WHERE player_id = $current");

        $sql = "SELECT player_id FROM player ORDER BY (player_no >= $current_no) DESC, player_no ASC";
        $ordered_ids = $this->getObjectListFromDB($sql, true);
        // ⬅️ Retourne un array simple [2037568, 2037569, ...]

        $tableCards = $this->cards->getCardsInLocation('table');

        // Indexe les cartes par player_id
        $cards_by_player = array_column($tableCards, null, 'location_arg');

        // Trie les cartes selon l’ordre
        $ordered_cards = [];
        foreach ($ordered_ids as $player_id) {
            $player_id = (int) $player_id;
            if (isset($cards_by_player[$player_id])) {
                $ordered_cards[] = $cards_by_player[$player_id];
                
            }
        }
        
        return $ordered_cards;
        
    }

function winnerOfTurn()
    {
        $round = game::$instance->getGameStateValue("no_round");
        $trick = game::$instance->getGameStateValue("no_trick");


        $color_request = game::$instance->getGameStateValue("color_request");
        $cards_played = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location = 'table'");
        
        // VARIABLES
        $escalibur = 0;
        $pe_green = 0;
        $bouffon = 0;
        

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




        
        // CALCUL WIN
        $player_win = 0;
        $maxValue = 0;
        foreach ($cards_played as $card) {

            //ESCALIBUR
            if ($card['type'] == 4 && $card['type_arg'] == 5)
            {
                $player_win = $card['location_arg'];
                $escalibur = 1;
                break;
            }

            if ($card['type'] == $color_request) {
                if ($card['type_arg'] > $maxValue) {
                    $maxValue = $card['type_arg'];
                    $player_win = $card['location_arg'];
                    
                }
            }


            // PORTE ETENDARD VERT (11 1)
            if ($card['type'] == 1 && $card['type_arg'] == 11 && $card['type_arg_2'] == 1)
            {
                $pe_green = 1;
                
            }

            // BRICOLEUR (11 2)
            if ($card['type'] == 1 && $card['type_arg'] == 11 && $card['type_arg_2'] == 2 && $trick != $ticks_max)
            {
                game::$instance->setGameStateValue("bricoleur", 1);
                
            }

            // BOUFFON
            if ($card['type'] == 2 && $card['type_arg'] == 1 && $trick == $ticks_max)
            {
                $bouffon = $card['location_arg'];
                
            }


            
        }

        
        $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$player_win}'");

        /////////////////////////////////// END OF TURN ////////////////////////////
        // MESSAGE ET ENDTURN WIN
        if($escalibur == 0)
        {
            game::$instance->notifyAllPlayers(
                    'endTurn',
                    clienttranslate('${player_name} wins the trick and begins the next trick'),
                    array(
                        'winner_id' => $player_win,
                        'player_name' => $winner_name,
                    )
            );
        }

        if($escalibur == 1)
        {
            game::$instance->notifyAllPlayers(
                    'endTurn',
                    clienttranslate('${player_name} wins the trick and begins the next trick thanks to Escalibur'),
                    array(
                        'winner_id' => $player_win,
                        'player_name' => $winner_name,
                    )
            );
        }
        ////////////////////////////////////////////////////////////////////////////


        // BONUS PORTE ETENDARD VERT (11 1)
        if($pe_green == 1)
        {
             game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} wins 3 pv thanks to 11_vert'),
                    array(
                        'player_name' => $winner_name,
                    )
            );

            game::$instance->DbQuery("UPDATE bonus set bonus = 3 WHERE player_id = '{$player_win}' AND round = '{$round}'");

        }

        // BONUS BRICOLEUR (11 2)
        $bricoleur = game::$instance->getGameStateValue("bricoleur");
        if($bricoleur == 2)
        {
             game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} wins 3 pv thanks to bricoleur joué au round precedent'),
                    array(
                        'player_name' => $winner_name,
                    )
            );

            game::$instance->DbQuery("UPDATE bonus set bonus = 3 WHERE player_id = '{$player_win}' AND round = '{$round}'");
            game::$instance->setGameStateValue("bricoleur", 0);

        }

        if($bricoleur == 1)
        {
            game::$instance->setGameStateValue("bricoleur", 2);
        }


        // BONUS BOUFFON
        if($bouffon != 0)
        {
            $bouffon_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$bouffon}'");
             game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} joue le bouffon sur le dernier pli et gagne 3 pv'),
                    array(
                        'player_name' => $bouffon_name,
                    )
            );

            game::$instance->DbQuery("UPDATE bonus set bonus = 3 WHERE player_id = '{$bouffon}' AND round = '{$round}'");

        }




        // VERIFIER SI 4 VERT A ETE REMPORTE ( => PROCHAIN DEALER)
        foreach($cards_played as $card_played)
        {
            if($card_played['type'] == 1 && $card_played['type_arg'] == 5)
            {
                game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} wins le 4 vert et sera le dealer du prochain round'),
                array(
                    'winner_id' => $player_win,
                    'player_name' => $winner_name,
                )
                );

                game::$instance->setGameStateValue("first_player_deal", $player_win);

            }



        }

        return $player_win;
    }



/// LOGS

function getLogSpecial($card)
{
    $type = $card[0]['type'];
    $type_arg = $card[0]['type_arg'];
    $type_arg_2 = $card[0]['type_arg_2'];

    if($type == 4)
    {
        $variableX = ($type_arg - 1)*(-100);
        $variableY = 0;
    }

    if($type == 1)
    {
        if($type_arg == 1)
        {
            $variableX = -500;
            $variableY = 0;

        }
        if(($type_arg == 11)&&($type_arg_2 == 1))
        {
            $variableX = -600;
            $variableY = 0;
            
        }
        if(($type_arg == 11)&&($type_arg_2 == 2))
        {
            $variableX = 0;
            $variableY = -100;
            
        }       
        
    }

    if($type == 2)
    {
        if($type_arg == 1)
        {
            $variableX = -100;
            $variableY = -100;

        }
        if(($type_arg == 11)&&($type_arg_2 == 1))
        {
            $variableX = -200;
            $variableY = -100;
            
        }
        if(($type_arg == 11)&&($type_arg_2 == 2))
        {
            $variableX = -300;
            $variableY = -100;
            
        } 
       
    }

    if($type == 3)
    {
        if($type_arg == 1)
        {
            $variableX = -400;
            $variableY = -100;

        }
        if(($type_arg == 11)&&($type_arg_2 == 1))
        {
            $variableX = -500;
            $variableY = -100;
            
        }
        if(($type_arg == 11)&&($type_arg_2 == 2))
        {
            $variableX = -600;
            $variableY = -100;
            
        } 
        
    }

    return "<div class='specialcardlog' title='' style='background-position-x: {$variableX}%; background-position-y: {$variableY}%;'></div>";
    
}


function getLogQuest($card)
{
    $type = $card[0]['rand'];
    $validate = $card[0]['validate'];
    
    if($type >=1 && $type <=10)
    {
        $variableX = -100*(intval($type) - 1);
        if($validate == 1)
        {
            $variableY = 0;
        }
        if($validate == 2)
        {
            $variableY = -100;
        }
        

    }

    if($type >=11 && $type <=20)
    {
        $variableX = -100*(intval($type) - 11);
        if($validate == 1)
        {
            $variableY = -200;
        }
        if($validate == 2)
        {
            $variableY = -300;
        }

    }

   
    return "<div class='questcardlog' title='' style='background-position-x: {$variableX}%; background-position-y: {$variableY}%;'></div>";
    
}




///////////////////////////////////////////////////////////////////////////////// 
//     _____  _                                    _   _                 
//    |  __ \| |                                  | | (_)                
//    | |__) | | __ _ _   _  ___ _ __    __ _  ___| |_ _  ___  _ __  ___ 
//    |  ___/| |/ _` | | | |/ _ \ '__|  / _` |/ __| __| |/ _ \| '_ \/ __|
//    | |    | | (_| | |_| |  __/ |    | (_| | (__| |_| | (_) | | | \__ \
//    |_|    |_|\__,_|\__, |\___|_|     \__,_|\___|\__|_|\___/|_| |_|___/
//                     __/ |                                             
//                    |___/                                              
/////////////////////////////////////////////////////////////////////////////////


    public function actSelect(string $arg1)
    {
    
        self::checkArgs($arg1);        
        
        $pending =  self::getObjectFromDB( "SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from pending where id=".$pending['id']);
        $this->gamestate->nextState( 'next');
        
    }

    public function actButton(string $arg1)
    {

        self::checkArgs($arg1);       
        
        $pending =  self::getObjectFromDB( "SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from pending where id=".$pending['id']);
        $this->gamestate->nextState( 'next');
        
    }

///////////////////////////////////////////////////////////////////////////////// 
//     _____                             _        _                                                    _       
//    / ____|                           | |      | |                                                  | |      
//    | |  __  __ _ _ __ ___   ___   ___| |_ __ _| |_ ___    __ _ _ __ __ _ _   _ _ __ ___   ___ _ __ | |_ ___ 
//    | | |_ |/ _` | '_ ` _ \ / _ \ / __| __/ _` | __/ _ \  / _` | '__/ _` | | | | '_ ` _ \ / _ \ '_ \| __/ __|
//    | |__| | (_| | | | | | |  __/ \__ \ || (_| | ||  __/ | (_| | | | (_| | |_| | | | | | |  __/ | | | |_\__ \
//     \_____|\__,_|_| |_| |_|\___| |___/\__\__,_|\__\___|  \__,_|_|  \__, |\__,_|_| |_| |_|\___|_| |_|\__|___/
//                                                                    __/ |                                   
//                                                                   |___/                                    
///////////////////////////////////////////////////////////////////////////////// 


    public function argPlayerTurn()
    {
        $pending =  self::getObjectFromDB( "SELECT* FROM pending order by id desc limit 1");
        $arg = $this->callPending($pending, false);
    
        return $arg;
    }


///////////////////////////////////////////////////////////////////////////////// 
//      _____                            _        _                    _   _                 
//     / ____|                          | |      | |                  | | (_)                
//    | |  __  __ _ _ __ ___   ___   ___| |_ __ _| |_ ___    __ _  ___| |_ _  ___  _ __  ___ 
//    | | |_ |/ _` | '_ ` _ \ / _ \ / __| __/ _` | __/ _ \  / _` |/ __| __| |/ _ \| '_ \/ __|
//    | |__| | (_| | | | | | |  __/ \__ \ || (_| | ||  __/ | (_| | (__| |_| | (_) | | | \__ \
//     \_____|\__,_|_| |_| |_|\___| |___/\__\__,_|\__\___|  \__,_|\___|\__|_|\___/|_| |_|___/
//                                                                                       
/////////////////////////////////////////////////////////////////////////////////     


public function callPending($pending, $execute, $arg1 = null, $arg2 = null)
{
    
        $obj = $this;
        if($pending['player_id'] != null)
        {
            $obj = new Pending($pending['player_id']);
        }
        
        $fname ="";
        if(!$execute)
        {
            $fname .= "arg";
        }
        $fname .= $pending['function'];
        
        $ret = null;
        if(method_exists($obj, $fname))
        {
            $ret = $obj->$fname($pending['arg'], $pending['arg2'], $arg1, $arg2);
        }
    
    return $ret;
}


public function stPending() {
   
   $pending =  self::getObjectFromDB( "SELECT * FROM pending order by id desc limit 1");
   if($pending == null)
   {
        //$this->endGame();
        $this->gamestate->nextState( 'end' ); 
   }
   else
   {
       $args = $this->callPending($pending, false);

       
       if($pending['player_id'] != self::getActivePlayerId())
            {          
               

                //change active player      
                $this->gamestate->changeActivePlayer( $pending['player_id']);    
                $this->gamestate->nextState( 'same' );
            }
              
       else if($args == null || (count($args['selectable']) == 0 && count($args['buttons']) == 0))
       {
           //no args required, execute
           $this->callPending($pending, true);
           self::DbQuery("delete from pending where id=".$pending['id']);
           $this->gamestate->nextState( 'same' );  
       }
       
       else
       {
           
           $this->gamestate->nextState( 'player' ); 
       }            
   }
   
}

///////////////////////////////////////////////////////////////////////////////// 
//     _____  ____                                    _      
//    |  __ \|  _ \                                  | |     
//    | |  | | |_) |  _   _ _ __   __ _ _ __ __ _  __| | ___ 
//    | |  | |  _ <  | | | | '_ \ / _` | '__/ _` |/ _` |/ _ \
//    | |__| | |_) | | |_| | |_) | (_| | | | (_| | (_| |  __/
//    |_____/|____/   \__,_| .__/ \__, |_|  \__,_|\__,_|\___|
//                         | |     __/ |                     
//                         |_|    |___/                      
/////////////////////////////////////////////////////////////////////////////////  


    public function upgradeTableDb($from_version)
    {

    }


    

/////////////////////////////////////////////////////////////////////////////////
//    ______               _     _      
//   |___  /              | |   (_)     
//      / / ___  _ __ ___ | |__  _  ___ 
//     / / / _ \| '_ ` _ \| '_ \| |/ _ \
//    / /_| (_) | | | | | | |_) | |  __/
//   /_____\___/|_| |_| |_|_.__/|_|\___|
//                                   
/////////////////////////////////////////////////////////////////////////////////     

    protected function zombieTurn(array $state, int $active_player): void
    {
        $state_name = $state["name"];

        if ($state["type"] === "activeplayer") {
            switch ($state_name) {
                default:
                {
                    $player_id = $this->getActivePlayerId();
    	            self::DbQuery("delete from pending where player_id = {$player_id}");
                    $this->gamestate->nextState("end");
                    break;
                }
            }

            return;
        }

        // Make sure player is in a non-blocking status for role turn.
        if ($state["type"] === "multipleactiveplayer") {
            $this->gamestate->setPlayerNonMultiactive($active_player, '');
            $this->gamestate->nextState("end");
            return;
        }

        throw new \feException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }
}
