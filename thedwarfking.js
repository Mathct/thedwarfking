/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * thedwarfking implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com>
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
            


            this.setupBoard();
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

         
            //// CONNECTIONS CLICK
            dojo.query(".stockitem").connect('onclick', this, 'onSelect' )
            dojo.query(".questcardmodal").connect('onclick', this, 'onSelect' )

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
                                if(args.buttons[nb] == "continue")
                                {
                                this.addActionButton( 'continue', _("Continue") ,'onOpButton', null, null, 'gray' );
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
            
                    <div id="round">
                    <div id="nb_round">${_("Round")}: ${this.no_round} / 7</div>
                    <div id="btn_round"><div class="icon1_btn"></div><div>/</div><div class="icon2_btn"></div></div>
                    </div>

                    <div id="table_cards_container" class="cards-container">
                        <div class="titre">${_("Cards played")}:</div>
                        <div id="table_cards" class="cards"></div>
                        
                    </div> 

                    
                    <div id="hand_container" class="cards-container">
                        <div class="titre" id="my_cards_title">${_("My hand")}:</div>
                        <div id="my_cards" class="cards"></div>
                    </div>

                    <div id="modal_round_container">
                        <div id="modal_round">
                        <div id="modal_section_1" class="modal_section"></div>
                        <div id="modal_section_2" class="modal_section"></div>
                        <div id="croix">X</div>
                        </div>
                    </div>

                    <div id="modal_players_container" class="hidden">
                        <div id="modal_players">
                        </div>
                    </div>
                        
        `
        
        
        const gamePlayArea = document.getElementById("board_id");
        gamePlayArea.insertAdjacentHTML("beforeend", gameBoardHTML);

        var choiceQuest = this.gamedatas.active_quest_card;
        if(choiceQuest[0].validate == 0)
        {
            var modal_round_container = document.getElementById("modal_round_container");
            modal_round_container.classList.remove("hidden");
            
        }
        else
        {
            var modal_round_container = document.getElementById("modal_round_container");
            modal_round_container.classList.add("hidden");
            
        }

        this.addEventListenerModal();


        var druide = this.gamedatas.druide_player;
        if((this.getCurrentPlayerId() == druide)&&(!this.isSpectator ))
        {
            this.addPlayersModal(this.players, druide);
        }
        

        if(this.isSpectator ) {
        
        dojo.addClass('hand_container', 'hidden');
        
        let board = document.getElementById("board_id");
        board.style.height = "700px";
        }

        this.setupSpecialModal(this.gamedatas.active_special_card);
        this.setupQuestModal(this.gamedatas.active_quest_card);
        this.setupStocks();

        },

        addPlayersModal: function(players, player_play)
        {
                        
            Object.values(players).forEach((player) => {
                if(player.id != player_play)
                {

                var parent = document.getElementById("modal_players");

                const modal_player = document.createElement("div");
                modal_player.id = 'modal_player_'+player.id; 
                modal_player.classList.add('modal_player');

                var avatar ='avatar_'+player.id;
                var avatarImage = document.getElementById(avatar);
                var avatarSrc = avatarImage.src;
                var newImage = document.createElement('img');
                newImage.src = avatarSrc;
                newImage.id = 'newavatar_'+player.id; 
                newImage.classList.add('newAvatar');
                modal_player.appendChild(newImage);


                const player_name = document.createElement("div");
                player_name.id = "modal_player_name_"+player.id;
                player_name.style.color = "#"+player.color;
                player_name.textContent = player.name;
                modal_player.appendChild(player_name);

                parent.appendChild(modal_player);

                }
            });

            var modal_players_container = document.getElementById("modal_players_container");
            modal_players_container.classList.remove("hidden");
            var round = document.getElementById("round");
            round.classList.add("index");


            dojo.query(".modal_player").connect('onclick', this, 'onSelect' )
            
        },

        removePlayersModal: function()
        {

            var parent = document.getElementById("modal_players_container");
            parent.remove(); 
            
            var round = document.getElementById("round");
            round.classList.remove("index");
            
            
        },
        
        addEventListenerModal: function() {
            var modal_round_container = document.getElementById("modal_round_container");
            var croix = document.getElementById("croix");
            var btn = document.getElementById("btn_round");

            // On stocke les handlers dans this pour pouvoir les enlever après
            this._croixHandler = () => {
                modal_round_container.classList.add("hidden");
            };

            this._btnHandler = () => {
                modal_round_container.classList.toggle("hidden");
            };

            croix.addEventListener("click", this._croixHandler);
            btn.addEventListener("click", this._btnHandler);
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
    this.handStock.use_vertical_overlap_as_offset = false;
    this.handStock.vertical_overlap = -5;
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

        console.warn('this_table_card', card);


        const card_type = this.getStockCardType(card);
        this.tableStock.addToStockWithId(card_type, card.id);

        const player = this.players[card.location_arg];

        const card_div = document.getElementById('table_cards_item_' + card.id);
        dojo.place('<div class="player-title" style="color: #' + player.color + '">' + player.name + '</div>', card_div);
    });


    //momie
    if(this.gamedatas.momie_id_table != null)
    {
        this.addMomieTable(this.gamedatas.momie_id_table, this.gamedatas.type_last_card_win, this.gamedatas.typearg_last_card_win);
    }
    else
    {
        this.addMomie(this.gamedatas.no_trick, this.gamedatas.momie_id, this.gamedatas.momie_player, this.gamedatas.type_last_card_win, this.gamedatas.typearg_last_card_win);
    }
    
    

},


addMomie: function(trick, momie_id, momie_player, type_last_card_win, typearg_last_card_win)
{
    var momie_last = document.getElementById('last_card_momie');
    if(momie_last)
    {
        momie_last.remove();
    }
    
    if(trick >= 2 && momie_id != null && momie_player == this.getCurrentPlayerId())
    {
    
        var momie = document.getElementById('my_cards_item_' + momie_id);
        var type = type_last_card_win;
        var type_arg = typearg_last_card_win;

        if(type == 1)
        {
            var variableY = 0;
            var color = 'vert';
        }

        if(type == 2)
        {
            var variableY = -100;
            var color = 'bleu';
        }

        if(type == 3)
        {
            var variableY = -200;
            var color = 'rouge';
        }

        if(type == 4)
        {
            var variableY = -300;
        }

        var variableX = (type_arg-1)*(-100);

        const newDiv = document.createElement('div');
        newDiv.id = 'last_card_momie';
        newDiv.classList.add('last_card_momie', color);
        newDiv.style.backgroundPositionX = variableX + '%';
        newDiv.style.backgroundPositionY = variableY + '%';
        momie.appendChild(newDiv);


    }
    
},


addMomieTable: function(momie_id, type_last_card_win, typearg_last_card_win)
{
    var momie_last = document.getElementById('last_card_momie');
    if(momie_last)
    {
        momie_last.remove();
    }
    
        
        var momie = document.getElementById('table_cards_item_' + momie_id);
        var type = type_last_card_win;
        var type_arg = typearg_last_card_win;

        if(type == 1)
        {
            var variableY = 0;
            var color = 'vert';
        }

        if(type == 2)
        {
            var variableY = -100;
            var color = 'bleu';
        }

        if(type == 3)
        {
            var variableY = -200;
            var color = 'rouge';
        }

        if(type == 4)
        {
            var variableY = -300;
        }

        var variableX = (type_arg-1)*(-100);

        const newDiv = document.createElement('div');
        newDiv.id = 'last_card_momie';
        newDiv.classList.add('last_card_momie', color);
        newDiv.style.backgroundPositionX = variableX + '%';
        newDiv.style.backgroundPositionY = variableY + '%';
        momie.appendChild(newDiv);


    
    
},

setupSpecialModal: function(data)
{
    /* SPECIAL CARD*/
    const parent = document.getElementById("modal_section_1");

    var type = data[0].type;
    var type_arg = data[0].type_arg;
    var type_arg_2 = data[0].type_arg_2;

    if(type == 4)
    {
        var variableX = (type_arg - 1)*(-100);
        var variableY = 0;
    }

    if(type == 1)
    {
        if(type_arg == 1)
        {
            var variableX = -500;
            var variableY = 0;

        }
        if((type_arg == 11)&&(type_arg_2 == 1))
        {
            var variableX = -600;
            var variableY = 0;
            
        }
        if((type_arg == 11)&&(type_arg_2 == 2))
        {
            var variableX = 0;
            var variableY = -100;
            
        }       
        
    }

    if(type == 2)
    {
        if(type_arg == 1)
        {
            var variableX = -100;
            var variableY = -100;

        }
        if((type_arg == 11)&&(type_arg_2 == 1))
        {
            var variableX = -200;
            var variableY = -100;
            
        }
        if((type_arg == 11)&&(type_arg_2 == 2))
        {
            var variableX = -300;
            var variableY = -100;
            
        } 
    
    }

    if(type == 3)
    {
        if(type_arg == 1)
        {
            var variableX = -400;
            var variableY = -100;

        }
        if((type_arg == 11)&&(type_arg_2 == 1))
        {
            var variableX = -500;
            var variableY = -100;
            
        }
        if((type_arg == 11)&&(type_arg_2 == 2))
        {
            var variableX = -600;
            var variableY = -100;
            
        } 
        
    }

    const enfantspecialcard = document.createElement("div");
    enfantspecialcard.id = "specialcardmodal";
    enfantspecialcard.className = "specialcardmodal";
    enfantspecialcard.style.backgroundPositionX = variableX + "%";
    enfantspecialcard.style.backgroundPositionY = variableY + "%";

    parent.appendChild(enfantspecialcard);


},


setupQuestModal: function(data)
{
    /* QUEST CARD */

    var type = data[0].rand;
    var validate = data[0].validate;

    if(type >=1 && type <=10)
    {

        var variableX = -100*(Number(type) - 1);
        var variableY = 0;

    }

    if(type >=11 && type <=20)
    {

        var variableX = -100*(Number(type) - 11);
        var variableY = -200;

    }

    const parent2 = document.getElementById("modal_section_2");

    if(validate == 0 || validate == 1)
    {
        const enfantquestcard1 = document.createElement("div");
        enfantquestcard1.id = "questcardmodal1";
        enfantquestcard1.className = "questcardmodal";
        enfantquestcard1.style.backgroundPositionX = variableX + "%";
        enfantquestcard1.style.backgroundPositionY = variableY + "%";
        parent2.appendChild(enfantquestcard1);
    }

    if(validate == 0 || validate == 2)
    {
        const enfantquestcard2 = document.createElement("div");
        enfantquestcard2.id = "questcardmodal2";
        enfantquestcard2.className = "questcardmodal";
        enfantquestcard2.style.backgroundPositionX = variableX + "%";
        enfantquestcard2.style.backgroundPositionY = (variableY-100) + "%";
        parent2.appendChild(enfantquestcard2);
    }



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
            dojo.subscribe( 'ChoiceQuest', this, "notif_ChoiceQuest" );
            dojo.subscribe( 'majRound', this, "notif_majRound" );
            dojo.subscribe( 'drawCards', this, "notif_drawCards" );
            dojo.subscribe( 'majModal', this, "notif_majModal" );
            dojo.subscribe( 'momieChange', this, "notif_momieChange" );
            dojo.subscribe( 'removeCards', this, "notif_removeCards" );
            dojo.subscribe( 'showPlayersModal', this, "notif_showPlayersModal" );
            dojo.subscribe( 'removePlayersModal', this, "notif_removePlayersModal" );

            

            this.notifqueue.setSynchronous( 'endTurn', 1000 );
            
        },  
        
        notif_playCard: function(notif) {
            
            const card = notif.args.card_play;
            // Add the card to the table
            this.table[card.id] = card; // remplacer l'indice par table.length si ça sert à quelque chose...
            const div_id = this.player_id == card.location_arg ? `my_cards_item_${card.id}` : undefined;
            const card_type = this.getStockCardType(card);
            const player = this.players[card.location_arg];
            this.tableStock.addToStockWithId(card_type, card.id, div_id);
            const card_div = document.getElementById('table_cards_item_' + card.id);
            dojo.place('<div class="player-title" style="color: #' + player.color + '">' + player.name + '</div>', card_div);

            if (notif.args.momie == 1)
            {
                this.addMomieTable(card.id, notif.args.type_last_card_win, notif.args.typearg_last_card_win);
            }

            // Destroy the card for the current player
            if (this.player_id == card.location_arg) {

                this.handStock.removeFromStockById(card.id);
            }


        },

        notif_removeCards: async function(notif) {
            // chaque joueur reçoît des cartes lors de la nouvelle manche
            Object.values(notif.args.cards).forEach(card => {
                // Destroy cards for the current player
                if (this.player_id == card.location_arg) {

                    this.handStock.removeFromStockById(card.id);
                }
            });
        },

        notif_endTurn: function(notif) {
            const winner_id = notif.args.winner_id;
            setTimeout(() => {
            this.tableStock.removeAllTo('overall_player_board_' + winner_id);
            }, "1000");
   
        },

        notif_ChoiceQuest: function(notif) {
            const questremove = notif.args.questremove;
            var card = document.getElementById('questcardmodal'+questremove);
            var modal_round_container = document.getElementById("modal_round_container");
            modal_round_container.classList.add("hidden");

            card.remove();
 
        },

        notif_majRound: function(notif) {

            document.getElementById('nb_round').innerText = _('Round ')+': ' + notif.args.new_round + ' / 7';
            
        },

        notif_drawCards: async function(notif) {
            // chaque joueur reçoît des cartes lors de la nouvelle manche
            Object.values(notif.args.cards).forEach(card => {
                const card_type = this.getStockCardType(card);
                this.handStock.addToStockWithId(card_type, card.id, undefined);
            });
        },


        notif_majModal: function(notif) {
            const special = document.getElementById("specialcardmodal");
            special.remove();
            const quest1 = document.getElementById("questcardmodal1");
            if(quest1){
                quest1.remove();
            }
            const quest2 = document.getElementById("questcardmodal2");
            if(quest2)
            {
                quest2.remove();
            }

            
            this.setupSpecialModal(notif.args.active_special_card);
            this.setupQuestModal(notif.args.active_quest_card);
            

            var modal_round_container = document.getElementById("modal_round_container");
            var croix = document.getElementById("croix");
            modal_round_container.classList.remove('hidden');
            croix.classList.add('hidden');

            dojo.query(".questcardmodal").connect('onclick', this, 'onSelect' )
            dojo.query(".stockitem").connect('onclick', this, 'onSelect' )
 
        },

        notif_momieChange: function(notif) {
            this.addMomie(notif.args.trick, notif.args.momie_id, notif.args.momie_player, notif.args.type_last_card_win, notif.args.typearg_last_card_win)
   
        },

        notif_showPlayersModal: function(notif) {
            this.addPlayersModal(notif.args.players, notif.args.player);
        },

        notif_removePlayersModal: function(notif) {
            this.removePlayersModal();
        },


        


        














   });             
});
