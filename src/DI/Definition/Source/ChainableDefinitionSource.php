<?php
/**
 * PHP-DI
 *
 * @link      http://php-di.org/
 * @copyright Matthieu Napoli (http://mnapoli.fr/)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 */

namespace DI\Definition\Source;

use DI\Definition\Definition;
use DI\Definition\MergeableDefinition;

/**
 * Definition source that allows chaining another source after it.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
abstract class ChainableDefinitionSource implements DefinitionSource
{
    /**
     * @var DefinitionSource
     */
    protected $chainedSource;

    /**
     * Chain another definition source after this one.
     *
     * @param DefinitionSource $source
     */
    public function chain(DefinitionSource $source)
    {
        $this->chainedSource = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        $definition = $this->findDefinition($name);

        if ($definition === null) {
            // Not found, we use the chain or return null
            if ($this->chainedSource) {
                return $this->chainedSource->getDefinition($name);
            }
            return null;
        }

        // Enrich definition in sub-source
        if ($this->chainedSource && $definition instanceof MergeableDefinition) {
            $subDefinition = $this->chainedSource->getDefinition($definition->getExtendedDefinitionName());
            if ($subDefinition instanceof MergeableDefinition) {
                $definition = $definition->merge($subDefinition);
            }
        }

        return $definition;
    }

    /**
     * @param string $name
     * @return Definition|null
     */
    protected abstract function findDefinition($name);
}
