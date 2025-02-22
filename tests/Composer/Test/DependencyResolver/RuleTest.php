<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Test\DependencyResolver;

use Composer\DependencyResolver\GenericRule;
use Composer\DependencyResolver\Rule;
use Composer\DependencyResolver\RuleSet;
use Composer\DependencyResolver\Pool;
use Composer\Package\Link;
use Composer\Semver\Constraint\MatchAllConstraint;
use Composer\Test\TestCase;

class RuleTest extends TestCase
{
    public function testGetHash()
    {
        $rule = new GenericRule(array(123), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));

        $hash = unpack('ihash', md5('123', true));
        $this->assertEquals($hash['hash'], $rule->getHash());
    }

    public function testEqualsForRulesWithDifferentHashes()
    {
        $rule = new GenericRule(array(1, 2), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));
        $rule2 = new GenericRule(array(1, 3), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));

        $this->assertFalse($rule->equals($rule2));
    }

    public function testEqualsForRulesWithDifferLiteralsQuantity()
    {
        $rule = new GenericRule(array(1, 12), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));
        $rule2 = new GenericRule(array(1), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));

        $this->assertFalse($rule->equals($rule2));
    }

    public function testEqualsForRulesWithSameLiterals()
    {
        $rule = new GenericRule(array(1, 12), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));
        $rule2 = new GenericRule(array(1, 12), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));

        $this->assertTrue($rule->equals($rule2));
    }

    public function testSetAndGetType()
    {
        $rule = new GenericRule(array(), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));
        $rule->setType(RuleSet::TYPE_REQUEST);

        $this->assertEquals(RuleSet::TYPE_REQUEST, $rule->getType());
    }

    public function testEnable()
    {
        $rule = new GenericRule(array(), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));
        $rule->disable();
        $rule->enable();

        $this->assertTrue($rule->isEnabled());
        $this->assertFalse($rule->isDisabled());
    }

    public function testDisable()
    {
        $rule = new GenericRule(array(), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));
        $rule->enable();
        $rule->disable();

        $this->assertTrue($rule->isDisabled());
        $this->assertFalse($rule->isEnabled());
    }

    public function testIsAssertions()
    {
        $rule = new GenericRule(array(1, 12), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));
        $rule2 = new GenericRule(array(1), Rule::RULE_ROOT_REQUIRE, array('packageName' => '', 'constraint' => new MatchAllConstraint));

        $this->assertFalse($rule->isAssertion());
        $this->assertTrue($rule2->isAssertion());
    }

    public function testPrettyString()
    {
        $pool = new Pool(array(
            $p1 = $this->getPackage('foo', '2.1'),
            $p2 = $this->getPackage('baz', '1.1'),
        ));

        $repositorySetMock = $this->getMockBuilder('Composer\Repository\RepositorySet')->disableOriginalConstructor()->getMock();
        $requestMock = $this->getMockBuilder('Composer\DependencyResolver\Request')->disableOriginalConstructor()->getMock();

        $emptyConstraint = new MatchAllConstraint();
        $emptyConstraint->setPrettyString('*');

        $rule = new GenericRule(array($p1->getId(), -$p2->getId()), Rule::RULE_PACKAGE_REQUIRES, new Link('baz', 'foo', $emptyConstraint));

        $this->assertEquals('baz 1.1 relates to foo * -> satisfiable by foo[2.1].', $rule->getPrettyString($repositorySetMock, $requestMock, $pool, false));
    }
}
