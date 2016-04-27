<?php

namespace Rezzza\SymfonyRestApiJson\Tests\Units;

use mageekguy\atoum;

class Weblink extends atoum\test
{
    public function test_it_parse_url()
    {
        $this
            ->given(
                $givenHeader = '<http://google.fr>; rel="customer"'
            )
            ->when(
                $sut = $this->testedClass->getClass()::fromHeaderString($givenHeader)
            )
            ->then
                ->variable($sut->getUrl())
                    ->isEqualTo('http://google.fr')
        ;
    }

    public function test_it_replace_host()
    {
        $this
            ->given(
                $givenWeblink = $this->newTestedInstance('https://www.yahoo.fr/search?foo=bar#anchor', 'Customer', ['title' => 'hello you', 'description' => 'customer description'])
            )
            ->and(
                $givenHost = 'http://google.com'
            )
            ->when(
                $sut = $this->testedClass->getClass()::fromWeblinkWithHost($givenWeblink, $givenHost)
            )
            ->then
                ->variable($sut->getUrl())
                    ->isEqualTo('http://google.com/search?foo=bar#anchor')
        ;
    }

    public function test_it_detects_wrong_url()
    {
        $this
            ->exception(function () {
                $sut = $this->newTestedInstance('http:/woot');
            })
                ->hasMessage('"http:/woot" is not a valid url')
        ;
    }

    public function test_it_parse_rel_attribute()
    {
        $this
            ->given(
                $givenHeader = '<http://google.fr>; rel="customer"'
            )
            ->when(
                $sut = $this->testedClass->getClass()::fromHeaderString($givenHeader)
            )
            ->then
                ->variable($sut->getRel())
                    ->isEqualTo('customer')
        ;
    }

    public function test_it_parse_others_attributes()
    {
        $this
            ->given(
                $givenHeader = '<http://google.fr>; rel="customer"; title="my link";media="mobile"'
            )
            ->when(
                $sut = $this->testedClass->getClass()::fromHeaderString($givenHeader)
            )
            ->then
                ->phpArray($sut->getAttributes())
                    ->isEqualTo([
                        'title' => 'my link',
                        'media' => 'mobile'
                    ])
        ;
    }

    public function test_it_is_related_to()
    {
        $this
            ->given(
                $sut = $this->newTestedInstance('http://google.fr', 'customer')
            )
            ->then
                ->boolean($sut->isRelatedTo('Customer'))
                    ->isTrue()
        ;
    }
}
