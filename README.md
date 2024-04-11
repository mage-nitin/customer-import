# Customer Import Module

### Requirement : 

We want you to write code that supports importing customers and their addresses.
The requirement is to import from a sample CSV or JSON at present however the
code should be written to support importing from other sources in future. We've
intentionally used a slightly ambiguous term of "profiles" to allow for future
scope e.g. two CSVs but with differing columns, hence two profiles.

The two sample files which you need to accomodate are provided.

The user interface to the code should be via a CLI command as below:

`bin/magento customer:import <profile-name> <source>`

So to import from the CSV and the JSON respectively the user would execute
either one of the following (you can vary the command format slightly if you
wish):
```
bin/magento customer:import sample-csv sample.csv
bin/magento customer:import sample-json sample.json
```

### Module To Consider : 
- Neat and concise code that is well-written and easy to read
- Good architecture which supports the SOLID principles
- Specifically, the ability to add support for additional profiles via extension
  and not modification
- Adherence to the latest Magento standards
- Code that is your own (e.g. no copy-pasting), though the correct usage of
  libraries is encouraged via composer (any libraries used must be publically
  available)
- Your code should be unit tested as per good Engineering Practice

# Dev Solution 

Command has been modified, So we can get rid of passing 1 additional parameter "profile".
Expecting the key=>values in json and cav or any file furture extend must follow minimum requirement columns.

`bin/magento customer:import <source path>`

## How to test

composer require magenit/magento2-customers-import
php bin/magento setup:upgrade
php bin/magento cache:clean
store csv and json file in val/ImportExport directory (recommended and good approach)

### execute command :
#### php bin/magento ng:customers:import --profile-file=var/importExport/sample.csv
Or/And
#### php bin/magento ng:customers:import --profile-file=var/importExport/sample.json
Verify Admin.

### Achieved Target in Requirement implementation(Module To Consider)
- Neat and concise code that is well-written and easy to read : yes
- Good architecture which supports the SOLID principles  : yes
- Specifically, the ability to add support for additional profiles via extension 
  and not modification  : yes
- Adherence to the latest Magento standards  : yes
- Code that is your own (e.g. no copy-pasting), though the correct usage of 
  libraries is encouraged via composer (any libraries used must be publically
  available)  : yes
- Your code should be unit tested as per good Engineering Practice : Partial but No (due to time constraint.)

### How To extend this module to support for additional profile whithout modifying code :

Create custom module and command.
extends MageNit\CustomersImport\Model\CustomerImport
Major function to override
1. setAllowedFileExtension
2. prepareDataFromFile
3. prepareDataForProcessing (please follow customerInterface)

### What not Done
Unit testing not done, it takes more time and efforts,
so due to time constraints it difficult to achieve in this test.
