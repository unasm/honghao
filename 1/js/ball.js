/**
 * ���Ǹ����������Ϸ
 *    @name:       sai.js
 *    @author:     unasm<1264310280@qq.com>
 *    @since :     2014-03-22 09:22:06
 */
window.onload = function () {
    var doc = document;
    //���ӵĸ߶ȺͿ�ȣ��ߵĸ߶ȺͿ��
    var boxHeight = 300 , boxWidth = 400 ;
    var moveLength = 1;//ÿ���ƶ�3��px,������ʾ�������̶���ȷ��
    //ָ��������
    var ctx = doc.getElementById('cvs').getContext('2d');
    //�Գ��ؽ��г�ʼ��
    var table =  new function init() {
        var $this = this;
        var stxy = 0;
        //linewidth ָ�������ߵļ�������ֵ
        ctx.wallWdith = 10;//��Χ�ߵĿ��
        //�������ص�xyֵ
        $this.innerStartx = stxy + ctx.wallWdith / 2;
        $this.innerStarty = stxy + ctx.wallWdith / 2;
        $this.innerWidth = boxWidth - ctx.wallWdith ;
        $this.innerHeight = boxHeight - ctx.wallWdith ;
        //����Ե��������x ��y��Χ

        ctx.fillStyle = "green";
        ctx.strokeRect(stxy,stxy, boxWidth , boxHeight);
        ctx.fillRect($this.innerStartx , $this.innerStarty , $this.innerWidth , $this.innerHeight );
    };
    var slowrate = 1;
    /**
     * ����������Ľӿ�positonx ,positony cntTime ��ʾ������xy����;����ƶ���Ҫ��õ�ʱ��
     * cnttime ��ʶÿ���ƶ�length ��Ҫ��ʱ��
     * @param {int} speed  ��������Ҫ��ʱ��
     * @param {int} rad     �Ƕ�
     * @param {int} positonx  �������x
     * @param {int} positony   �������y
     */
    function ball(speed ,rad ,positonx ,positony) {
        //��İ뾶��С
        var thisBall = this;
        thisBall.ballrad = 10;
        thisBall.positonx = positonx;
        thisBall.positony = positony;
        //ballmaxx �����ĸ�������ָ���������ܴﵽ�ķ�Χ
        var ballminX =  table.innerStartx + thisBall.ballrad;
        var ballmaxX =  table.innerWidth - thisBall.ballrad / 2;
        var ballminY = table.innerStarty + thisBall.ballrad ;
        var ballmaxY = table.innerHeight - thisBall.ballrad / 2;
        thisBall.moveCheck = function calluate(){
            var spx = Math.sin(rad) * moveLength;
            var spy = Math.cos(rad) * moveLength;
            thisBall.cntTime = Math.round(moveLength * 1000 / speed);
            speed -= slowrate;//���������˶�
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
        //���㲢�ҳ�ʼ��cntTime,
        //���⿪ʼ���ڻ�����ڲ���������������bug
        positonx = Math.max(ballminX , positonx);
        positonx = Math.min(ballmaxX ,positonx);
        positony = Math.max(positony, ballminY);
        positony = Math.min(positony, ballmaxY);
        // ������һ����Ҫ��ʱ��
    }
    var balllist = [] , timePiece = 2;
    balllist[0] = new ball(1000 , 0.45, 50,70);
    balllist[1] = new ball(400 , -0.85, 50,90);
    //ÿ��һ����ʱ�䣬���һ��,����������ػ�
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
    //�������˶����¼�����
    /*
    doc.getElementById('ball').onsubmit= function (event) {
        event = event || window.event;
        event.preventDefault();
        spx = -1;
        spy = 1;
    };
    */
}

