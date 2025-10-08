<?php
require 'vendor/autoload.php';
use simplehtmldom\HtmlDocument;
class Parser {
	public static $reg_patterns = [
	"title" => '/<h1.*(?=<\/h1>)/us',
	"title_filtr"=>'/(?<=">).*/us',
	"img" => '/<img\b(?![^>]*\balt\b)\b(?![^>]*\bvideo\b)\b(?![^>]*\bstyle\b)[^>]*loading="lazy"[^>]*srcset="[^>]*\/[a-zA-Z0-9]*\/\d*\.jpg[^>]*>/u',
	"img_filtr" => '/(?<=src=").*?jpg(?=")/u',
	"categories" => '/<span>(.*?)<\/span>(?!.*<svg xmlns="http:\/\/www\.w3\.org\/2000\/svg" width="12" height="12" viewBox="0 0 24 24" class="d2g_11">)/u',
	"categories_filtr" => '/(?<=<span>).*?(?=<\/span>)/u',
	"type" => '/Тип.(.*?)(?=[А-Я])/u',
	"country" => '/Страна.*[А-Я][а-я]*(?=[А-Я])/u',
	"article_number" => '/(Партномер|Артикул производителя)(.*?)\d+/u',
	"description" => '/Описание(.*?)(?=Показать)/u',
	"feature" => '/Характеристики.*/u',
	"feature_filtr" => '/(?<=\>)[^>]+(?=<)/u'
	];
	public $result = [];
	
	public static function parse($url){
		$cmd = 'node render-console.js ' . escapeshellarg($url);
		$html = shell_exec($cmd);
	   
		if (!$html) {
			die("Ошибка: не удалось получить HTML\n");
		}
		$html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5);
		$filtred_html = self::filtr_html($html);
		return self::parse_html($html, $filtred_html);
	}
	
	public static function fix_text($string){
		$string = preg_replace(['/([а-я])([А-Я])/u','/([а-я])([A-Z])/u'], ['$1 $2','$1 $2'], $string);
		return $string;
	
	}
	public static function parse_html($html, $filtred_html){
		preg_match_all(self::$reg_patterns["title"], $html, $title);
		preg_match_all(self::$reg_patterns["title_filtr"], $title[0][0],$title_filtred);
		$result[] =$title_filtred[0][0];

		preg_match_all(self::$reg_patterns["categories"], $html, $categories);
		preg_match_all(self::$reg_patterns["categories_filtr"], $categories[0][0], $categories_filtred);
		$result[] = implode(" ", $categories_filtred[0]);

		preg_match_all(self::$reg_patterns["type"], $filtred_html, $type);
		$result[] = $type[0][0];

		preg_match_all(self::$reg_patterns["country"], $filtred_html, $country);
		$result[] = $country[0][0];

		preg_match_all(self::$reg_patterns["article_number"], $filtred_html, $article);
		$result[] = $article[0][0];

		$result[] = $country[0][0];

		preg_match_all(self::$reg_patterns["img"], $html, $img);
		$img = implode(" ", $img[0]);
		preg_match_all(self::$reg_patterns["img_filtr"], $img, $img_filtred);
		$result[] = $img_filtred[0];
		
		preg_match_all(self::$reg_patterns["description"], $filtred_html, $description);
		$result[] = $description[0][0];
		
		preg_match_all(self::$reg_patterns["feature"], $html, $feature);
		echo $feature;
		preg_match_all(self::$reg_patterns["feature_filtr"], $feature[0][0], $feature_filtred);
		echo $feature_filtred;
		$result[] = $feature_filtred[0];
		foreach($result as $key => $value){
			if (is_string($value)){
				$result[$key]=self::fix_text($value);
			}
		}
		return $result;
	}

	
	public static function filtr_html($html){
		return strip_tags($html);
	}

}
 
#$res = Parser::parse('https://www.ozon.ru/product/filtr-maslyanyy-mando-mof4460-originalnyy-nomer-2630035505-dlya-henday-i-kia-1-4-2010-2017-1-6-s-859656225/');
#print_r($res);
#$res2 = Parser::parse('https://www.ozon.ru/product/filtr-maslyanyy-chevrolet-shevrole-aveo-aveo-cruze-kruz-lacetti-lachetti-lanos-lanos-niva-282317736/?at=MZtvXJn5RsqmGqJkTZ87JD2Uo0D32wcnRGommtoqroo5');
#print_r($res2)
?>
