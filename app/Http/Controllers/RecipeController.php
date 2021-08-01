<?php


namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

class RecipeController extends Controller
{
    public function getRecipe(Request $request)
    {
        //$ClearText = preg_replace( "/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($HTMLText))) );
        $url = $request->get('url');
        if (!empty($url)) {
            $this->fetch($url);
        }


        return view('welcome', ['oldUrl' => $url]);
    }

    private function fetch($url)
    {
        $client = new Client();
        $res = $client->get($url);

        $html = $res->getBody()->getContents();


        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $elements = $xpath->query('//h3');

        $arr = [];

        foreach ($elements as $element) {
            $arr[] = $element->textContent;
        }

        $headings = $this->getAllHeadings($xpath);
        $ingredientsHeading = $this->getIngredientsHeading($headings);

        $ingredientsHeadingElement = $xpath->query($ingredientsHeading['path']);

        $parent = $ingredientsHeadingElement->item(0)->parentNode;

        $listsQuery = "{$parent->getNodePath()}//ul";
        $lists = $xpath->query($listsQuery);

        $listsArray = [];


        foreach ($lists as $list) {
            $listArray = [];
            foreach ($list->childNodes as $listItem) {
//                $listArray[] = $this->convertFractions($listItem->textContent);
                $listArray[] =
                    $this->convertUnits(
                        $this->convertFractions(
                            trim(
                                $listItem->textContent
                            )
                        )
                    );
            }

            $listsArray[] = $listArray;
        }
    }

    private function getAllHeadings(\DOMXPath $xpath): array {
        $types = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        $headings = [];

        foreach ($types as $type) {
            $elements = $xpath->query("//{$type}");

            foreach ($elements as $element) {
                $headings[] = [
                    'text' => strtolower($element->textContent),
                    'path' => $element->getNodePath()
                ];
            }
        }

        return $headings;
    }

    private function convertUnits(string $text): string
    {
        $units = ['cup', 'cups', 'ounces', 'ounce', 'inch', 'inches'];
        $unitsString = implode('|', $units);

        $text = '5 ounces feta cheese, cut into 0.5 inch cubes*';
        $regex = "/(?P<amount>\d+(\.\d+)?) (?P<unit>{$unitsString})/";
        $text = preg_replace_callback($regex, function($matches) {
            $amount = $matches['amount'];
            $unit = $matches['unit'];

            switch ($matches['unit']) {
                case 'ounces':
                    $unit = 'gram';
                    $amount = round($matches['amount'] * 28.3495231, 1);
                    break;
                case 'inch':
                    $unit = 'cm';
                    $amount = round($matches['amount'] * 2.54, 1);
                    break;
            }

            return $amount . ' '. $unit;
        }, $text);


        return $text;
    }

    private function convertFractions($text)
    {
        $fracs = ['¼', '½', '¾', '⅐', '⅑', '⅒', '⅓', '⅔', '⅕', '⅖', '⅗', '⅘', '⅙', '⅚', '⅛', '⅜', '⅝', '⅞', '↉'];
        $replacements = [(1/4), (1/2), (3/4), (1/7), (1/9), (1/10), (1/3), (2/3), (1/5), (2/5), (3/5), (4/5), (1/6), (5/6), (1/8), (3/8), (5/8), (7/8), (0/3)];
        return str_replace($fracs, $replacements, $text);
    }

    private function getIngredientsHeading(array $headings)
    {
        foreach ($headings as $heading) {
            if (strpos($heading['text'], 'ingredients') !== false) {
                return $heading;
            }
        }
    }
}
