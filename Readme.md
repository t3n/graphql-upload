
# t3n.GraphQL.Upload

Sidecar package for [t3n/graphql](https://github.com/t3n/graphql) that brings an `Upload` scalar and enables you to handle file uploads in your schema.

Simply install the package via composer:
```
composer require t3n/graphql-upload
```

## Configuration
This package ships all needed parts. However, you must add the typeDefs and the Resolver to your schema like this:
```yaml
t3n:
  GraphQL:
    endpoints:
      'your-endpoint': #use your endpoint variable here
        schemas:
          upload:
            typeDefs: 'resource://t3n.GraphQL.Upload/Private/GraphQL/schema.upload.graphql'
            resolvers:
              Upload: 't3n\GraphQL\Upload\Resolver\Type\UploadResolver'
```
This is everything you need to do. Once configured you can use the `Upload` scalar in your app.

## Usage
To use the `Upload` scalar you might want to add it as an arg to a mutation like this:

```graphql
type Mutation {
    uploadFile(file: Upload): String
}
```

This package will handle the upload itself and pass an `Neos\Http\Factories\FlowUploadedFile` to your `MutationResolver`. Within your resolver method your could for instance import your resource:

```php
class MutationResolver implements ResolverInterface
{
    /**
     * @Flow\Inject
     *
     * @var ResourceManager
     */
    protected $resourceManager;

    public function uploadFile($_, $variables): string
    {
        /** @var FlowUploadedFile $file */
        $file = $variables['file'];

        $resource = $this->resourceManager->importResource($file->getStream()->detach());
        $resource->setFilename($file->getClientFilename());
        $resource->setMediaType($file->getClientMediaType());

        return $file->getClientFilename();
    }
}
```

## Some notes

To actually use file upload your frontend client must use `multipart/form-data` in your forms. This Package is tested with a react app that uses https://github.com/jaydenseric/apollo-upload-client
