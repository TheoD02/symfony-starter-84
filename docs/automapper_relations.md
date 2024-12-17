# Automapper with Relation Mapping Guide (Supplier-Criteria Example)

This documentation provides guidance on using the [Automapper](https://automapper.jolicode.com) package with a custom
transformer, `RelationToEntityTransformer`, to map relation objects into real database entities. It also explains how to
handle relational fields and control automatic mapping behavior through the mapper context.

We will use a real-world example of mapping `SupplierPayload` to the `Supplier` entity, where the `Supplier` has a
`ManyToMany` relationship with `Criteria`.

### 1. **Understanding the Relation Object**

The relation object defines how you manage related entities in your payload. It has the following structure:

- `set`: Replaces all current values with the provided ones.
- `add`: Adds new values to the current collection.
- `remove`: Removes values from the current collection.

#### Example Relation Object:

```json
{
  "set": [
    1,
    2,
    3
  ],
  "add": [
    4,
    5,
    6
  ],
  "remove": [
    7,
    8,
    9
  ]
}
```

**Key Points:**

- **`set`**: Used for replacing all values (typically for collections). It will override existing relations.
- **`add` and `remove`**: These can be used together to incrementally modify the collection. They allow adding or
  removing specific items without replacing the entire collection.
- You cannot combine `set` with `add` or `remove`, as `set` completely replaces the current data.
- When using `add` and `remove`, it is illogical to try to add and remove the same entity simultaneously.

### 2. **RelationToEntityTransformer**

The `RelationToEntityTransformer` is a custom transformer responsible for converting relation objects from the payload
into real database entities. It handles the following operations:

- **Set**: Replaces all existing related entities with the new ones.
- **Add**: Adds new related entities to the current collection.
- **Remove**: Removes specific related entities from the collection.

### 3. **Example: Mapping SupplierPayload to Supplier**

#### 3.1 SupplierPayload Class

In the following example, the `SupplierPayload` contains a `Relation` type for the `criteria` property. This property is
validated to ensure that the relation object is properly structured.

```php
use Symfony\Component\Validator\Constraints as Assert;
use YourNamespace\Relation;

class SupplierPayload
{
    #[Assert\Valid]
    public Relation $criteria; // Relation object containing 'set', 'add', 'remove'
}
```

#### 3.2 Supplier Entity Class

The `Supplier` entity is a Doctrine entity that has a `ManyToMany` relationship with the `Criteria` entity. It manages
multiple `Criteria` entities using a collection.

```php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use YourNamespace\Criteria;

class Supplier
{
    /**
     * @var Collection<int, Criteria>
     */
    #[ORM\ManyToMany(targetEntity: Criteria::class)]
    private Collection $criteria;

    public function __construct()
    {
        $this->criteria = new ArrayCollection();
    }

    /**
     * @return Collection<int, Criteria>
     */
    public function getCriteria(): Collection
    {
        return $this->criteria;
    }

    public function setCriteria(Collection $criteria): void
    {
        $this->criteria = $criteria;
    }
}
```

In this setup:

- The `Supplier` entity has a `ManyToMany` relationship with `Criteria`.
- The `criteria` field is a collection that can contain multiple `Criteria` entities.
- Only the `getCriteria()` and `setCriteria()` methods are necessary to manage this relation, though you can implement
  additional methods (`addCriteria()`, `removeCriteria()`) if needed.

### 4. **Mapping Using Automapper**

#### 4.1 Using the `set` Operation

If the `set` operation is provided in the payload, the entire current collection of `Criteria` entities will be
replaced.

##### Input Payload Example:

```json
{
  "criteria": {
    "set": [
      1,
      2,
      3
    ]
    // These IDs refer to existing Criteria entities
  }
}
```

##### Mapping Example:

```php
$supplierPayload = new SupplierPayload();
$supplierPayload->criteria = new Relation(['set' => [1, 2, 3]]);

$supplier = $autoMapper->map($supplierPayload, $supplier);
```

In this case, Automapper replaces the entire `criteria` collection in the `Supplier` entity with the `Criteria` entities
having IDs 1, 2, and 3.

#### 4.2 Using the `add` and `remove` Operations

When `add` and/or `remove` operations are used, Automapper will modify the collection without replacing it. This allows
you to incrementally add or remove `Criteria` entities.

##### Input Payload Example:

```json
{
  "criteria": {
    "add": [
      4,
      5
    ],
    // Add Criteria with IDs 4 and 5
    "remove": [
      2
    ]
    // Remove Criteria with ID 2
  }
}
```

##### Mapping Example:

```php
$supplierPayload = new SupplierPayload();
$supplierPayload->criteria = new Relation(['add' => [4, 5], 'remove' => [2]]);

$supplier = $autoMapper->map($supplierPayload, $supplier);
```

In this case:

- The `Criteria` entities with IDs 4 and 5 are added to the `criteria` collection.
- The `Criteria` entity with ID 2 is removed from the collection.

### 5. **Controlling Auto-Mapping for Relations**

Automapper provides a flexible way to control whether relations are automatically mapped. You can disable or customize
the mapping behavior for specific relations using the **mapper context**.

#### 5.1 Disabling Auto-Mapping for Specific Relations

If you want to disable auto-mapping for a particular relation (e.g., `criteria`), you can pass an option to the mapper
context:

##### Example:

```php
$supplierPayload = new SupplierPayload();
$supplierPayload->criteria = new Relation(['set' => [1, 2, 3]]);

$supplier = $autoMapper->map($supplierPayload, $supplier, [
    'association_mappings' => [
        'criteria' => false, // Disable auto-mapping for 'criteria' relation
    ],
]);
```

Here, the `criteria` relation will not be automatically mapped, and you will need to handle the association manually.

#### 5.2 Disabling All Auto-Mapping

If you want to disable auto-mapping for **all** relations in the entity, you can set `association_mappings` to `false`:

##### Example:

```php
$supplier = $autoMapper->map($supplierPayload, $supplier, [
    'association_mappings' => false, // Disable all auto-mapping for relations
]);
```

### 6. **Key Considerations**

- **Single Value Relations**: If a relation is not a collection, only a single value should be provided in the `set`
  operation.
- **Conflict Prevention**: Avoid trying to add and remove the same entity simultaneously, as this creates a conflict.
- **Flexible Mapping Control**: Use `association_mappings` to toggle auto-mapping on or off for specific or all
  relations, based on your requirements.

### Conclusion

The Automapper with `RelationToEntityTransformer` provides a robust way to handle complex relationships like the
`Supplier` and `Criteria` example. It allows you to manage related entities with a flexible structure (`set`, `add`,
`remove`) while giving you full control over whether relations should be auto-mapped or manually handled via the mapper
context.