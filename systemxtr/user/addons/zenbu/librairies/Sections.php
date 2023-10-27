<?php namespace Zenbu\librairies;

use Craft;
use Zenbu\librairies\platform\ee\SectionBase as SectionBase;

class Sections extends SectionBase
{
    var $sections;
    var $subSections;
    public function __construct()
    {
        parent::__construct();
        $this->sectionBase = new parent();
    }

    public function setSections($sections)
    {
        return $this->sections = $this->sectionBase->getSections();
    }

    public function getSections()
    {
        return $this->sections;
    }

    public function setSubSections($section_id = 0)
    {
        return $this->subSections = $this->sectionBase->getSubSections($section_id);
    }

    public function getSubSections($subSections)
    {
        return $this->subSections;
    }

    /**
     * Retrieve a list of sections for settings select dropown
     * @return array The section array
     */
    public function getSectionSelectOptions()
    {
        $sections         = $this->getSections();
        $dropdown_options = parent::buildSelectOptions($sections);

        return $dropdown_options;

    } // END getSectionSelectOptions

    // --------------------------------------------------------------------
}