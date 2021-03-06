<?php

namespace Jellygnite\Seo\Forms;

use Jellygnite\Seo\Analysis\Analysis;
use Jellygnite\Seo\Extensions\PageHealthExtension;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;

/**
 * Class HealthAnalysisField
 * @package Jellygnite\Seo\Forms
 */
class HealthAnalysisField extends LiteralField
{

    /**
     * @var \Page
     */
    protected $page;

    /**
     * @var int
     */
    protected $result;
    protected $schemaComponent = 'HealthAnalysisField';

    protected $template = self::class;

    /**
     * HealthAnalysisField constructor.
     *
     * @param string                               $name
     * @param \SilverStripe\Forms\FormField|string $title
     * @param \Page                                $page
     */
    public function __construct($name, $title, SiteTree $page)
    {
        $this->setPage($page);
        Requirements::javascript('jellygnite/silverstripe-seo:client/dist/javascript/main.min.js');
        Requirements::css('jellygnite/silverstripe-seo:client/dist/css/styles.min.css');

        parent::__construct($name, ArrayData::create(
            [
                'Title'      => $title,
                'Results'    => $this->runAnalyses(),
            ]
        )->renderWith(self::class));
    }

    /**
     * Fetches a list of all Analysis subclasses
     *
     * @return array
     */
    public function getAnalyses()
    {
        $classes = ClassInfo::subclassesFor(Analysis::class);
        $output  = [];

        /** @var Analysis $class */
        foreach ($classes as $class) {
            if ($class === Analysis::class) {
                continue;
            }

            $output[] = $class;
        }

        return $output;
    }

    /**
     * @return SiteTree|PageHealthExtension
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Runs all analyses and returns an ArrayList
     *
     * @return ArrayList
     */
    public function runAnalyses()
    {
        $analyses = $this->getAnalyses();
        $output   = ArrayList::create([]);

        foreach ($analyses as $analysisClass) {
            /** @var Analysis $analysis */
            $analysis = $analysisClass::create($this->getPage());
            $output->push($analysis->inspect());
        }

        return $output;
    }

    /**
     * @param SiteTree $page
     * @return $this
     */
    public function setPage(SiteTree $page)
    {
        $this->page = $page;
        return $this;
    }
}
