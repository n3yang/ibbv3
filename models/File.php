<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "file".
 *
 * @property string $id
 * @property string $name
 * @property string $path
 * @property string $mime
 * @property integer $size
 * @property string $md5
 * @property integer $user_id
 * @property string $created_at
 * @property string $updated_at
 */
class File extends \yii\db\ActiveRecord
{


    
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    /**
     * @var UploadedFile
     */
    public $upfile;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['path'], 'required', 'on' => self::SCENARIO_UPDATE],
            [['size', 'user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['name', 'path', 'mime'], 'string', 'max' => 200],
            [['md5'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'PK',
            'name' => '名称',
            'path' => '路径',
            'mime' => 'MIME类型',
            'size' => '文件大小',
            'md5' => 'md5 hash',
            'user_id' => 'User ID',
            'created_at' => '创建时间',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                // 'attributes' => [
                //     ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                //     ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                // ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['name', 'user_id', 'created_at', 'upfile'];
        $scenarios[self::SCENARIO_UPDATE] = ['name', 'path', 'mime', 'size', 'md5', 'user_id', 'upfile'];

        return $scenarios;
    }

    /**
     * upload file with $this->upfile
     * @return boolean true or false
     */
    public function upload()
    {
        // checking extension
        if ( in_array($this->upfile->extension, Yii::$app->params['uploadFileExtensions']) ) {

            $filename = $this->upfile->baseName . '.' . $this->upfile->extension;
            if ( file_exists( self::getUploadPath() . '/' . $filename ) ) {
               $filename = $this->upfile->baseName . '-' . hash('crc32b', microtime(true)) . '.' . $this->upfile->extension;
            }
            $targetFile = self::getUploadPath() . '/' . $filename;
            $this->upfile->saveAs($targetFile);

            // set file infomation
            $this->path = date('Y') . '/' . date('m') . '/' . $filename;
            $this->name = $this->upfile->baseName;
            $this->size = $this->upfile->size;
            $this->mime = $this->upfile->type;
            $this->md5  = md5_file($targetFile);
            $this->user_id = empty(Yii::$app->user) ? null : Yii::$app->user->id;

            // @TODO: md5 checking, same md5 and same user, just updating property

            return true;
        } else {
            return false;
        }
    }

    /**
     * upload file from local file system
     * @param  string  $file       the file location
     * @param  boolean $removeFile remove the source file after uploading
     * @return boolean             true or false
     */
    public function uploadByLocal($file, $removeFile = false, $name = '')
    {
        if ( !file_exists($file) )
            return false;
        // get file info
        $info = pathinfo($file);

        $targetFile = self::getUploadPath() . '/' . $info['basename'];
        if ( file_exists( $targetFile ) ) {
           $targetFile = $info['filename'] . '-' . hash('crc32b', microtime(true)) . '.' . $info['extension'];
           $targetFile = self::getUploadPath() . '/' . $targetFile;
        }

        if ( copy($file, $targetFile) ) {
            $this->path = date('Y') . '/' . date('m') . '/' . basename($targetFile);
            $this->name = empty($name) ? $info['filename'] : $name;
            $this->size = filesize($targetFile);
            $this->mime = FileHelper::getMimeType($file);
            $this->md5  = md5_file($targetFile);
            $this->user_id = empty(Yii::$app->user) ? null : Yii::$app->user->id;
            if ( $removeFile ) {
                unlink($file);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * find One By Md5
     * @param  string $hash md5 string of uploaded file
     * @return File       
     */
    public static function findOneByMd5($hash)
    {
        return static::findOne(['md5' => $hash]);
    }

    public static function getUrlById($id)
    {
        // doesnt work ?
        // $file = static::getDb()->cache(function($db) use($id){
        //     return static::find()->where(['id' => $id])->one();
        // });
        // return Yii::getAlias('@uploadUrl') . '/' . $file->path;
        
        $file = static::findOne($id)->toArray();
        return Yii::getAlias('@uploadUrl') . '/' . $file['path'];
    }

    public static function getImageUrlByPath($path)
    {
        return Yii::getAlias('@uploadUrl') . '/' . $path;
    }

    public function getImageUrl()
    {
        return static::getImageUrlByPath($this->path);
    }

    public static function getImageUrlById($id)
    {
        if (!$id){
            return null;
        }
        $file = static::findOne($id)->toArray();
        if (!preg_match("/^image/", $file['mime'])) {
            return null;
        }

        return Yii::getAlias('@uploadUrl') . '/' . $file['path'];
    }

    /**
     * get uploading directory from configurations
     * @return string uploading directory
     */
    public static function getUploadPath()
    {
        $basePath = Yii::getAlias('@uploadPath');
        $toPath = $basePath . '/' . date('Y') . '/' . date('m');
        
        $rs = FileHelper::createDirectory($toPath);

        return $rs ? $toPath : $basePath;
    }

    /**
     * remove file
     * @return bool true/false
     */
    public function removeFile()
    {
        if (!$this->path) {
            return false;
        }
        $file = Yii::getAlias('@uploadPath') . '/' . $this->path;
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

}
