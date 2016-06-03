<?
use app\models\Offer;
use yii\helpers\Url;

$hots = Offer::findHot();
?>

            <div class="col-sm-12 col-md-3 sidebar-offcanvas" id="sidebar">

                <div class="list-group">
                    <a href="#" class="list-group-item active">GOnggao</a>
                    <p class="list-group-item">即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些
                    <p>
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