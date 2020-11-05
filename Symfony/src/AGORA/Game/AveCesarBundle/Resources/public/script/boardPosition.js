/**
 * Created by nathanpanchout on 24/05/2018.
 */

var boardPositions = initializePosition();
var boardTransition = getTransition();

function getCellRotation(caseName) {
    return boardPositions[caseName].deg;
}

function place(img, deg, coord) {
    var value = deg + "deg";
    img.css("-webkit-transform", 'rotate(' + value + ')');
    img.css("-moz-transform", 'rotate(' + value + ')');
    img.css("-ms-transform", 'rotate(' + value + ')');
    img.css("-o-transform", 'rotate(' + value + ')');
    img.css("transform", 'rotate(' + value + ')');
    img.css("left", coord.x);
    img.css("top", coord.y);

}

function getPositionMap(pos) {
    return boardPositions[pos];
}

function getInfosPawn(pos) {
    var i = getCellRotation(pos);
    var s = getPositionMap(pos);
}

function displayPawns(players) {
    for (var i in players) {
        var p = players[i];
        var char = $('<img>');
        var j = parseInt(i) + 1;
        char.attr('src', imagePath + "char" + j + ".png");
        char.addClass("char");
        var pos = p.position;

        var deg = getCellRotation(pos);
        var coord = getPositionMap(pos);
        place(char,deg,coord);
        char.appendTo("#gameBoard");
        players[i].pawn = char;
    }
}

function placePawn(player, pos) {
    var deg = getCellRotation(pos);
    var coord = getPositionMap(pos);
    place(player.pawn,deg,coord);
}

function displayCards(hand) {
    for (var i in hand) {
        var x = hand[i];
        var img = $('<img>');
        img.attr('src', imagePath + x + ".jpg");
        img.appendTo("#handCards");
        img.click(clickOnCard(i));
        handNode.push(img);
    }
    var dos = $('<img>');
    dos.attr('src', imagePath + "dos_carte.jpg");
    dos.appendTo("#deckCard");
}

function clickOnCard(i) {
    return function() {
        return selectCard(i);
    }
}

function playOnCell(cell) {
    move(cell, hand[selectedCard]);
    cardPlayed = selectedCard;
}

/**
 * Fonctioné appelé lorsqu'une carte de la main est selectionné
 * Met en surbrillance la carte.
 */
function selectCard(x) {
    var playableCard = canPlay();
    if (!playableCard[x]) {
        return ;
    }
    var deck = document.getElementById("handCards");
    if (selectedCard != null) {
        deck.childNodes[selectedCard].classList.remove("selectedCard");
    }
    deck.childNodes[x].classList.add("selectedCard");
    selectedCard = x;
}


/**
 * Met à jour l'affichage de la carte à la position donnée.
 * @var index de la carte à mettre à jour.
 */
function updateCard(index) {
    var deck = document.getElementById("handCards");
    var card = deck.childNodes[index];

    var x = hand[index];
    var img = $('<img>');
    img.attr('src', imagePath + x + ".jpg");
    img.addClass("#handCards");
    img.click(clickOnCard(index));

    deck.replaceChild(img.get(0), card);
    handNode[index] = img;
}


function updatePlayableCards() {
    var playableCards = canPlay();
    for (var i = 0; i < playableCards.length; i++) {
        if (!playableCards[i]) {
            handNode[i].addClass("greyCard");
            handNode[i].removeClass("selectedCard");
        } else {
            handNode[i].removeClass("greyCard");
        }
    }
    if (canPass()) {
        var deck = $('#handCards');
        var passButton = $('<div>');
        passButton.text("Click to pass");
        passButton.attr('id', "passButton");
        passButton.click(function () {
           pass();
           $('#passButton').remove();
        });
        if ($('#passButton').length == 0) {
            deck.append(passButton);
        }
    } else {
        $('#passButton').remove();
    }
}

function getTakenPosition() {
    var takenPosition = [];
    for (var p in players) {
        if (players[p].id != selfId) {
            takenPosition.push(players[p].position);
        }
    }
    return takenPosition;
}

function canPass() {
    var playableCard = canPlay();
    return !playableCard.includes(true);
}

function isStuckPosition(position) {
    var stuckPosition = ["7a", "17a", "25a"];
    return stuckPosition.includes(position);
}

function countOf(array, element) {
    var count = 0;
    for (var e in array) {
        if (array[e] == element) {
            count++;
        }
    }
    return count;
}

function canPlaySix() {
    return countOf(hand, "6") == hand.length && isStuckPosition(playerPosition);
}

function canPlay() {
    var result = [];
    // Que des 6
    if (isFirst() && canPlaySix()) {
        console.log("JE SUIS ICI okdpzokdzek");
        return [true, true, true];
    }
    //
    for (var card in hand) {
        if (hand[card] != 6 || !isFirst() ) {
            var tmp = canMove(playerPosition, getTakenPosition(), hand[card]);
            result.push(tmp);
        } else {
            result.push(false);
        }
    }
    return result;
}

/**
 * Si le deplacement est possible avec une certaine carte.
 * @param position
 * @param takenPosition
 * @param card
 * @returns {boolean}
 */
function canMove(position, takenPosition, card) {

    if (takenPosition.includes(position)) {
        return false;
    }

    if(card == 0) {
        return true;
    }

    var result = false;

    //Pour tout les champs du tableau
    for(var field in boardTransition[position]) {
        var nextPosition = boardTransition[position][field];
        result = result || canMove(nextPosition, takenPosition, card - 1);
    }
    return result;
}

function canMoveTo(position, targetPosition, takenPosition, card) {
    if (takenPosition.includes(position)) {
        return false;
    }
    if (position == targetPosition && card == 0) {
        return true;
    }
    if(card == 0) {
        return false;
    }

    var result = false;

    //Pour tout les champs du tableau
    for(var field in boardTransition[position]) {
        var nextPosition = boardTransition[position][field];
        result = result || canMoveTo(nextPosition, targetPosition, takenPosition, card - 1);
    }
    return result;
}

function isFirst() {
    return firstPlayer == selfId;
}
