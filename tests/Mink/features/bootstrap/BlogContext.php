<?php

namespace Shopware\Tests\Mink;

use Shopware\Tests\Mink\Page\Emotion\Blog;
use Shopware\Tests\Mink\Element\Emotion\BlogBox;
use Behat\Gherkin\Node\TableNode;

class BlogContext extends SubContext
{
    /**
     * @Given /^I am on the blog category (?P<categoryId>\d+)$/
     * @Given /^I go to the blog category (?P<categoryId>\d+)$/
     */
    public function iAmOnTheBlogCategory($categoryId)
    {
        $this->getPage('Blog')->open(['categoryId' => $categoryId]);
    }

    /**
     * @Given /^I click to read the blog article on position (\d+)$/
     */
    public function iClickToReadTheBlogArticleOnPosition($position)
    {
        /** @var Blog $page */
        $page = $this->getPage('Blog');
        $language = Helper::getCurrentLanguage($page);

        /** @var BlogBox $blogBox */
        $blogBox = $this->getMultipleElement($page, 'BlogBox', $position);
        Helper::clickNamedLink($blogBox, 'readMore', $language);
    }

    /**
     * @When /^I write a comment:$/
     */
    public function iWriteAComment(TableNode $data)
    {
        $this->getPage('Blog')->writeComment($data->getHash());
    }

    /**
     * @When /^the shop owner activates my latest comment$/
     */
    public function theShopOwnerActivateMyLatestComment()
    {
        $sql = 'UPDATE `s_blog_comments` SET `active`= 1 ORDER BY id DESC LIMIT 1';
        $this->getContainer()->get('db')->exec($sql);
        $this->getSession()->reload();
    }

    /**
     * @Then /^I should see an average evaluation of (\d+) from following comments:$/
     */
    public function iShouldSeeAnAverageEvaluationOfFromFollowingComments($average, TableNode $comments)
    {
        /** @var \Shopware\Tests\Mink\Page\Emotion\Blog $page */
        $page = $this->getPage('Blog');

        /** @var \Shopware\Tests\Mink\Element\MultipleElement $blogComments */
        $blogComments = $this->getMultipleElement($page, 'BlogComment');

        $page->checkComments($blogComments, $average, $comments->getHash());
    }
}
