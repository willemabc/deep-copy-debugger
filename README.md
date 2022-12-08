# DeepCopyDebugger

DeepCopy is powerful, but hard to debug. Especially when you set the filters for a single DeepCopy instance in multiple classes and use wildcard matchers like the PropertyNameMatcher. The DeepCopyDebugger makes it easier to spot incorrect or missing filters.

DeepCopyDebugger helps in the following ways:
- It can return a list of all filters set on the DeepCopy instance, in the order in which they were set.
- Given a DeepCopy instance and a Doctrine entity class, it can return all properties and the matched filters for each individual property.
- There are also methods to only get matched properties or only unmatched properties.

## Usage
It is important to call the DeepCopyDebugger *before* calling the *copy* method on your DeepCopy instance!

```
$deepCopy = new DeepCopy();
// ... set your filters

$deepCopyDebugger = new DeepCopyDebugger($deepCopy);
dump($deepCopyDebugger->getFilterCollection());
dump($deepCopyDebugger->getMatchedAndUnmatchedEntityProperties(YourDoctrineEntity::class));
dump($deepCopyDebugger->getMatchedEntityProperties(YourDoctrineEntity::class));
dump($deepCopyDebugger->getUnmatchedEntityProperties(YourDoctrineEntity::class));

$deepCopy->copy($sourceObject);
```