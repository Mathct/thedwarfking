<?php

$this->_CARDS = [
    110 => [
        "name" => clienttranslate('Druid'),
        "text" => clienttranslate("After this trick, swap your hand for any other player's. This card has no effect if played in the final trick.")
    ],
    150 => [
        "name" => clienttranslate('Musician'),
        "text" => clienttranslate('The player who wins this card becomes the dealer for the next deal.')
    ],
    1111 => [
        "name" => clienttranslate('Flag Bearer'),
        "text" => clienttranslate("<b>+3</b> <div class='log_pv title=''></div> for the player who wins this card.")
    ],
    1112 => [
        "name" => clienttranslate('Tinkerer'),
        "text" => clienttranslate("<b>+3</b> <div class='log_pv title=''></div> for the player who wins the next trick. This card has no effect if played in the final trick.")
    ],
    210 => [
        "name" => clienttranslate('Joker'),
        "text" => clienttranslate("<b>+3</b> <div class='log_pv title=''></div> if you play this card during the final trick, whether you win it or not.")
    ],
    250 => [
        "name" => clienttranslate('Musician'),
        "text" => clienttranslate('Choose the Quest for this deal.')
    ],
    2111 => [
        "name" => clienttranslate('Flag Bearer'),
        "text" => clienttranslate('After this trick, choose whether all players must give their hand to the player on their left or right.')
    ],
    2112 => [
        "name" => clienttranslate('Enchanter'),
        "text" => clienttranslate("The player who wins this card doubles <div class='log_pv title=''></div> they win or lose during this deal.")
    ],
    310 => [
        "name" => clienttranslate('Scout'),
        "text" => clienttranslate("<b>+1</b> <div class='log_pv title=''></div> for each <b>J</b>, <b>Q</b>, <b>K</b> and <b>A</b> played in the same trick as this card, whether you win it or not.")
    ],
    350 => [
        "name" => clienttranslate('Musician'),
        "text" => clienttranslate('Lead the first trick with any card.')
    ],
    3111 => [
        "name" => clienttranslate('Shaman'),
        "text" => clienttranslate("<b>-3</b> <div class='log_pv title=''></div> for the player who wins this card.")
    ],
    3112 => [
        "name" => clienttranslate('Flag Bearer'),
        "text" => clienttranslate("Reveal this card right after <b>Choose a Quest</b>. Take 2 random cards from any player's hand, then give them any 2 cards from your hand.")
    ],
    410 => [
        "name" => clienttranslate('Dragon'),
        "text" => clienttranslate('You can always play the <b>Dragon</b>, even if you have cards of the required suit. You win the trick, unless someone plays an <b>A</b>. If A of the suit led is played, it wins the trick. If it is not played, the last A played in that trick wins it. If you lead a trick with Dragon, the suit led is that of the next card played.')
    ],
    420 => [
        "name" => clienttranslate('Mummy'),
        "text" => clienttranslate('The <b>Mummy</b> copies the winning card from the previous trick. It has the same color and value. Cannot be played on the first trick of a round.')
    ],
    430 => [
        "name" => clienttranslate('Puppeteer'),
        "text" => clienttranslate('The <b>Puppeteer</b> has the same color as the previous card played, though its value is considered to be slightly higher. You cannot start the first trick of a round with Puppeteer.')
    ],
    440 => [
        "name" => clienttranslate('Sorcerer'),
        "text" => clienttranslate('Play this card between two tricks: choose a card from the previous trick, add it to your hand, and replace it with the <b>Sorcerer</b>.')
    ],
    450 => [
        "name" => clienttranslate('Excalibur'),
        "text" => clienttranslate('You can only play <b>Excalibur</b> if you do not have the required suit. You win the trick.')
    ]
    
];

$this->_QUESTS = [
    11 => [
        "name" => clienttranslate(''),
        "text" => clienttranslate("")
    ],
    12 => [
        "name" => clienttranslate(''),
        "text" => clienttranslate("")
    ],

];