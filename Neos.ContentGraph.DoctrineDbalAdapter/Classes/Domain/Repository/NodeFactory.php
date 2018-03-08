<?php

namespace Neos\ContentGraph\DoctrineDbalAdapter\Domain\Repository;

/*
 * This file is part of the Neos.ContentGraph.DoctrineDbalAdapter package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
use Neos\ContentRepository\Domain as ContentRepository;
use Neos\ContentRepository\Domain\Projection\Content as ContentProjection;
use Neos\ContentRepository\Domain\ValueObject\NodeName;
use Neos\ContentRepository\Domain\ValueObject\NodeTypeName;
use Neos\Flow\Annotations as Flow;


/**
 * Implementation detail of ContentGraph and ContentSubgraph
 *
 * @Flow\Scope("singleton")
 */
final class NodeFactory
{
    /**
     * @Flow\Inject
     * @var ContentRepository\Service\NodeTypeManager
     */
    protected $nodeTypeManager;

    /**
     * @param array $nodeRow Node Row from projection (neos_contentgraph_node table)
     * @return ContentProjection\NodeInterface
     * @throws \Exception
     * @throws \Neos\ContentRepository\Exception\NodeConfigurationException
     * @throws \Neos\ContentRepository\Exception\NodeTypeNotFoundException
     */
    public function mapNodeRowToNode(array $nodeRow): ContentProjection\NodeInterface
    {
        $nodeType = $this->nodeTypeManager->getNodeType($nodeRow['nodetypename']);
        $className = $nodeType->getNodeInterfaceImplementationClassName();

        // $serializedSubgraphIdentifier is empty for the root node
        if (!empty($nodeRow['dimensionspacepointhash'])) {
            // NON-ROOT case
            if (!array_key_exists('contentstreamidentifier', $nodeRow)) {
                throw new \Exception('The "contentstreamidentifier" property was not found in the $nodeRow; you need to include the "contentstreamidentifier" field in the SQL result.');
            }
            if (!array_key_exists('dimensionspacepoint', $nodeRow)) {
                throw new \Exception('The "dimensionspacepoint" property was not found in the $nodeRow; you need to include the "dimensionspacepoint" field in the SQL result.');
            }

            $contentStreamIdentifier = new ContentRepository\Context\ContentStream\ContentStreamIdentifier($nodeRow['contentstreamidentifier']);
            // FIXME Move to DimensionSpacePoint::fromJson
            $dimensionSpacePoint = new ContentRepository\ValueObject\DimensionSpacePoint(json_decode($nodeRow['dimensionspacepoint'], true)['coordinates']);

            $nodeIdentifier = new ContentRepository\ValueObject\NodeIdentifier($nodeRow['nodeidentifier']);

            $properties = json_decode($nodeRow['properties'], true);

            // Reference and References "are no properties" anymore by definition; so Node does not know
            // anything about it.
            $properties = array_filter($properties, function($propertyName) use ($nodeType) {
                $propertyType = $nodeType->getPropertyType($propertyName);
                return $propertyType !== 'reference' && $propertyType !== 'references';
            }, ARRAY_FILTER_USE_KEY);

            /* @var $node ContentProjection\NodeInterface */
            $node = new $className(
                $contentStreamIdentifier,
                $dimensionSpacePoint,
                new ContentRepository\Context\NodeAggregate\NodeAggregateIdentifier($nodeRow['nodeaggregateidentifier']),
                $nodeIdentifier,
                new NodeTypeName($nodeRow['nodetypename']),
                $nodeType,
                new ContentRepository\ValueObject\NodeName($nodeRow['name']),
                $nodeRow['hidden'],
                $properties
            );
                //new ContentProjection\PropertyCollection(, $referenceProperties, $referencesProperties, $nodeIdentifier, $contentSubgraph),

            if (!array_key_exists('name', $nodeRow)) {
                throw new \Exception('The "name" property was not found in the $nodeRow; you need to include the "name" field in the SQL result.');
            }
            return $node;
        } else {
            // ROOT node!
            /* @var $node \Neos\ContentRepository\Domain\Projection\Content\NodeInterface */
            $node = new $className(
                ContentProjection\RootNodeIdentifiers::rootContentStreamIdentifier(),
                ContentProjection\RootNodeIdentifiers::rootDimensionSpacePoint(),
                ContentProjection\RootNodeIdentifiers::rootNodeAggregateIdentifier(),
                new ContentRepository\ValueObject\NodeIdentifier($nodeRow['nodeidentifier']),
                new NodeTypeName($nodeRow['nodetypename']),
                $nodeType,
                NodeName::root(),
                false,
                []
            );
            return $node;
        }
    }
}
