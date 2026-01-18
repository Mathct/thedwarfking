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

            //"game_mode" => 100,

            "first_player_deal" => 10,
            "first_player_quest" => 11,
            "first_player_play" => 12,
            "no_round" => 13,
            "no_trick" => 14, //no trick pour le round actuel
            "color_request" => 15,

            "bricoleur" => 16,
            "enchanteur" => 17, //id de celui remporte l'enchanteur... double les points
            "last_card_play" => 18, // id pour le clone
            "clone_play" => 19, 
            "druide_player" => 20,
            "druide_active" => 21,
            "pe_bleu_player" => 22,
            "pe_bleu_active" => 23,
            "pe_rouge_player" => 24,
            "pe_rouge_active" => 25,
            "sorcier_player" => 26,
            "sorcier_active" => 27,

            "end_game" => 28,
           
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
        $this->setGameStateInitialValue("enchanteur", 0);
        $this->setGameStateInitialValue("last_card_play", 0);
        $this->setGameStateInitialValue("clone_play", 0);
        $this->setGameStateInitialValue("druide_player", 0);
        $this->setGameStateInitialValue("druide_active", 0);
        $this->setGameStateInitialValue("pe_bleu_player", 0);
        $this->setGameStateInitialValue("pe_bleu_active", 0);
        $this->setGameStateInitialValue("pe_rouge_player", 0);
        $this->setGameStateInitialValue("pe_rouge_active", 0);
        $this->setGameStateInitialValue("sorcier_player", 0);
        $this->setGameStateInitialValue("sorcier_active", 0);

        $this->setGameStateInitialValue("end_game", 0);

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

        //FORCE CARD SPECIAL FOR DEV
        //$rand = 3;

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

    $round = $this->getGameStateValue('no_round');
    $trick = $this->getGameStateValue('no_trick');
    $previous_trick = $trick - 1;

    $result['no_round'] = $round;
    $result['no_trick'] = $trick;
    $result['first_player_play'] = $this->getGameStateValue('first_player_play');
    $result['my_hand'] = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_location_arg='{$current_player_id}'" );
    $result['table'] = $this->getCardsOnTableOrdered();
    $result['active_special_card'] = self::getObjectListFromDB( "SELECT type type, type_arg type_arg, type_arg_2 type_arg_2 FROM specialcard WHERE round = '{$result['no_round']}'" );
    $result['active_quest_card'] = self::getObjectListFromDB( "SELECT rand rand, validate validate FROM quest WHERE round = '{$result['no_round']}'" );

    //momie
    $result['momie_id'] = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 2 AND card_location = 'hand'");
    $result['momie_id_table'] = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 2 AND card_location = 'table'");
    $result['momie_player'] = self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type = 4 AND card_type_arg = 2 AND card_location = 'hand'");
    $result['type_last_card_win'] = self::getUniqueValueFromDB("SELECT type FROM tricks WHERE round = '{$round}' AND trick = '{$previous_trick}' AND card_win = 1");
    $result['typearg_last_card_win'] = self::getUniqueValueFromDB("SELECT type_arg FROM tricks WHERE round = '{$round}' AND trick = '{$previous_trick}' AND card_win = 1");

    //druide
    $result['druide_player'] = $this->getGameStateValue('druide_active');

    //pe_bleu
    $result['pe_blue_player'] = $this->getGameStateValue('pe_bleu_active');

    //pe_rouge
    $result['pe_rouge_player'] = $this->getGameStateValue('pe_rouge_active');

    //sorcier
    $result['sorcier_player'] = $this->getGameStateValue('sorcier_active');
    $result['previous_trick_cards'] = self::getCollectionFromDB( "SELECT card_id id, type type, type_arg type_arg, type_arg_2 type_arg_2 FROM tricks WHERE round ='{$round}' AND trick='{$previous_trick}'" );

    //clone
    $result['clone_id'] = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 3 AND card_location = 'hand'");
    $result['clone_id_table'] = self::getUniqueValueFromDB("SELECT card_id FROM cards WHERE card_type = 4 AND card_type_arg = 3 AND card_location = 'table'");
    $result['clone_player'] = self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type = 4 AND card_type_arg = 3 AND card_location = 'hand'");
    $last_id = game::$instance->getGameStateValue("last_card_play");
    $result['type_last_card'] = self::getUniqueValueFromDB("SELECT card_type FROM cards WHERE card_id = '{$last_id}'");
    $result['typearg_last_card'] = self::getUniqueValueFromDB("SELECT card_type_arg FROM cards WHERE card_id = '{$last_id}'");

    //all cards for tooltips
    $result['all_cards'] = self::getObjectListFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards" );
    $result['tooltips_cards'] = $this->_CARDS;
    $result['tooltips_quests'] = $this->_QUESTS;

    //pannel last trick win
    $players = self::getObjectListFromDB( "SELECT player_id FROM player", true );
    foreach ($players as $player)
    {
        $result['tricks_win'][$player] = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));

        if ($result['tricks_win'][$player] > 0)
        {
            $list_tricks = self::getObjectListFromDB( "SELECT trick FROM tricks WHERE card_win = 1 AND player_win = '{$player}' ORDER BY id ASC", true );
            $last_trick = end($list_tricks);
            $result['last_trick_win'][$player] = self::getObjectListFromDB( "SELECT type type, type_arg type_arg FROM tricks WHERE round = '{$round}' AND trick = '{$last_trick}' AND player_win = '{$player}'" );
        }
    }

    // prev and next player
    foreach ($players as $player)
    {
        $prev_id = game::$instance->getPlayerBefore($player);
        $next_id = game::$instance->getPlayerAfter($player);
        $result['player_before'][$player] = self::getObjectListFromDB( "SELECT player_name name, player_color color FROM player WHERE player_id = '{$prev_id}'" );
        $result['player_after'][$player] = self::getObjectListFromDB( "SELECT player_name name, player_color color FROM player WHERE player_id = '{$next_id}'" );

    }


    $tricks_max = 0;
    $nbreplayers = count(self::getObjectListFromDB( "SELECT player_id FROM player", true ));

    if($nbreplayers == 3)
    {
        $tricks_max = 13;
    }
    if($nbreplayers == 4)
    {
        $tricks_max = 10;
    }
    if($nbreplayers == 5)
    {
        $tricks_max = 8;
    }

    $result['tricks_max'] = $tricks_max;

    $result['resume_score'] = [];

    for($i = 1; $i <=$round; $i++)
    {
        if($i != $round)
        {
            
            for($j = 1; $j <= $tricks_max; $j++)
            {
                $result['round_result'][$i][$j]['cards_played'] = self::getObjectListFromDB( "SELECT type type, type_arg type_arg FROM tricks WHERE round = '{$i}' AND trick = '{$j}'" );
                //$result['round_result'][$i][$j]['winner'] = self::getUniqueValueFromDB( "SELECT player_win FROM tricks WHERE round = '{$i}' AND trick = '{$j}' AND card_win = 1");
                $datas_win = self::getObjectListFromDB( "SELECT player_win FROM tricks WHERE round = '{$i}' AND trick = '{$j}' AND card_win = 1", true );
                $result['round_result'][$i][$j]['winner'] = $datas_win[0];
            }

            $result['resume_score'][$i] = self::getObjectListFromDB( "SELECT player_id player_id, score_quest score_quest, score_bonus score_bonus, bonus_enchanteur bonus_enchanteur, score_round score_round FROM scores WHERE round = '{$i}'" );

        }
        else 
        {
            for($j = 1; $j <= $trick; $j++)
            {
                $result['round_result'][$i][$j]['cards_played'] = self::getObjectListFromDB( "SELECT type type, type_arg type_arg FROM tricks WHERE round = '{$i}' AND trick = '{$j}'" );
                //$result['round_result'][$i][$j]['winner'] = self::getUniqueValueFromDB( "SELECT player_win FROM tricks WHERE round = '{$i}' AND trick = '{$j}' AND card_win = 1");
                $datas_win = self::getObjectListFromDB( "SELECT player_win FROM tricks WHERE round = '{$i}' AND trick = '{$j}' AND card_win = 1", true );
                if($datas_win == null)
                {
                    $result['round_result'][$i][$j]['winner'] = null;
                }
                else
                {
                    $result['round_result'][$i][$j]['winner'] = $datas_win[0];
                }
                

            }
        }
        
        
        $result['resume_quest_card'][$i] = self::getObjectListFromDB( "SELECT rand rand, validate validate FROM quest WHERE round = '{$i}'" );


    }


    
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
    $round = game::$instance->getGameStateValue("no_round");
    $end = game::$instance->getGameStateValue("end_game");

    if($end == 0)
    {
        if($round <= 7)
        {
            return floor(($round*100)/7);
        }

        else{

            return 100;
        }

    }

    else{
        return 100;
    }

    
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

        $tableCards = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='table'" );

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

        // TRICKS MAX POUR BOUFFON ET BRICOLEUR
        // UTILISE AUSSI POUR LE DERNIER MESSAGE DU ROUND
        $tricks_max = 0;
        $nbreplayers = count(self::getObjectListFromDB( "SELECT player_id FROM player", true ));

        if($nbreplayers == 3)
        {
            $tricks_max = 13;
        }
        if($nbreplayers == 4)
        {
            $tricks_max = 10;
        }
        if($nbreplayers == 5)
        {
            $tricks_max = 8;
        }

        // COLOR REQUEST ET CARDS PLAYED
        $color_request = game::$instance->getGameStateValue("color_request");
        $cards_played = self::getObjectListFromDB("SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location = 'table'");
        
        // VARIABLES
        $excalibur = 0;
        $pe_green = 0;
        $bouffon = 0;
        $enchanteur = 0;

        $eclaireur = 0;
        $eclaireur_pv = 0;

        $chaman = 0;
        $druide = game::$instance->getGameStateValue("druide_player");
        $pe_blue = game::$instance->getGameStateValue("pe_bleu_player");

        $hydre_play = 0; // Hydre (4 1 0)
        $hydre_last_A = 0;
        $ordered_cards = game::$instance->getCardsOnTableOrdered();
        $PlayerHydre = self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type = 4 AND card_type_arg = 1 AND card_location = 'table'");
        if($PlayerHydre != null)
        {
            $hydre_play = 1;
        }

        //momie (4 2 0)
        $momie_player = self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type = 4 AND card_type_arg = 2 AND card_location = 'table'");
        if($momie_player != null)
        {
            $last_trick = $trick - 1;
            $type_last_card_win = intval(self::getUniqueValueFromDB("SELECT type FROM tricks WHERE round = '{$round}' AND trick = '{$last_trick}' AND card_win = 1"));
            $typearg_last_card_win = intval(self::getUniqueValueFromDB("SELECT type_arg FROM tricks WHERE round = '{$round}' AND trick = '{$last_trick}' AND card_win = 1"));
            foreach ($cards_played as &$card) { 
                if ($card['location_arg'] == $momie_player) {
                    $card['type'] = $type_last_card_win; 
                    $card['type_arg'] = $typearg_last_card_win; 
                    break;
                }
            }
            unset($card);
        }

        //clone (4 3 0)
        $clone_player = self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_type = 4 AND card_type_arg = 3 AND card_location = 'table'");
        if($clone_player != null)
        {
            $last_card_play = game::$instance->getGameStateValue("last_card_play");
            $type_last_card_play = intval(self::getUniqueValueFromDB("SELECT card_type FROM cards WHERE card_id ='{$last_card_play}'"));
            $typearg_last_card_play = intval(self::getUniqueValueFromDB("SELECT card_type_arg FROM cards WHERE card_id ='{$last_card_play}'"));
            foreach ($cards_played as &$card) { 
                if ($card['location_arg'] == $clone_player) {
                    $card['type'] = $type_last_card_play; 
                    $card['type_arg'] = $typearg_last_card_play + 0.5; 
                    break;
                }
            }
            unset($card);
        }


       
        // CALCUL WIN
        $player_win = 0;
        $maxValue = 0;

        if($hydre_play == 0) // l'Hydre n'est pas joué - BOUCLE GAIN NORMAL
        {
            foreach ($cards_played as $card) 
            {

                //EXCALIBUR (4 5 0)
                if ($card['type'] == 4 && $card['type_arg'] == 5)
                {
                    $player_win = $card['location_arg'];
                    $excalibur = 1;
                    break;
                }

                if ($card['type'] == $color_request) {
                    
                    if ($card['type_arg'] > $maxValue) {
                        $maxValue = $card['type_arg'];
                        $player_win = $card['location_arg'];
                        
                    }
                    
                }   


                // PORTE ETENDARD VERT (1 11 1)
                if ($card['type'] == 1 && $card['type_arg'] == 11 && $card['type_arg_2'] == 1)
                {
                    $pe_green = 1;
                    
                }

                // BRICOLEUR VERT (1 11 2)
                if ($card['type'] == 1 && $card['type_arg'] == 11 && $card['type_arg_2'] == 2 && $trick != $tricks_max)
                {
                    game::$instance->setGameStateValue("bricoleur", 1);
                    
                }

                // BOUFFON BLEU (2 1 0)
                if ($card['type'] == 2 && $card['type_arg'] == 1 && $trick == $tricks_max)
                {
                    $bouffon = $card['location_arg'];
                    
                }

                // ENCHANTEUR BLEU (2 11 2)
                if ($card['type'] == 2 && $card['type_arg'] == 11 && $card['type_arg_2'] == 2)
                {
                    $enchanteur = 1;
                    
                }

                // ECLAIREUR ROUGE (3 1 0)
                // PV
                if($card['type_arg'] == 12 || $card['type_arg'] == 13 || $card['type_arg'] == 14 || $card['type_arg'] == 15)
                {
                    $eclaireur_pv = $eclaireur_pv +1;
                }
                //PLAY
                if ($card['type'] == 3 && $card['type_arg'] == 1)
                {
                    $eclaireur = $card['location_arg'];

                }

                // CHAMAN ROUGE (3 11 1)
                if ($card['type'] == 3 && $card['type_arg'] == 11 && $card['type_arg_2'] == 1)
                {
                    $chaman = 1;
                    
                }

                
            }
            
        }

        if($hydre_play == 1)   // l'Hydre est joué (4 1 0)
        {

            foreach($ordered_cards as $card)
            {
                if ($card['type'] == $color_request && $card['type_arg'] == 15) // l'A de la couleur demandée est joué
                {
                    $player_win = $card['location_arg'];
                    break;
                    
                }

                if ($card['type'] != $color_request && $card['type_arg'] == 15) // l'A d'une autre couleur est joué
                {
                    $hydre_last_A = $card['location_arg'];
                        
                }

            }

            if($player_win == 0 && $hydre_last_A == 0)
            {
                $player_win = $PlayerHydre;
            }

            if($player_win == 0 && $hydre_last_A != 0)
            {
                $player_win = $hydre_last_A;
            }

        }

        
        $winner_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$player_win}'");

        /////////////////////////////////// END OF TURN ////////////////////////////
        // MESSAGE ET ENDTURN WIN

        if($trick < $tricks_max)
        {
            if($excalibur == 0 && $hydre_play == 0 && $player_win != $momie_player && $player_win != $clone_player)
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

            if($excalibur == 1)
            {
                game::$instance->notifyAllPlayers(
                        'endTurn',
                        clienttranslate('${player_name} wins the trick thanks to ${log} and begins the next trick'),
                        array(
                            'winner_id' => $player_win,
                            'player_name' => $winner_name,
                            'log' => game::$instance->getLogIcon('4_5'),
                        )
                );
            }


            if($hydre_play == 1)
            {
                game::$instance->notifyAllPlayers(
                        'endTurn',
                        clienttranslate('${player_name} wins the trick thanks to ${log} and begins the next trick'),
                        array(
                            'winner_id' => $player_win,
                            'player_name' => $winner_name,
                            'log' => game::$instance->getLogIcon('4_1'),
                        )
                );
            }

            if($player_win == $momie_player)
            {

                game::$instance->notifyAllPlayers(
                        'endTurn',
                        clienttranslate('${player_name} wins the trick thanks to ${log} and begins the next trick'),
                        array(
                            'winner_id' => $player_win,
                            'player_name' => $winner_name,
                            'log' => game::$instance->getLogIcon('4_2'),
                        )
                );

            }

            if($player_win == $clone_player)
            {

                game::$instance->notifyAllPlayers(
                        'endTurn',
                        clienttranslate('${player_name} wins the trick thanks to ${log} and begins the next trick'),
                        array(
                            'winner_id' => $player_win,
                            'player_name' => $winner_name,
                            'log' => game::$instance->getLogIcon('4_3'),
                        )
                );

            }
        }

        else
        {
            if($excalibur == 0 && $hydre_play == 0 && $player_win != $momie_player && $player_win != $clone_player)
            {
                game::$instance->notifyAllPlayers(
                        'endTurn',
                        clienttranslate('${player_name} wins the last trick'),
                        array(
                            'winner_id' => $player_win,
                            'player_name' => $winner_name,
                        )
                );
            }

            if($excalibur == 1)
            {
                game::$instance->notifyAllPlayers(
                        'endTurn',
                        clienttranslate('${player_name} wins the last trick thanks to ${log}'),
                        array(
                            'winner_id' => $player_win,
                            'player_name' => $winner_name,
                            'log' => game::$instance->getLogIcon('4_5'),
                        )
                );
            }


            if($hydre_play == 1)
            {
                game::$instance->notifyAllPlayers(
                        'endTurn',
                        clienttranslate('${player_name} wins the last trick thanks to ${log}'),
                        array(
                            'winner_id' => $player_win,
                            'player_name' => $winner_name,
                            'log' => game::$instance->getLogIcon('4_1'),
                        )
                );
            }

            if($player_win == $momie_player)
            {

                game::$instance->notifyAllPlayers(
                        'endTurn',
                        clienttranslate('${player_name} wins the last trick thanks to ${log}'),
                        array(
                            'winner_id' => $player_win,
                            'player_name' => $winner_name,
                            'log' => game::$instance->getLogIcon('4_2'),
                        )
                );

            }

            if($player_win == $clone_player)
            {

                game::$instance->notifyAllPlayers(
                        'endTurn',
                        clienttranslate('${player_name} wins the last trick thanks to ${log}'),
                        array(
                            'winner_id' => $player_win,
                            'player_name' => $winner_name,
                            'log' => game::$instance->getLogIcon('4_3'),
                        )
                );

            }
        }


        ////////////////////////////////////////////////////////////////////////////
        //    BONUS
        ////////////////////////////////////////////////////////////////////////////


        // BONUS PORTE ETENDARD VERT (1 11 1)
        if($pe_green == 1)
        {
             game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} wins ${log} and gains 3 ${log2}'),
                    array(
                        'player_name' => $winner_name,
                        'log' => game::$instance->getLogIcon('1_11'),
                        'log2' => game::$instance->getLogPv(),
                    )
            );

            game::$instance->DbQuery("UPDATE bonus set bonus = 3 WHERE player_id = '{$player_win}' AND round = '{$round}'");

        }

        // BONUS BRICOLEUR VERT (1 11 2)
        $bricoleur = game::$instance->getGameStateValue("bricoleur");
        if($bricoleur == 2)
        {
            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} will score 3 ${log2} thanks to ${log} played in the previous trick'),
                    array(
                        'player_name' => $winner_name,
                        'log' => game::$instance->getLogIcon('1_11'),
                        'log2' => game::$instance->getLogPv(),
                    )
            );

            game::$instance->DbQuery("UPDATE bonus set bonus = 3 WHERE player_id = '{$player_win}' AND round = '{$round}'");
            game::$instance->setGameStateValue("bricoleur", 0);

        }

        if($bricoleur == 1)
        {
            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${log} has been played. The player who wins the next trick will score 3 ${log2}'),
                    array(
                        'log' => game::$instance->getLogIcon('1_11'),
                        'log2' => game::$instance->getLogPv(),
                    )
            );

            game::$instance->setGameStateValue("bricoleur", 2);
        }


        // BONUS BOUFFON BLEU (2 1 0)
        if($bouffon != 0)
        {
            $bouffon_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$bouffon}'");
             game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} plays ${log} on the last trick and will score 3 ${log2}'),
                    array(
                        'player_name' => $bouffon_name,
                        'log' => game::$instance->getLogIcon('2_1'),
                        'log2' => game::$instance->getLogPv(),
                    )
            );

            game::$instance->DbQuery("UPDATE bonus set bonus = 3 WHERE player_id = '{$bouffon}' AND round = '{$round}'");

        }


        // ENCHANTEUR BLEU (2 11 2)
        if ($enchanteur == 1)
        {

            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} wins ${log} and will double ${log2} (won or lost) at the end of the round'),
                    array(
                        'player_name' => $winner_name,
                        'log' => game::$instance->getLogIcon('2_11'),
                        'log2' => game::$instance->getLogPv(),
                    )
            );

            game::$instance->setGameStateValue("enchanteur", $player_win);
            
            
        }

        // BONUS ECLAIREUR ROUGE (3 1 0)
        if($eclaireur != 0)
        {
            $eclaireur_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$eclaireur}'");
             game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} plays ${log} and will score ${pv} ${log2}'),
                    array(
                        'player_name' => $eclaireur_name,
                        'pv' => $eclaireur_pv,
                        'log' => game::$instance->getLogIcon('3_1'),
                        'log2' => game::$instance->getLogPv(),
                    )
            );

            game::$instance->DbQuery("UPDATE bonus set bonus = $eclaireur_pv WHERE player_id = '{$eclaireur}' AND round = '{$round}'");

        }

        // BONUS CHAMAN ROUGE (3 11 1)
        if ($chaman == 1)
        {
            game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} wins ${log} and will lose 3 ${log2}'),
                    array(
                        'player_name' => $winner_name,
                        'log' => game::$instance->getLogIcon('3_11'),
                        'log2' => game::$instance->getLogPv(),
                    )
            );

            game::$instance->DbQuery("UPDATE bonus set bonus = -3 WHERE player_id = '{$player_win}' AND round = '{$round}'");
            
        }

        
        /////////////////////////////////////////////////////////////////////////////////

        // VERIFIER SI 5 VERT A ETE REMPORTE ( => PROCHAIN DEALER)
        foreach($cards_played as $card_played)
        {
            if($card_played['type'] == 1 && $card_played['type_arg'] == 5)
            {
                game::$instance->notifyAllPlayers(
                'message',
                clienttranslate('${player_name} wins ${log} and will be the dealer for the next round'),
                array(
                    'winner_id' => $player_win,
                    'player_name' => $winner_name,
                    'log' => game::$instance->getLogIcon('1_5'),
                )
                );

                game::$instance->setGameStateValue("first_player_deal", $player_win);

            }



        }

        //MESSAGE DRUIDE VERT (1 1 0)
        if($druide != 0) {

            $druide_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$druide}'");
             game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} has played ${log} and must exchange their hand with that of another player'),
                    array(
                        'player_name' => $druide_name,
                        'log' => game::$instance->getLogIcon('1_1'),
                    )
            );
        }


        //MESSAGE PE BLEU (2 11 1)
        if($pe_blue != 0) {

            $pe_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$pe_blue}'");
             game::$instance->notifyAllPlayers(
                    'message',
                    clienttranslate('${player_name} has played ${log}. All players will pass their hand to the NEXT or PREVIOUS player'),
                    array(
                        'player_name' => $pe_name,
                        'log' => game::$instance->getLogIcon('2_11'),
                    )
            );
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

function getLogIcon($icon)
{
    $explode = explode('_', $icon);
    $type = $explode[0];
    $type_arg = $explode[1];

    if($type == 1)
    {
        $variableY = 0;
    }

    if($type == 2)
    {
        $variableY = -100;
    }

    if($type == 3)
    {
        $variableY = -200;
    }

    if($type == 4)
    {
        $variableY = -300;
    }

    $variableX = ($type_arg-1)*(-100);


    return "<div class='icon_log' title='' style='background-position-x: {$variableX}%; background-position-y: {$variableY}%;'></div>";
    
}

function getLogPv()
{
    
    return "<div class='log_pv title=''></div>";
    
}


function calculScore()
{
    $players = self::getObjectListFromDB( "SELECT player_id FROM player", true );
    $round = game::$instance->getGameStateValue("no_round");
    $type_quest = self::getUniqueValueFromDB("SELECT rand FROM quest WHERE round = '{$round}'");
    $face_quest = self::getUniqueValueFromDB("SELECT validate FROM quest WHERE round = '{$round}'");

    $quest = $type_quest.$face_quest;
    

    foreach($players as $player)
    {
        $player_name = self::getUniqueValueFromDB("SELECT player_name FROM player WHERE player_id = '{$player}'");
        $pv = 0;
        $score_quest = 0;
        $score_bonus = self::getUniqueValueFromDB("SELECT bonus FROM bonus WHERE player_id = '{$player}' AND round = '{$round}'");

        $bonus_enchanteur = 1;
        if(game::$instance->getGameStateValue("enchanteur") == $player)
        {
            game::$instance->setGameStateValue("enchanteur", 0);
            $bonus_enchanteur = 2;
        }

        if($quest == '11')
        {

            
            $tab = self::getObjectListFromDB( "SELECT card_win FROM tricks WHERE round = '{$round}' AND player_id = '{$player}' ORDER BY id ASC", true );
            $max = 0;
            $count = 0;

            foreach ($tab as $value) {
                if ($value == 1) {
                    $count++;
                    if ($count > $max) {
                        $max = $count;
                    }
                } else {
                    $count = 0;
                }
            }
            $pv = $max * 2;
            
        }

        if($quest == '12')
        {
            $tab = self::getObjectListFromDB( "SELECT card_win FROM tricks WHERE round = '{$round}' AND player_id = '{$player}' ORDER BY id ASC", true );
            $max = 0;
            $count = 0;

            foreach ($tab as $value) {
                if ($value == 1) {
                    $count++;
                    if ($count > $max) {
                        $max = $count;
                    }
                } else {
                    $count = 0;
                }
            }
            $pv = $max * (-2);
                    
        }

        if($quest == '21')
        {
            $nbreplayers = count(self::getObjectListFromDB( "SELECT player_id FROM player", true ));

            if($nbreplayers == 3)
            {
                $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND card_win = 1 AND trick >= 10", true ));
            }
            if($nbreplayers == 4)
            {
                $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND card_win = 1 AND trick >= 7", true ));
            }
            if($nbreplayers == 5)
            {
                $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND card_win = 1 AND trick >= 5", true ));
            }
            
            $pv = $nb_tricks * 2;
        }

        if($quest == '22')
        {
            $nbreplayers = count(self::getObjectListFromDB( "SELECT player_id FROM player", true ));

            if($nbreplayers == 3)
            {
                $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND card_win = 1 AND trick >= 10", true ));
            }
            if($nbreplayers == 4)
            {
                $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND card_win = 1 AND trick >= 7", true ));
            }
            if($nbreplayers == 5)
            {
                $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND card_win = 1 AND trick >= 5", true ));
            }
            
            $pv = $nb_tricks * (-2);
            
            
        }

        if($quest == '31')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND (type_arg = 3 OR type_arg = 4)", true ));
            $pv = $nb_tricks * 4;
            
        }

        if($quest == '32')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND (type_arg = 6 OR type_arg = 7)", true ));
            $pv = $nb_tricks * 4;
            
        }

        if($quest == '41')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 2", true ));
            $pv = $nb_tricks * (-1);
            
        }

        if($quest == '42')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 3", true ));
            $pv = $nb_tricks * (-1);
            
        }

        if($quest == '51')
        {
            $beforeplayer = game::$instance->getPlayerBefore($player);
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$beforeplayer}'", true ));
            $pv = $nb_tricks;
            
        }

        if($quest == '52')
        {
            $nextplayer = game::$instance->getPlayerAfter($player);
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$nextplayer}'", true ));
            $pv = $nb_tricks;
            
            
        }

        if($quest == '61')
        {
            $sum_player = 0;
            $max = 1;

            $sum = self::getUniqueValueFromDB("SELECT SUM(type_arg) FROM tricks WHERE type_arg >= 1 AND type_arg <= 11  AND type <= 3 AND round = '{$round}' AND player_win = '{$player}'");
            if($sum != null)
            {
                $sum_player = $sum;
            } 

            

            foreach($players as $player_test)
            {
                $sum2 = 0;

                if($player_test != $player)
                {
                    $sum_player_other = self::getUniqueValueFromDB("SELECT SUM(type_arg) FROM tricks WHERE type_arg >= 1 AND type_arg <= 11 AND type <= 3 AND round = '{$round}' AND player_win = '{$player_test}'");
                    if($sum_player_other != null)
                    {
                        $sum2 = $sum_player_other;
                    }

                    if($sum <= $sum2)
                    {
                        $max = 0;
                        break;
                    }

                }
            }

            if($max == 1)
            {
                $pv = 5;
            }
            
        }

        if($quest == '62')
        {
            $sum_player = 0;
            $max = 1;

            $sum = self::getUniqueValueFromDB("SELECT SUM(type_arg) FROM tricks WHERE type_arg >= 1 AND type_arg <= 11 AND round = '{$round}' AND player_win = '{$player}'");
            if($sum != null)
            {
                $sum_player = $sum;
            } 
            

            foreach($players as $player_test)
            {
                $sum2 = 0;

                if($player_test != $player)
                {
                    $sum_player_other = self::getUniqueValueFromDB("SELECT SUM(type_arg) FROM tricks WHERE type_arg >= 1 AND type_arg <= 11 AND round = '{$round}' AND player_win = '{$player_test}'");
                    if($sum_player_other != null)
                    {
                        $sum2 = $sum_player_other;
                    }

                    if($sum <= $sum2)
                    {
                        $max = 0;
                        break;
                    }

                }
            }

            if($max == 1)
            {
                $pv = -5;
            } 
            
        }
        
        if($quest == '71')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));
            $pv = $nb_tricks;
            
        }

        if($quest == '72')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));
            $pv = $nb_tricks * (-1);
            
        }

        if($quest == '81')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));
            if ($nb_tricks == 3)
            {
                $pv = 5;
            }
            
            
        }

        if($quest == '82')
        {

            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));
            if ($nb_tricks == 1)
            {
                $pv = 5;
            }
            
            
        }

        if($quest == '91')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));
            if ($nb_tricks == 4)
            {
                $pv = 5;
            }
            
        }

        if($quest == '92')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));
            if ($nb_tricks == 2)
            {
                $pv = 5;
            }
            
        }

        if($quest == '101')
        {

            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));
            if ($nb_tricks % 2 == 0)
            {
                $pv = 5;
            }
            
            
        }

        if($quest == '102')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));
            if ($nb_tricks % 2 != 0)
            {
                $pv = 5;
            }
            
        }

        if($quest == '111')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));
            $ok = 1;

            foreach($players as $player_test)
            {                
                if($player_test != $player)
                {
                    $trick2 = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player_test}'", true ));
                    if($trick2 == $nb_tricks)
                    {
                        $ok = 0;
                        break;
                    }

                }
            }

            if($ok == 1)
            {
                $pv = 5;
            }
        }

        if($quest == '112')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));
            $ok = 0;

            foreach($players as $player_test)
            {                
                if($player_test != $player)
                {
                    $trick2 = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player_test}'", true ));
                    if($trick2 == $nb_tricks)
                    {
                        $ok = 1;
                        break;
                    }

                }
            }

            if($ok == 1)
            {
                $pv = 5;
            }

        }

        if($quest == '121')
        {

            $nb_vert = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 1", true ));
            $nb_bleu = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 2", true ));

            $pv = $nb_bleu - $nb_vert;
        }

        if($quest == '122')
        {
            $nb_vert = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 1", true ));
            $nb_bleu = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 2", true ));

            $pv = $nb_vert - $nb_bleu;

        }

        if($quest == '131')
        {
            $nb_k = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type_arg = 14", true ));
            $nb_q = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type_arg = 13", true ));

            $pv = (3*$nb_k) - (3*$nb_q);

        }

        if($quest == '132')
        {
            $nb_k = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type_arg = 14", true ));
            $nb_q = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type_arg = 13", true ));

            $pv = (3*$nb_q) - (3*$nb_k);

        }

        if($quest == '141')
        {
            $players_win_tricks = self::getObjectListFromDB( "SELECT player_win FROM tricks WHERE round = '{$round}' AND card_win = 1 ORDER BY id ASC", true );
            $players_ordre = [];
            
            foreach($players as $player_test)
            {
                $players_tricks[$player_test] = [0];
            }

            foreach($players_win_tricks as $player_win_trick)
            {

                $players_tricks[$player_win_trick][0] = $players_tricks[$player_win_trick][0] + 1;

                if($players_tricks[$player_win_trick][0] == 3)
                {
                    $players_ordre[] = $player_win_trick;
                }
                
            }

            if (($index = array_search($player, $players_ordre)) !== false) {
            
                $pv = 3 +  $index;
            }

            
        }

        if($quest == '142')
        {

            $players_win_tricks = self::getObjectListFromDB( "SELECT player_win FROM tricks WHERE round = '{$round}' AND card_win = 1 ORDER BY id ASC", true );
            $players_ordre = [];
            
            foreach($players as $player_test)
            {
                $players_tricks[$player_test] = [0];
            }

            foreach($players_win_tricks as $player_win_trick)
            {

                $players_tricks[$player_win_trick][0] = $players_tricks[$player_win_trick][0] + 1;

                if($players_tricks[$player_win_trick][0] == 3)
                {
                    $players_ordre[] = $player_win_trick;
                }
                
            }

            if (($index = array_search($player, $players_ordre)) !== false) {
            
                $pv = -3 - $index;
            }

            
        }

        if($quest == '151')
        {
            $player_win_type_arg = self::getObjectListFromDB( "SELECT type_arg FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type <= 3 ORDER BY id ASC", true );
            $card1 = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $card2 = [0, 0, 0, 0];

            foreach($player_win_type_arg as $type_arg)
            {
                if($type_arg <= 11)
                {
                    $card1[$type_arg-1] = $card1[$type_arg-1] +1;
                }

                else {

                    $card2[$type_arg-12] = $card2[$type_arg-12] +1;
                }
            }

            foreach ($card1 as $card)
            {
                if($card == 3)
                {
                    $pv = $pv + 1;
                }
            }

            foreach ($card2 as $card)
            {
                if($card == 3)
                {
                    $pv = $pv + 2;
                }
            }
        }

        if($quest == '152')
        {

            $player_win_type_arg = self::getObjectListFromDB( "SELECT type_arg FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type <= 3 ORDER BY id ASC", true );
            $card1 = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $card2 = [0, 0, 0, 0];

            foreach($player_win_type_arg as $type_arg)
            {
                if($type_arg <= 11)
                {
                    $card1[$type_arg-1] = $card1[$type_arg-1] +1;
                }

                else {

                    $card2[$type_arg-12] = $card2[$type_arg-12] +1;
                }
            }

            foreach ($card1 as $card)
            {
                if($card == 3)
                {
                    $pv = $pv - 1;
                }
            }

            foreach ($card2 as $card)
            {
                if($card == 3)
                {
                    $pv = $pv - 2;
                }
            }

        }

        if($quest == '161')
        {
            $tab = self::getObjectListFromDB( "SELECT card_win FROM tricks WHERE round = '{$round}' AND player_id = '{$player}' ORDER BY id ASC", true );
            $max = 0;
            $count = 0;

            foreach ($tab as $value) {
                if ($value == 1) {
                    $count++;
                    if ($count > $max) {
                        $max = $count;
                    }
                } else {
                    $count = 0;
                }
            }

            if($max >= 4)
            {
                $pv = 5;
            }

        }

        if($quest == '162')
        {
            $tab = self::getObjectListFromDB( "SELECT card_win FROM tricks WHERE round = '{$round}' AND player_id = '{$player}' ORDER BY id ASC", true );
            $max = 0;
            $count = 0;

            foreach ($tab as $value) {
                if ($value == 0) {
                    $count++;
                    if ($count > $max) {
                        $max = $count;
                    }
                } else {
                    $count = 0;
                }
            }

            if($max >= 4)
            {
                $pv = 5;
            }

        }

        if($quest == '171')
        {
            $nb_vert = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND type = 1 AND player_win = '{$player}'", true ));
            $nb_bleu = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND type = 2 AND player_win = '{$player}'", true ));
            $nb_rouge = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND type = 3 AND player_win = '{$player}'", true ));

            $tab = [$nb_vert, $nb_bleu, $nb_rouge];

            $pv = min($tab);
        }

        if($quest == '172')
        {
            $nb_vert = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND type = 1 AND player_win = '{$player}'", true ));
            $nb_bleu = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND type = 2 AND player_win = '{$player}'", true ));
            $nb_rouge = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND type = 3 AND player_win = '{$player}'", true ));

            $tab = [$nb_vert, $nb_bleu, $nb_rouge];

            $pv = max($tab);

        }

        if($quest == '181')
        { 
            $valeur = self::getUniqueValueFromDB("SELECT MIN(type_arg) AS valeur_min FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 1 AND type_arg <= 11");
            if ($valeur != null)
            {
                $pv = $valeur;
            }
        }

        if($quest == '182')
        {
            $valeur = self::getUniqueValueFromDB("SELECT MIN(type_arg) AS valeur_min FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 2 AND type_arg <= 11");
            if ($valeur != null)
            {
                $pv = $valeur;
            }
        }
        

        if($quest == '191')
        {

            $player_win_type_arg_blue = self::getObjectListFromDB( "SELECT type_arg FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 2 ORDER BY id ASC", true );
            $player_win_type_arg_blue2 = self::getObjectListFromDB( "SELECT type_arg FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 2 AND type_arg >= 12 ORDER BY id ASC", true );
            $count1 = count($player_win_type_arg_blue);
            $count2 = count($player_win_type_arg_blue2);

            $pv = $count1 + $count2;

        }

        if($quest == '192')
        {
            $player_win_type_arg_blue = self::getObjectListFromDB( "SELECT type_arg FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 2 ORDER BY id ASC", true );
            $player_win_type_arg_blue2 = self::getObjectListFromDB( "SELECT type_arg FROM tricks WHERE round = '{$round}' AND player_win = '{$player}' AND type = 2 AND type_arg >= 12 ORDER BY id ASC", true );
            $count1 = count($player_win_type_arg_blue);
            $count2 = count($player_win_type_arg_blue2);

            $pv = -($count1) - $count2;

        }

        if($quest == '201')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));

            $beforeplayer = game::$instance->getPlayerBefore($player);
            $nb_tricks_before = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$beforeplayer}'", true ));
            
            $nextplayer = game::$instance->getPlayerAfter($player);
            $nb_tricks_after = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$nextplayer}'", true ));

            if($nb_tricks < $nb_tricks_before)
            {
                $pv = $pv + 3;
            }

            if($nb_tricks > $nb_tricks_after)
            {
                $pv = $pv + 3;
            }

        }

        if($quest == '202')
        {
            $nb_tricks = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$player}'", true ));

            $beforeplayer = game::$instance->getPlayerBefore($player);
            $nb_tricks_before = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$beforeplayer}'", true ));
            
            $nextplayer = game::$instance->getPlayerAfter($player);
            $nb_tricks_after = count(self::getObjectListFromDB( "SELECT id FROM tricks WHERE round = '{$round}' AND card_win = 1 AND player_win = '{$nextplayer}'", true ));

            if($nb_tricks > $nb_tricks_before)
            {
                $pv = $pv + 3;
            }

            if($nb_tricks > $nb_tricks_after)
            {
                $pv = $pv + 3;
            }

        }

        

        $pv_round = ($pv + $score_bonus) * $bonus_enchanteur;
        self::DbQuery("INSERT INTO scores (round, player_id, score_quest, score_bonus, bonus_enchanteur, score_round) VALUES ($round, $player, $pv, $score_bonus, $bonus_enchanteur, $pv_round)");
        self::DbQuery("UPDATE player set player_score = player_score + $pv_round WHERE player_id = '{$player}'");

        if($bonus_enchanteur == 1)
        {
            game::$instance->notifyAllPlayers(
                        'message',
                        clienttranslate('${player_name}: ${pv} ${log} <br> (Quest: ${quest} ${log} + Bonus: ${bonus} ${log})'),
                        array(
                            'player_name' => $player_name,
                            'pv' => $pv_round,
                            'quest' => $pv,
                            'bonus' => $score_bonus,
                            'log' => game::$instance->getLogPv(),
                        )
            );
        }

        if($bonus_enchanteur == 2)
        {
            game::$instance->notifyAllPlayers(
                        'message',
                        clienttranslate('${player_name}: ${pv} ${log} <br> (Quest: ${quest} ${log} + Bonus: ${bonus} ${log}) x2 ${log2}'),
                        array(
                            'player_name' => $player_name,
                            'pv' => $pv_round,
                            'quest' => $pv,
                            'bonus' => $score_bonus,
                            'log' => game::$instance->getLogPv(),
                            'log2' => game::$instance->getLogIcon('2_11'),
                        )
            );
        }

        

        $new_score = self::getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id = '{$player}'");
        game::$instance->notifyAllPlayers(
                'score',
                '',
                array(
                    'player' => $player,
                    'score' => $new_score,
                 
                )
                );

    }






   
 
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

    public function actValidateMulti(string $arg1)
    {
        $pending =  self::getObjectFromDB("SELECT* FROM pending order by id desc limit 1");
        $this->callPending($pending, true, $arg1);
        self::DbQuery("delete from pending where id=" . $pending['id']);
        $this->gamestate->nextState('next');
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
