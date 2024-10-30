Custom.AssetPickerConfig = {
    endPoint: 'https://contenthub-demo.brix.ch/cora',
    apiKey: '3pi8ps5mm47tl8q9rsuddtpsl6',
    locale: 'de',
    searchScope: {
      rootNodes: [3371]
    },
    downloadFormats: {
      defaults: {
        unknown: 1,
        image: 8,
        document:5,
        video: 3,
        audio: 4,
        text: 5
        },
        supported: [1,2,3,4,5,7,8,10,11,12,13,14,15,16],
        additionalDownloadFormats: [1,2,3,4,5,7,8,10,11,12,13,14,15,16]
    },
    requiredAssetData: ['fileCategory', 'versionInformation', 'fileInformation'],
    forceDownloadSelection: true,
    keepSelectionOnExport: false
};