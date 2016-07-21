<?
use app\models\Offer;
use yii\helpers\Url;
use yii\helpers\Html;

$hots = Offer::findHot();
?>

            <div class="col-sm-12 col-md-3 sidebar-offcanvas" id="sidebar">

                <div class="list-group">
                    <a href="#" class="list-group-item active">关于我</a>
                    <p class="list-group-item">欢迎来到我的小站，我是Sixma。成为孩子的父母之后切身体会到育儿不易，国内环境又让人忧心忡忡，雾霾，假奶粉，过期疫苗等等，
                        一个个事件无情的打击着每一位家长疲惫的心灵。无论生活如何艰难，作为一位孩子的父母，还是要尽我所能给他们最好的。好货虽贵，幸好常有优惠。我建此站的初衷，
                        就是希望通过搜罗一些实实在在的优惠，帮助大家节省一些时间、金钱和精力。也欢迎各位联系我与大家一起分享促销信息和购买经验（仅限个人分享，拒绝任何商业合作，
                        谢谢）。
                    </p>
                    <!-- <p>爱宝贝，个人小站，记录收集到的商品优惠信息，方便自己购买。</p> -->
                </div>

                <div class="list-group hot-row">
                    <a href="#" class="list-group-item active">今日热门</a>
                    <?
                    foreach ($hots as $hot) {
                    ?>

                    <a href="<?=Url::toRoute(['offer/view', 'id'=>$hot->id])?>" class="list-group-item">
                        <img src="<?=$hot->getThumbUrl()?>" class="img-responsive hidden-xs" alt="<?= Html::encode($hot->title); ?>" /> <?=$hot->title?> <span class="price"><?=$hot->price?></span>
                    </a>
                    <? } // end foreach ?>
                </div>
            </div><!--/.sidebar-offcanvas-->