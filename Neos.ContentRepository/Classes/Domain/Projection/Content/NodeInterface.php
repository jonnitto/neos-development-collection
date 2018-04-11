<?php
namespace Neos\ContentRepository\Domain\Projection\Content;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\ValueObject\NodeIdentifier;
use Neos\ContentRepository\Domain\Context\ContentStream\ContentStreamIdentifier;
use Neos\ContentRepository\Domain\ValueObject\DimensionSpacePoint;
use Neos\ContentRepository\Domain\Context\NodeAggregate\NodeAggregateIdentifier;
use Neos\ContentRepository\Domain\ValueObject\NodeName;
use Neos\ContentRepository\Domain\ValueObject\NodeTypeName;
use Neos\Cache\CacheAwareInterface;

/**
 * The "new" Event-Sourced NodeInterface. Supersedes the old Neos\ContentRepository\Domain\Model\NodeInterface.
 *
 * !! Reference resolving NOT in NodeInterface
 *
 * Immutable. Read-only. Detached from storage.
 */
interface NodeInterface extends CacheAwareInterface
{

    public function getContentStreamIdentifier(): ContentStreamIdentifier;

    public function getNodeIdentifier(): NodeIdentifier;

    public function getNodeAggregateIdentifier(): NodeAggregateIdentifier;

    public function getNodeTypeName(): NodeTypeName;

    public function getNodeType(): NodeType;


    public function getNodeName(): NodeName;

    public function getDimensionSpacePoint(): DimensionSpacePoint;

    /**
     * Returns all properties of this node. References are NOT part of this API; there you need to check getReference() and getReferences()
     *
     * @return array Property values, indexed by their name
     * @api
     */
    public function getProperties(): PropertyCollection;

    /**
     * Returns the specified property.
     *
     * If the node has a content object attached, the property will be fetched
     * there if it is gettable.
     *
     * @param string $propertyName Name of the property
     * @return mixed value of the property
     * @throws NodeException if the node does not contain the specified property
     * @api
     */
    public function getProperty($propertyName);


    /**
     * If this node has a property with the given name. Does NOT check the NodeType; but checks
     * for a non-NULL property value.
     *
     * @param string $propertyName
     * @return boolean
     * @api
     */
    public function hasProperty($propertyName): bool;

    /**
     * Returns the current state of the hidden flag
     *
     * @return boolean
     * @api
     */
    public function isHidden();

    /**
     * Returns the node label as generated by the configured node label generator
     *
     * @return string
     */
    public function getLabel(): string;
}
