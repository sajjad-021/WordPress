<?php

/**
 * Project : 355_wp_Opensearch
 * User: thuytien
 * Date: 8/31/2014
 * Time: 2:38 PM
 */
class OpenSearch
{
protected $shortName;
protected $description;
protected $urls;
protected $exampleQuery;
protected $contact;
protected $tags;
protected $longName;
protected $developer;
protected $attribution;
protected $syndicationRight;
protected $adultContent;
protected $inputEncoding;
protected $outputEncoding;
protected $smallIcon;
protected $largeIcon;
protected $language;


    /**
     * @param $template
     * @param string $type
     */
    public function addUrl($template, $type = 'text/html')
    {
        $this->urls[] = array(
            'type' => $type,
            'template' => $template
        );
    }

public function toXML()
{
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"></OpenSearchDescription>');
    $xml->addChild('ShortName', htmlspecialchars($this->shortName));
    $xml->addChild('Description', htmlspecialchars($this->description));
    $xml->addChild('LongName', htmlspecialchars($this->longName));
    $xml->addChild('Contact', htmlspecialchars($this->contact));
    $xml->addChild('Developer', htmlspecialchars($this->developer));
    $xml->addChild('Attribution', htmlspecialchars($this->attribution));
    $xml->addChild('Tags', htmlspecialchars($this->tags));
    $xml->addChild('InputEncoding', $this->inputEncoding);
    $xml->addChild('OutputEncoding', $this->outputEncoding);
    $xml->addChild('Language', $this->language);
    $xml->addChild('AdultContent', ($this->adultContent ? 'true' : 'false'));
    $selfUrl = $xml->addChild('Url');
    $selfUrl->addAttribute('rel', 'self');
    $selfUrl->addAttribute('type', 'application/opensearchdescription+xml');
    $selfUrl->addAttribute('template', htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));

    foreach ($this->urls as $url) {
        $link = $xml->addChild('Url');
        $link->addAttribute('type', $url['type']);
        $link->addAttribute('template', htmlspecialchars($url['template']));
    }
    if ($this->smallIcon) {
        $image = $xml->addChild('Image', htmlspecialchars($this->smallIcon));
        $image->addAttribute('height', '16');
        $image->addAttribute('width', '16');
        $image->addAttribute('type', 'image/vnd.microsoft.icon');
    }
    if ($this->largeIcon) {
        $image = $xml->addChild('Image', htmlspecialchars($this->largeIcon));
        $image->addAttribute('height', '64');
        $image->addAttribute('width', '64');
        $image->addAttribute('type', 'image/png');
    }
    if ($this->exampleQuery) {
        $query = $xml->addChild('Query');
        $query->addAttribute('role', 'example');
        $query->addAttribute('searchTerms', htmlspecialchars($this->exampleQuery));
    }
    if (@in_array($this->syndicationRight, array('open', 'limited', 'private', 'closed'))) {
        $xml->addChild('SyndicationRight', $this->syndicationRight);
    }
    return $xml->asXML();

}

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param mixed $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }
    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * @param mixed $urls
     */
    public function setUrls($urls)
    {
        $this->urls = $urls;
    }

    /**
     * @return mixed
     */
    public function getExampleQuery()
    {
        return $this->exampleQuery;
    }

    /**
     * @param mixed $exampleQuery
     */
    public function setExampleQuery($exampleQuery)
    {
        $this->exampleQuery = $exampleQuery;
    }

    /**
     * @return mixed
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param mixed $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return mixed
     */
    public function getLongName()
    {
        return $this->longName;
    }

    /**
     * @param mixed $longName
     */
    public function setLongName($longName)
    {
        $this->longName = $longName;
    }

    /**
     * @return mixed
     */
    public function getDeveloper()
    {
        return $this->developer;
    }

    /**
     * @param mixed $developer
     */
    public function setDeveloper($developer)
    {
        $this->developer = $developer;
    }

    /**
     * @return mixed
     */
    public function getAttribution()
    {
        return $this->attribution;
    }

    /**
     * @param mixed $attribution
     */
    public function setAttribution($attribution)
    {
        $this->attribution = $attribution;
    }

    /**
     * @return mixed
     */
    public function getSyndicationRight()
    {
        return $this->syndicationRight;
    }

    /**
     * @param mixed $syndicationRight
     */
    public function setSyndicationRight($syndicationRight)
    {
        $this->syndicationRight = $syndicationRight;
    }

    /**
     * @return mixed
     */
    public function getAdultContent()
    {
        return $this->adultContent;
    }

    /**
     * @param mixed $adultContent
     */
    public function setAdultContent($adultContent)
    {
        $this->adultContent = $adultContent;
    }

    /**
     * @return mixed
     */
    public function getInputEncoding()
    {
        return $this->inputEncoding;
    }

    /**
     * @param mixed $inputEncoding
     */
    public function setInputEncoding($inputEncoding)
    {
        $this->inputEncoding = $inputEncoding;
    }

    /**
     * @return mixed
     */
    public function getOutputEncoding()
    {
        return $this->outputEncoding;
    }

    /**
     * @param mixed $outputEncoding
     */
    public function setOutputEncoding($outputEncoding)
    {
        $this->outputEncoding = $outputEncoding;
    }

    /**
     * @return mixed
     */
    public function getSmallIcon()
    {
        return $this->smallIcon;
    }

    /**
     * @param mixed $smallIcon
     */
    public function setSmallIcon($smallIcon)
    {
        $this->smallIcon = $smallIcon;
    }

    /**
     * @return mixed
     */
    public function getLargeIcon()
    {
        return $this->largeIcon;
    }

    /**
     * @param mixed $largeIcon
     */
    public function setLargeIcon($largeIcon)
    {
        $this->largeIcon = $largeIcon;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

} 