/**
 * 这是个碰碰球的游戏
 *    @name:       sai.js
 *    @author:     unasm<1264310280@qq.com>
 *    @since :     2014-03-22 09:22:06
 */
window.onload = function () {
    var doc = document;
    //盒子的高度和宽度，边的高度和宽度
    var boxHeight = 300 , boxWidth = 400 ;
    var moveLength = 1;//每次移动3个px,根据显示的流畅程度来确定
    //指整个画布
    var ctx = doc.getElementById('cvs').getContext('2d');
    //对场地进行初始化
    var table =  new function init() {
        var $this = this;
        var stxy = 0;
        //linewidth 指的是两边的加起来的值
        ctx.wallWdith = 10;//周围边的宽度
        //整个场地的xy值
        $this.innerStartx = stxy + ctx.wallWdith / 2;
        $this.innerStarty = stxy + ctx.wallWdith / 2;
        $this.innerWidth = boxWidth - ctx.wallWdith ;
        $this.innerHeight = boxHeight - ctx.wallWdith ;
        //球可以到达的最大的x ，y范围

        ctx.fillStyle = "green";
        ctx.strokeRect(stxy,stxy, boxWidth , boxHeight);
        ctx.fillRect($this.innerStartx , $this.innerStarty , $this.innerWidth , $this.innerHeight );
    };
    var slowrate = 1;
    /**
     * 有三个对外的接口positonx ,positony cntTime 表示所处的xy坐标和距离移动需要多久的时间
     * cnttime 标识每次移动length 需要的时间
     * @param {int} speed  球体所需要的时间
     * @param {int} rad     角度
     * @param {int} positonx  球的坐标x
     * @param {int} positony   球的坐标y
     */
    function ball(speed ,rad ,positonx ,positony) {
        //球的半径大小
        var thisBall = this;
        thisBall.ballrad = 10;
        thisBall.positonx = positonx;
        thisBall.positony = positony;
        //ballmaxx 下面四个变量是指球中心所能达到的范围
        var ballminX =  table.innerStartx + thisBall.ballrad;
        var ballmaxX =  table.innerWidth - thisBall.ballrad / 2;
        var ballminY = table.innerStarty + thisBall.ballrad ;
        var ballmaxY = table.innerHeight - thisBall.ballrad / 2;
        thisBall.moveCheck = function calluate(){
            var spx = Math.sin(rad) * moveLength;
            var spy = Math.cos(rad) * moveLength;
            thisBall.cntTime = Math.round(moveLength * 1000 / speed);
            speed -= slowrate;//先做匀速运动
            if(speed <0)speed = 0;
            if( (thisBall.positonx + spx > ballmaxX) ||( thisBall.positonx + spx < ballminX)){
                spx = - spx;
            }
            if( (thisBall.positony + spy > ballmaxY ) || (thisBall.positony + spy < ballminY)){
                spy = -spy;
            }
            rad = Math.atan(spy / spx);
            if(spy < 0){
                rad +=Math.PI;
            }
           thisBall.positonx += spx;
           thisBall.positony += spy;
        };
        thisBall.moveCheck();
        //计算并且初始化cntTime,
        //避免开始不在活动区域内部的情况，以免出现bug
        positonx = Math.max(ballminX , positonx);
        positonx = Math.min(ballmaxX ,positonx);
        positony = Math.max(positony, ballminY);
        positony = Math.min(positony, ballmaxY);
        // 计算下一步需要的时间
    }
    var balllist = [] , timePiece = 2;
    balllist[0] = new ball(1000 , 0.45, 50,70);
    balllist[1] = new ball(400 , -0.85, 50,90);
    //每隔一定的时间，检查一次,并对球进行重绘
    setInterval(function() {
        ctx.clearRect(table.innerStartx , table.innerStarty , table.innerWidth , table.innerHeight);
        for(var i = 0,len = balllist.length ; i < len;i++){
            balllist[i].cntTime -=timePiece;
            if(balllist[i].cntTime <= 0){
                balllist[i].moveCheck();
            }
            ctx.beginPath();
            ctx.arc(balllist[i].positonx, balllist[i].positony,balllist[i].ballrad , 0 , Math.PI * 2 , true);
            ctx.fill();
        }

    }, timePiece);
    //激发球运动的事件处理
    /*
    doc.getElementById('ball').onsubmit= function (event) {
        event = event || window.event;
        event.preventDefault();
        spx = -1;
        spy = 1;
    };
    */
}

