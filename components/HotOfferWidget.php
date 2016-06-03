<?
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class HotOfferWidget extends Widget
{
    public $message;

    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = 'Hello World';
        }
    }

    public function run()
    {
        return Html::encode($this->message);
    }
}