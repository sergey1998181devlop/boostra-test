function img(asset_name)
{
    return 'design/boostra_mini_norm/img/loan_game/' + asset_name;
}

function isMobile()
{
    let check = false;
    (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
    return check;
}

let GAME_SPEED = 0.8;
const GAME_WIDTH = 512;
const GAME_HEIGHT = 160;

const GAME_BACKGROUNDS = 3;
const GAME_ROADS = 5;
const ROAD_SIZE = GAME_WIDTH / 2;
const GAME_OBSTACLES = 4;

class LoanGame extends Phaser.Scene
{
    preload ()
    {
        //  Фон
        this.load.image('background', img('background.png'));
        this.load.image('road', img('road.png'))

        //  Препятствия
        this.load.image('tree', img('tree.png'))
        this.load.image('snowman', img('snowman.png'))
        this.load.image('collision', img('collision.png'))

        //  Игрок
        this.load.spritesheet('player',
            img('player.png'),
            { frameWidth: 32, frameHeight: 64 }
        );

        //  Монетка
        this.load.image('coin', img('coin.png'))
        this.load.image('coin_effect', img('coin_effect.png'))

        this.load.image('start_btn', img('start.png'))
    }

    create ()
    {
        this.gameInProcess = false;

        //  Фон
        this.background = [];
        for (let i = 0; i < GAME_BACKGROUNDS; i++)
        {
            this.background[i] = this.add.sprite(GAME_WIDTH * i, 0, 'background').setOrigin(0, 0);
            this.background[i].setDisplaySize(GAME_WIDTH, 128);
        }

        this.road = [];
        for (let i = 0; i < GAME_ROADS; i++)
        {
            this.road[i] = this.add.sprite(ROAD_SIZE * i, 0, 'road').setOrigin(0, 0);
            this.road[i].setDisplaySize(ROAD_SIZE, 128);
            this.road[i].y = GAME_HEIGHT - 128;
        }

        //  Игрок
        this.anims.create({
            key: 'idle',
            frames: [ { key: 'player', frame: 0 } ],
            frameRate: 20,
        });
        this.anims.create({
            key: 'run',
            frames: this.anims.generateFrameNumbers('player', { start: 0, end: 1 }),
            frameRate: 10,
            repeat: -1
        });
        this.player = this.physics.add.sprite(30, 156, 'player').setOrigin(0.5, 1);
        this.player.setDisplaySize(32, 64);
        this.player.anims.play('run', true);
        this.player.setBounce(0.25);
        this.player.setCollideWorldBounds(true);
        this.player.setDepth(100);

        //  Земля
        this.ground = this.physics.add.staticGroup();
        this.ground.create(-100, 158, 'collision').setOrigin(0, 1).setDisplaySize(800, 2).refreshBody();

        this.physics.add.collider(this.player, this.ground);

        //  Препятствия
        this.obstacles = [];
        for (let i = 0; i < GAME_OBSTACLES; i++)
        {
            let sprite = ((i+1) % 3 === 0) ? 'snowman' : 'tree'; // Каждое третье препятствие - снеговик
            this.obstacles[i] = this.add.sprite(this.generateObstacleX(), 150, sprite)
                .setOrigin(0.5, 1)
                .setDisplaySize(20, 44);
        }

        //  Управление
        this.input.on('pointerdown', () => { this.onClick(); });
        this.input.keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.SPACE).on('down', () => { this.onClick(); });

        //  Очки
        this.scoreText = this.add.text(16, 16, 'Очки: 0', { fontSize: '24px', fill: '#000' });
        this.scoreText.visible = false;

        this.coin = this.add.sprite(GAME_WIDTH + 60, 80, 'coin').setDisplaySize(18, 18).setDepth(1);
        this.coinEffect = this.add.particles(0, 0, 'coin_effect', {
            speed: 50,
            scale: { start: 1, end: 0 },
            blendMode: 'NORMAL',
            angle: { min: -10, max: 10 },
            frequency: 100
        }).startFollow(this.coin, 10).setDepth(0);

        this.startBtn = this.add.sprite(GAME_WIDTH / 2, GAME_HEIGHT / 2 + 15, 'start_btn')
            .setOrigin(0.5, 0.5)
            .setDisplaySize(101, 40);

        this.startText = this.add.text(GAME_WIDTH / 2, GAME_HEIGHT / 2 - 40, 'Набери 5 000 очков\nи получи промокод', { fontSize: '24px', fill: '#000', align: 'center' }).setOrigin(0.5);
        this.endText = this.add.text(GAME_WIDTH / 2, GAME_HEIGHT / 2 - 30, '', { fontSize: '24px', fill: '#000' }).setOrigin(0.5);
        this.endText.visible = false;
        this.hintText = this.add.text(GAME_WIDTH / 2, GAME_HEIGHT / 2 - 30, 'Нажмите, чтобы перепрыгнуть препятствие!', { fontSize: '18px', fill: '#000' })
            .setOrigin(0.5);
        this.hintText.visible = false;

        this.debugText = this.add.text(GAME_WIDTH / 2, 20, '', { fontSize: '16px', fill: '#FF0000' }).setOrigin(0.5);
    }

    update (time, delta)
    {
        this.delta = delta * 0.1;

        if (this.gameInProcess)
        {
            //this.debugText.setText();
            this.updateBackground();
            this.updateRoad();
            this.updateObjects();
        }
        else
        {
            this.player.anims.play('idle', true);
        }
    }

    //  Обновление фона с ёлками
    updateBackground ()
    {
        for (let i = 0; i < GAME_BACKGROUNDS; i++)
        {
            this.background[i].x -= GAME_SPEED * 0.65 * this.delta;
            if (this.background[i].x <= -(GAME_WIDTH - 1))
                for (let i = 0; i < GAME_BACKGROUNDS; i++)
                    this.background[i].x = GAME_WIDTH * i;
        }
    }

    //  Обновление фона дороги
    updateRoad ()
    {
        for (let i = 0; i < GAME_ROADS; i++)
        {
            this.road[i].x -= GAME_SPEED * this.delta;
            if (this.road[i].x <= -(ROAD_SIZE - 1))
                for (let i = 0; i < GAME_ROADS; i++)
                    this.road[i].x = ROAD_SIZE * i;
        }

        this.roadLen += GAME_SPEED * this.delta;
        if (this.roadLen > 100)
        {
            this.setScore(this.score + 1);
            this.roadLen = 0;
        }
    }

    //  Обновление игрока, препятствий и подарков
    updateObjects ()
    {
        //  Игрок
        if (this.player.body.touching.down)
        {
            this.player.anims.timeScale = GAME_SPEED < 1 ? GAME_SPEED : 1;
            this.player.anims.play('run', true);
        }
        else
            this.player.anims.play('idle', true);

        //  Препятствия
        for (let i = 0; i < GAME_OBSTACLES; i++)
        {
            this.obstacles[i].x -= GAME_SPEED * this.delta;

            if (this.obstacleInPlayerCollider(this.obstacles[i]))
                this.endGame();

            if (this.obstacles[i].x < this.player.x)
            {
                if (!this.obstacles[i].counted && this.obstacles[i].x > 0)
                {
                    this.obstacles[i].counted = true;
                    this.setScore(this.score + 5);
                }

                if (this.obstacles[i].x < -30)
                {
                    this.obstacles[i].x = this.generateObstacleX();
                    this.obstacles[i].counted = false;
                }
            }
        }

        //  Монета
        this.coin.x -= GAME_SPEED * 1.4 * this.delta;
        if (this.coin.x < 0)
        {
            this.lastCoinSpawn += this.delta;
            if (this.lastCoinSpawn > 1300)
            {
                this.lastCoinSpawn = 0;
                this.coin.x = GAME_WIDTH + 60;
            }
        }
        else
        {
            if (this.coinInPlayerCollider())
            {
                this.coin.x = -50;
                this.setScore(this.score + 20);
            }
        }
    }

    generateObstacleX ()
    {
        let obstaclesOnScreen = 0;
        let maxX = 0;
        this.obstacles.forEach((obj) => {
           if (obj.x < (GAME_WIDTH + 30) && obj.x > 0)
               obstaclesOnScreen++;
           maxX = (obj.x > maxX) ? obj.x : maxX;
        });
        if (obstaclesOnScreen >= 3)
            return -300;

        if (maxX > GAME_WIDTH - 40 - GAME_SPEED * 40)
            return -300;

        return GAME_WIDTH + (Math.random() * (400 - 150) + 150);
    }

    obstacleInPlayerCollider (obj)
    {
        if (obj.x > (this.player.x + 4))
            return false;
        if (obj.x < (this.player.x - 4))
            return false;
        if (this.player.y < (obj.y - 40))
            return false;
        return true;
    }

    coinInPlayerCollider ()
    {
        if (this.coin.x > (this.player.x + 4))
            return false;
        if (this.coin.x < (this.player.x - 4))
            return false;
        if (this.player.y - 30 > this.coin.y + 20)
            return false;
        //if (this.player.y - 60 < this.coin.y - 10)
        //   return false
        return true;
    }

    setScore (newValue)
    {
        this.score = newValue;
        this.scoreText.setText('Очки: ' + this.score);

        this.updateGameSpeed();

        if (newValue > 2 && newValue < 10)
            this.hintText.visible = true;
        else
            this.hintText.visible = false;
    }

    updateGameSpeed ()
    {
        let newSpeed = this.score * 0.03;
        newSpeed = (newSpeed > 0.85) ? newSpeed : 0.85;

        //if (isMobile())
        //    newSpeed += 1;

        if (newSpeed > 2)
        {
            newSpeed *= 0.5;
            if (newSpeed < 2)
                newSpeed = 2;
            if (newSpeed > 2.3)
                newSpeed = 2.3;

            if (this.score > 400)
            {
                let maxSpeed = 2.5
                let fromScore = 400;
                if (this.score > 800)
                {
                    fromScore = 800;
                    maxSpeed = 3;
                }
                else if (this.score > 700)
                {
                    fromScore = 700;
                    maxSpeed = 3.5;
                }
                else if (this.score > 600)
                {
                    fromScore = 600;
                    maxSpeed = 3;
                }
                else if (this.score > 500)
                {
                    fromScore = 500;
                    maxSpeed = 2.8;
                }
                newSpeed = 3 + (this.score - fromScore) * 0.01;
                newSpeed = (newSpeed > maxSpeed) ? maxSpeed : newSpeed;
            }

        }
        GAME_SPEED = newSpeed;
    }

    onClick ()
    {
        if (this.gameInProcess && (this.player.body.touching.down || this.player.y > 150))
            this.player.setVelocityY(-300);
        else if (!this.gameInProcess)
            this.startGame();
    }

    startGame ()
    {
        this.gameInProcess = true;
        this.setScore(0);
        this.roadLen = 0;

        this.lastCoinSpawn = 0;
        this.coin.x = GAME_WIDTH + 1250;

        for (let i = 0; i < GAME_OBSTACLES; i++)
            this.obstacles[i].x = this.generateObstacleX();

        this.startText.visible = false;
        this.scoreText.visible = true;
        this.endText.visible = false;
        this.startBtn.visible = false;

        this.metric_id = 0;
        $.ajax({
            url: 'ajax/loan_game.php?action=metric_create',
            data: {
                is_mobile: isMobile() ? 1 : 0,
            },
            success: (response) => { if (response) this.metric_id = response.metric_id }
        });
    }

    endGame ()
    {
        this.gameInProcess = false;

        this.hintText.visible = false;
        this.scoreText.visible = false;
        this.endText.visible = true;

        this.endText.setText('Заработано очков: ' + this.score + '\nНажмите, чтобы повторить.')

        if (this.metric_id && this.metric_id !== 0)
        {
            $.ajax({
                url: 'ajax/loan_game.php?action=metric_update',
                data: {
                    metric_id: this.metric_id,
                    score: this.score,
                }
            });
        }
    }
}

const gameConfig = {
    type: Phaser.AUTO,
    scene: LoanGame,
    physics: {
        default: 'arcade',
        arcade: {
            gravity: { y: 600 }
        }
    },
    scale: {
        mode: Phaser.Scale.FIT,
        parent: 'game',
        width: GAME_WIDTH,
        height: GAME_HEIGHT
    },
};

const game = new Phaser.Game(gameConfig);