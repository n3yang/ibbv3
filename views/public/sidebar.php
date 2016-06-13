<?
use app\models\Offer;
use yii\helpers\Url;

$hots = Offer::findHot();
?>

            <div class="col-sm-12 col-md-3 sidebar-offcanvas" id="sidebar">

                <div class="list-group">
                    <a href="#" class="list-group-item active">关于我</a>
                    <p class="list-group-item">爱宝贝，一个提供母婴全网最惠商品信息的网站。由sixma创立，致力于让妈妈们更省心、放心、开心的购买母婴类商品。在这里，sixma希望与妈妈们互相交流、分享，一起陪宝宝度过最美的时光。
                    </p>
                </div>

                <div class="list-group">
                    <a href="#" class="list-group-item active">今日热门</a>
                    <?
                    foreach ($hots as $hot) {
                    ?>
                    <a href="<?=Url::toRoute(['offer/view', 'id'=>$hot->id])?>" class="list-group-item"><?=$hot->title?> <?=$hot->price?></a>
                    <? } // end foreach ?>
                </div>
            </div><!--/.sidebar-offcanvas-->