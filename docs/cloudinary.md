

# uploading file in cloudinary

uploading single file

```php
    // Validate the file
    $request->validate([
        'files.*' => 'required|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048'
    ]);

    $uploadedFiles = [];
    foreach($request->file('files') as $file){
        $uploadedFiles[] = Cloudinary::uploadApi()->upload($file->getRealPath());
    }
    return $uploadedFiles;

```
Response format

```json
[
    {
        "asset_id": "960b8af3452bf89fbc269b00485ca1f8",
        "public_id": "pgladekxohjbwd8hyoua",
        "version": 1743945502,
        "version_id": "76d50d55ce1ed8063a4a17eb2f4ba37a",
        "signature": "d5745bb3b667a0e2cc0ef30a38b52ee119e86287",
        "width": 1024,
        "height": 1024,
        "format": "jpg",
        "resource_type": "image",
        "created_at": "2025-04-06T13:18:22Z",
        "tags": [],
        "bytes": 135098,
        "type": "upload",
        "etag": "cfebb2ec50cb139afa84f7e9befbd78e",
        "placeholder": false,
        "url": "http://res.cloudinary.com/dh6caelii/image/upload/v1743945502/pgladekxohjbwd8hyoua.jpg",
        "secure_url": "https://res.cloudinary.com/dh6caelii/image/upload/v1743945502/pgladekxohjbwd8hyoua.jpg",
        "asset_folder": "",
        "display_name": "pgladekxohjbwd8hyoua",
        "original_filename": "phpMp0WHy",
        "api_key": "73748611698"
    }
]
```
-------------------

Uploading multiple files

```php
    // Validate the file
    $request->validate([
        'files.*' => 'required|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048'
    ]);

    $uploadedFiles = [];
    foreach($request->file('files') as $file){
        $uploadedFiles[] = Cloudinary::uploadApi()->upload($file->getRealPath());
    }
    return $uploadedFiles;

```

Response format

```json
[
    {
        "asset_id": "960b8af3452bf89fbc269b00485ca1f8",
        "public_id": "pgladekxohjbwd8hyoua",
        "version": 1743945502,
        "version_id": "76d50d55ce1ed8063a4a17eb2f4ba37a",
        "signature": "d5745bb3b667a0e2cc0ef30a38b52ee119e86287",
        "width": 1024,
        "height": 1024,
        "format": "jpg",
        "resource_type": "image",
        "created_at": "2025-04-06T13:18:22Z",
        "tags": [],
        "bytes": 135098,
        "type": "upload",
        "etag": "cfebb2ec50cb139afa84f7e9befbd78e",
        "placeholder": false,
        "url": "http://res.cloudinary.com/dh6caelii/image/upload/v1743945502/pgladekxohjbwd8hyoua.jpg",
        "secure_url": "https://res.cloudinary.com/dh6caelii/image/upload/v1743945502/pgladekxohjbwd8hyoua.jpg",
        "asset_folder": "",
        "display_name": "pgladekxohjbwd8hyoua",
        "original_filename": "phpMp0WHy",
        "api_key": "73748611698"
    },
    {
        "asset_id": "b231d97ef4ac2e3af9db5afa8791123d",
        "public_id": "w0akgb3obxgi5iqzehdl",
        "version": 1743945504,
        "version_id": "47fcc15551d6026a6ffba62e5870a88b",
        "signature": "135c1b8ae51c9d95521fbebd0331c73b88e0832f",
        "width": 512,
        "height": 512,
        "format": "png",
        "resource_type": "image",
        "created_at": "2025-04-06T13:18:24Z",
        "tags": [],
        "bytes": 54464,
        "type": "upload",
        "etag": "50119cb838f967e5f00c8172c9cc1868",
        "placeholder": false,
        "url": "http://res.cloudinary.com/dh6caelii/image/upload/v1743945504/w0akgb3obxgi5iqzehdl.png",
        "secure_url": "https://res.cloudinary.com/dh6caelii/image/upload/v1743945504/w0akgb3obxgi5iqzehdl.png",
        "asset_folder": "",
        "display_name": "w0akgb3obxgi5iqzehdl",
        "original_filename": "phpdTvLSJ",
        "api_key": "73748611698"
    }
]
```