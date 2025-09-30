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

        $this->initGameStateLabels([

            "first_player_deal" => 10,
            "first_player_quest" => 11,
            "first_player_play" => 12,
            "no_round" => 13,
            "no_trick" => 14, //no trick pour le round
            "no_turn" => 15, //no turn pour le game
            "color_request" => 16,
           
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
        $this->setGameStateInitialValue("no_turn", 1);
        $this->setGameStateInitialValue("color_request", 0);

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
        }

        if($rand == 6)
        {
            self::DbQuery("UPDATE cards set card_type = 1, card_type_arg = 1 WHERE card_type = 4");
        }

        if($rand == 7)
        {
            self::DbQuery("UPDATE cards set card_type = 1, card_type_arg = 11, card_type_arg_2 = 1 WHERE card_type = 4");
        }

        if($rand==8)
        {
            self::DbQuery("UPDATE cards set card_type = 1, card_type_arg = 11, card_type_arg_2 = 2 WHERE card_type = 4");
        }

        if($rand == 9)
        {
            self::DbQuery("UPDATE cards set card_type = 2, card_type_arg = 1 WHERE card_type = 4");
        }

         if($rand == 10)
        {
            self::DbQuery("UPDATE cards set card_type = 2, card_type_arg = 11, card_type_arg_2 = 1 WHERE card_type = 4");
        }

        if($rand == 11)
        {
            self::DbQuery("UPDATE cards set card_type = 2, card_type_arg = 11, card_type_arg_2 = 2 WHERE card_type = 4");
        }

        if($rand == 12)
        {
            self::DbQuery("UPDATE cards set card_type = 3, card_type_arg = 1 WHERE card_type = 4");
        }

         if($rand == 13)
        {
            self::DbQuery("UPDATE cards set card_type = 3, card_type_arg = 11, card_type_arg_2 = 1 WHERE card_type = 4");
        }

        if($rand == 14)
        {
            self::DbQuery("UPDATE cards set card_type = 3, card_type_arg = 11, card_type_arg_2 = 2 WHERE card_type = 4");
        }

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
    $result['no_turn'] = $this->getGameStateValue('no_turn');
    $result['first_player_play'] = $this->getGameStateValue('first_player_play');
    $result['my_hand'] = self::getCollectionFromDB( "SELECT card_id id, card_type type, card_type_arg type_arg, card_type_arg_2 type_arg_2, card_location location, card_location_arg location_arg FROM cards WHERE card_location ='hand' AND card_location_arg='{$current_player_id}'" );
    $result['table'] = $this->getCardsOnTableOrdered();


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
            throw new feException( "Not a valid selection");
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
        $color_request = game::$instance->getGameStateValue("color_request");

        // $first_player_play = game::$instance->getGameStateValue("first_player_play");

        // $ordre_players[] = intval($first_player_play);
        // $next = game::$instance->getPlayerAfter($first_player_play);
        // $count_players = count(self::getObjectListFromDB("SELECT player_id id FROM player", true));
        // for ($i = 1; $i <= $count_players - 1; $i++) {
        //     $ordre_players[] = $next;
        //     $next = game::$instance->getPlayerAfter($next);
        // }

        $player_win = self::getUniqueValueFromDB("SELECT card_location_arg FROM cards WHERE card_location = 'table' AND card_type ='{$color_request}' ORDER BY card_type_arg DESC LIMIT 1");

        return $player_win;


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
