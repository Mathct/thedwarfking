/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * thedwarfking implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * thedwarfking.js
 *
 * thedwarfking user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {


    /* CONSTANTS HERE */
    const CARD_WIDTH = 150;
    const CARD_HEIGHT = 236;
    const CARDS_PER_ROW = 16;



    return declare("bgagame.thedwarfking", ebg.core.gamegui, {
        constructor: function(){
            console.log('thedwarfking constructor');
              
        
        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */

/////////////////////////////////////////////////////////////////////////////////           
//    _____                      _____        _            
//   / ____|                    |  __ \      | |           
//  | |  __  __ _ _ __ ___   ___| |  | | __ _| |_ __ _ ___ 
//  | | |_ |/ _` | '_ ` _ \ / _ \ |  | |/ _` | __/ _` / __|
//  | |__| | (_| | | | | | |  __/ |__| | (_| | || (_| \__ \
//   \_____|\__,_|_| |_| |_|\___|_____/ \__,_|\__\__,_|___/
//                                                        
/////////////////////////////////////////////////////////////////////////////////
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );

                       
            // TODO: Set up your game interface here, according to "gamedatas"

            this.players = gamedatas.players; // A RAJOUTER POUR MOTEUR (UTILITY METHODS)

            this.my_hand = gamedatas.my_hand;
            this.table = gamedatas.table;
            this.first_player_play = gamedatas.first_player_play;
            this.no_round = parseInt(gamedatas.no_round);
            this.no_trick = parseInt(gamedatas.no_trick);
            this.no_turn = parseInt(gamedatas.no_turn);


            this.setupBoard();
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

         
            //// CONNECTIONS CLICK
            dojo.query(".stockitem").connect('onclick', this, 'onSelect' )

            console.log( "Ending game setup" );
        },

/////////////////////////////////////////////////////////////////////////////////   
//         _____ _        _            
//        / ____| |      | |           
//       | (___ | |_ __ _| |_ ___  ___ 
//        \___ \| __/ _` | __/ _ \/ __|
//        ____) | || (_| | ||  __/\__ \
//       |_____/ \__\__,_|\__\___||___/
//                                    
/////////////////////////////////////////////////////////////////////////////////    
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName, args );

            switch( stateName )
            {
            
            case 'playerTurn':
                this.args = args.args;
                for( var sid in this.args.selectable)
                {
                    if(this.isCurrentPlayerActive())
                    {
                        dojo.query("#"+this.args.selectable[sid]).addClass("selectable");
                    
                    }
                }

                for( var sid in this.args.selected)
                {
                    if(this.isCurrentPlayerActive())
                    {
                        dojo.query("#"+this.args.selected[sid]).addClass("selected");
                    }
                }

          

                if( this.isCurrentPlayerActive() )
                {
                    if(args.args.titleyou != null) 
                    {
                        $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.titleyou).replace('${you}', this.divYou()).replace(/#opponent#/g,args.args.opponent).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);
                    }
                } 
                    
                else
                {
                    if(args.args.title != null) 
                    {
                        $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.title).replace('${actplayer}', this.divActPlayer()).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);  
                    }
                }

                    
                
                break;
    
           
           
            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );

            dojo.query(".selectable").removeClass("selectable");
            dojo.query(".selected").removeClass("selected");
            
            switch( stateName )
            {
            
                      
           
            case 'dummy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName, args );
                      
            if( this.isCurrentPlayerActive() )
                {            
                    switch( stateName )
                    {
    
                        case "playerTurn":
                            for( var nb in args.buttons )
                            { 
                                     
                                if(args.buttons[nb] == "cancel")
                                {
                                this.addActionButton( 'cancel', _("Cancel") ,'onOpButton', null, null, 'gray' );
                                }
                                if(args.buttons[nb] == "pass")
                                {
                                this.addActionButton( 'pass', _("Pass") ,'onOpButton', null, null, 'gray' );
                                }
                                if(args.buttons[nb] == "yes") 
                                {
                                this.addActionButton('btn_yes', _("Yes"), 'onOpButton', null, null, 'blue');
                                this.startActionTimer('btn_yes', 5, 1);
                                }
                                if(args.buttons[nb] == "no") 
                                {
                                this.addActionButton( 'no', _("No") ,'onOpButton', null, null, 'red' );
                                }
                            }
                                      
                            
                            break;
    
    
    
                    }
                }
        },        

/////////////////////////////////////////////////////////////////////////////////         
//   _    _ _   _ _ _ _                          _   _               _     
//  | |  | | | (_) (_) |                        | | | |             | |    
//  | |  | | |_ _| |_| |_ _   _   _ __ ___   ___| |_| |__   ___   __| |___ 
//  | |  | | __| | | | __| | | | | '_ ` _ \ / _ \ __| '_ \ / _ \ / _` / __|
//  | |__| | |_| | | | |_| |_| | | | | | | |  __/ |_| | | | (_) | (_| \__ \
//   \____/ \__|_|_|_|\__|\__, | |_| |_| |_|\___|\__|_| |_|\___/ \__,_|___/
//                         __/ |                                           
//                        |___/                                            
/////////////////////////////////////////////////////////////////////////////////  

divYou : function() {
            
    var color = this.players[this.player_id].color;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + _("You") + "</span>";
    return you;
},

divActPlayer : function() {        	
    var color = this.players[this.getActivePlayerId()].color;
    var name = this.players[this.getActivePlayerId()].name;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + name + "</span>";
    return you;
},

format_string_recursive : function(log, args) {
    try {
        if (log && args && !args.processed) {
            args.processed = true;

            
        }
    } catch (e) {
        console.error(log,args,"Exception thrown", e.stack);
    }
    return this.inherited(arguments);
},
 
attachToNewParentNoDestroy: function (mobile_in, new_parent_in, relation, place_position) 
    {

        const mobile = $(mobile_in);
        const new_parent = $(new_parent_in);

        var src = dojo.position(mobile);
        if (place_position)
            mobile.style.position = place_position;
        dojo.place(mobile, new_parent, relation);
        mobile.offsetTop;//force re-flow
        var tgt = dojo.position(mobile);
        var box = dojo.marginBox(mobile);
        var cbox = dojo.contentBox(mobile);
        var left = box.l + src.x - tgt.x;
        var top = box.t + src.y - tgt.y;

        mobile.style.position = "absolute";
        mobile.style.left = left + "px";
        mobile.style.top = top + "px";
        box.l += box.w - cbox.w;
        box.t += box.h - cbox.h;
        mobile.offsetTop;//force re-flow
        return box;
    },

// TIMER sur bouton

startActionTimer: function(buttonId, time, pref, autoclick = false) {
    var button = $(buttonId);
    var isReadOnly = this.isReadOnly();
    if (button == null || isReadOnly || pref == 2) {
        return;
    }

    // If confirm disabled, click on button
    if (pref == 0) {
        if (autoclick) 
            button.click();
        return;
    }

    this._actionTimerLabel = button.innerHTML;
    this._actionTimerSeconds = time;
    this._actionTimerFunction = () => {
        var button = $(buttonId);
        if (button == null) {
            this.stopActionTimer();
        } 
        else if (this._actionTimerSeconds-- > 1) {
            button.innerHTML = this._actionTimerLabel + ' (' + this._actionTimerSeconds + ')';
        } 
        else {
            //debug('Timer ' + buttonId + ' execute');
            button.click();
            this.stopActionTimer();
        }
    };
    this._actionTimerFunction();
    this._actionTimerId = window.setInterval(this._actionTimerFunction.bind(this), 1000);
    //debug('Timer #' + this._actionTimerId + ' ' + buttonId + ' start');
},

stopActionTimer() {
    if (this._actionTimerId != null) {
        //debug('Timer #' + this._actionTimerId + ' stop');
        window.clearInterval(this._actionTimerId);
        delete this._actionTimerId;
    }
},

isReadOnly: function () {
    return (
        this.isSpectator || typeof g_replayFrom != "undefined" || g_archive_mode
    );
},

//////// RESIZED

onScreenWidthChange: function () {
//this.updateLayout();
},

updateLayout: function () {

    var gameWidth = 2000;
    

    game_play_area = document.getElementById('game_play_area');

    if(game_play_area.offsetWidth <= 2000)
    {
        var horizontalScale = game_play_area.offsetWidth / gameWidth;

        var resized = document.getElementById('resized');
        resized.style.transform = 'scale(' + horizontalScale + ')';

        var scaledHeight = (resized.offsetHeight * horizontalScale);
        game_play_area.style.height = scaledHeight+'px';

    }
},


/////////////////////////////////////////////////////////////////////////////////  
//         _____  _                       _                  _   _             
//        |  __ \| |                     ( )                | | (_)            
//        | |__) | | __ _ _   _  ___ _ __|/ ___    __ _  ___| |_ _  ___  _ __  
//        |  ___/| |/ _` | | | |/ _ \ '__| / __|  / _` |/ __| __| |/ _ \| '_ \ 
//        | |    | | (_| | |_| |  __/ |    \__ \ | (_| | (__| |_| | (_) | | | |
//        |_|    |_|\__,_|\__, |\___|_|    |___/  \__,_|\___|\__|_|\___/|_| |_|
//                         __/ |                                               
//                        |___/                                                
/////////////////////////////////////////////////////////////////////////////////  
        
                
        onSelect: function(evt)
        {        	 
            // Preventing default browser reaction
             dojo.stopEvent( evt );

            
             
            if( !this.isCurrentPlayerActive() || !(evt.currentTarget.classList.contains('selectable')) )
            {   
                return; 
            }
            
            if(this.isCurrentPlayerActive() && evt.currentTarget.classList.contains('selectable'))
            {
                
                this.bgaPerformAction('actSelect', { arg1: evt.currentTarget.id });
            }

        },

        onOpButton: function(evt)
        {
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );
            
            this.bgaPerformAction('actButton', { arg1: evt.currentTarget.id });
            
            

        },

        setupBoard: function () {
        console.log('Setting up the board');

        
        const gameBoardHTML = `
            
                    <div id="nb_round">${_("Round")}: ${this.no_round} / 7</div>

                    <div id="table_cards_container" class="cards-container">
                        <div class="titre">${_("Cards played")}:</div>
                        <div id="table_cards" class="cards"></div>
                        
                    </div> 

                    
                    <div id="hand_container" class="cards-container">
                        <div class="titre" id="my_cards_title">${_("My cards")}:</div>
                        <div id="my_cards" class="cards"></div>
                    </div>
                        
        `
        


        const gamePlayArea = document.getElementById("board_id");
        gamePlayArea.insertAdjacentHTML("beforeend", gameBoardHTML);

        this.setupStocks();

        },

        createStockForCards: function(page, element)
        {
            let stock = new ebg.stock();
            stock.create(page, element, CARD_WIDTH, CARD_HEIGHT);
            stock.image_items_per_row = CARDS_PER_ROW;

            return stock;
        },

        getStockCardType: function( card ) {
        let card_id;
        if (card.type == 1) {

            if(card.type_arg < 11)
            {
                card_id = parseInt(card.type_arg);
            }

            else if (card.type_arg == 11) {

                if(card.type_arg_2 == 1)
                {
                    card_id = 11;
                }

                else if(card.type_arg_2 == 2)
                {
                    card_id = 12;
                }
            }

            else if (card.type_arg > 11) {

                card_id = 1 + parseInt(card.type_arg);
            }
            
        } 
        
        else if (card.type == 2) {

            if(card.type_arg < 11)
            {
                card_id = 16 + parseInt(card.type_arg);
            }

            else if (card.type_arg == 11) {

                if(card.type_arg_2 == 1)
                {
                    card_id = 27;
                }

                else if(card.type_arg_2 == 2)
                {
                    card_id = 28;
                }
            }

            else if (card.type_arg > 11) {

                card_id = 17 + parseInt(card.type_arg);
            }
        } 

        else if (card.type == 3) {

            if(card.type_arg < 11)
            {
                card_id = 32 + parseInt(card.type_arg);
            }

            else if (card.type_arg == 11) {

                if(card.type_arg_2 == 1)
                {
                    card_id = 43;
                }

                else if(card.type_arg_2 == 2)
                {
                    card_id = 44;
                }
            }

            else if (card.type_arg > 11) {

                card_id = 33 + parseInt(card.type_arg);
            }
        }
        
        else if (card.type == 4) {
            
                card_id = 48 + parseInt(card.type_arg);
        } 


        return card_id

        },



    setupStocks: function() {

    // Stock pour la main du joueur
    this.handStock = this.createStockForCards(this, $('my_cards'));
    this.handStock.setSelectionMode(0);
    this.handStock.setOverlap(60, 0);
    for( var card_id = 1; card_id <= 53; card_id++) {
        this.handStock.addItemType(card_id, card_id, g_gamethemeurl + 'img/cards.jpg', card_id-1);
    }


    // Stock pour la table : pas de weight pour la table pour ne pas classer les cartes selon leur type.
    this.tableStock = this.createStockForCards(this, $('table_cards'));
    for( var card_id = 1; card_id <= 53; card_id++) {
        this.tableStock.addItemType(card_id, 0, g_gamethemeurl + 'img/cards.jpg', card_id-1);
    }
    this.tableStock.setSelectionMode(0);
    this.tableStock.use_vertical_overlap_as_offset = false;
    this.tableStock.vertical_overlap = -15;


    
    // Cards in player's hand
    Object.values(this.my_hand).forEach( card =>
    {
        const card_type = this.getStockCardType(card);
        this.handStock.addToStockWithId(card_type, card.id);
    } );
    this.handStock.updateDisplay();


    //Cards in table
    Object.values(this.table).forEach((card) => {

        console.log('this_table_card', card);


        const card_type = this.getStockCardType(card);
        this.tableStock.addToStockWithId(card_type, card.id);

        const player = this.players[card.location_arg];

        const card_div = document.getElementById('table_cards_item_' + card.id);
        dojo.place('<div class="player-title" style="color: #' + player.color + '">' + player.name + '</div>', card_div);
    });
    

},

        
///////////////////////////////////////////////////////////////////////////////// 
//       _   _       _   _  __ _           _   _                 
//      | \ | |     | | (_)/ _(_)         | | (_)                
//      |  \| | ___ | |_ _| |_ _  ___ __ _| |_ _  ___  _ __  ___ 
//      | . ` |/ _ \| __| |  _| |/ __/ _` | __| |/ _ \| '_ \/ __|
//      | |\  | (_) | |_| | | | | (_| (_| | |_| | (_) | | | \__ \
//      |_| \_|\___/ \__|_|_| |_|\___\__,_|\__|_|\___/|_| |_|___/
//                                                                 
/////////////////////////////////////////////////////////////////////////////////  

        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'playCard', this, "notif_playCard" );
            dojo.subscribe( 'endTurn', this, "notif_endTurn" );
        },  
        
        notif_playCard: function(notif) {
            
            const card = notif.args.card_play;
            console.warn(card.id)
            // Add the card to the table
            this.table[card.id] = card; // remplacer l'indice par table.length si ça sert à quelque chose...
            const div_id = this.player_id == card.location_arg ? `my_cards_item_${card.id}` : undefined;
            const card_type = this.getStockCardType(card);
            const player = this.players[card.location_arg];
            this.tableStock.addToStockWithId(card_type, card.id, div_id);
            const card_div = document.getElementById('table_cards_item_' + card.id);
            dojo.place('<div class="player-title" style="color: #' + player.color + '">' + player.name + '</div>', card_div);


            // Destroy the card for the current player
            if (this.player_id == card.location_arg) {

                this.handStock.removeFromStockById(card.id);
            }


        },

        notif_endTurn: function(notif) {
            const winner_id = notif.args.winner_id;
            this.tableStock.removeAllTo('overall_player_board_' + winner_id);
   
        },














   });             
});
