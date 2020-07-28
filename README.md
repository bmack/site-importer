# TYPO3 Site Importer

Built on top of TYPO3 and TYPO3 Console.

## Introduction

When it comes to deployment with TYPO3 projects, one tedious issue with TYPO3 is that
certain configuration is stored within the database.

While TYPO3 is doing a better job over and over again, there are still some remaining
issues left:

### sys_domain
Domain records are the most problematic issues. On a multi-site project (multiple websites in one installation),
it is very annoying to set up domain records over and over again, and keep them in sync.
This little tool will help out to overcome this issue.

### sys_template
Basic TypoScript information has to be still loaded from the database.

Since TYPO3 v8, there are good solutions such as the "bolt" extension, which
does not need to have a sys_template record anymore.

One change of a single database field inside the page solves the issue for most of our projects.

### sys_language
Most of our projects have a lot of langauges, however we add them to production
first to ensure that all IDs are the same throughout the system.


## Basic Usage
Our deployment setups use composer, .env and TYPO3 console. Deployment runs through
ansible or deployer, or completely transparent via platform.sh.


For our needs, we need to add the domain records to all of our projects, depending on the environment,
usually a local, testing, staging and production system.

In order to make use of the site importer, require this package via composer

`composer req bmack/site-importer`

After that, create a yaml file somewhere in your project repository. Our setups usually look like this:

    bin/
    conf/
    web/
    var/
    vendor/
    composer.json
    composer.lock

In this case, create a file `conf/site_dev.yaml` for the local setup. It looks like this

    domains:
      mode: "replace"
      table: "sys_domain"
      entries:
        - { domainName: "myproject.local", pid: 1 }
        - { domainName: "myproject-ch.local", pid: 1 }
        - { domainName: "myproject-microsite.local", pid: 13056 }

Other files for staging, production etc. can be created accordingly.

The option `mode` describes whether to truncate the database table before the entries are added.
but could also be set to `append` or `update`. If set to `append` the entries will be written as
pure insert without regard whether the record already exists.

If `mode` is set to `replace` the table is first truncated, then entries are inserted.

If `mode` is set to `update` the following happens:

* A check is made if the entry contains a `uid` property. If it does not, the entry is inserted as
  a new record (like it would happen if `mode` was set to `append`).
* If entry does contain a `uid` property, the script checks if the record exist in the table and
  if it does, an SQL update is done to update the record. If it does not exist, it is inserted.

The file accepts more than one table, thus, all language records could be added as well, however
this could be done in a generic `sites.yaml` file which works for all environments.

When deploying, simply call `bin/typo3cms siteimport:fromfile conf/site_dev.yaml` to import
the configured recordsets.

### platform.sh
For platform.sh we use `bin/typo3cms siteimport:fromfile conf/site_$PLATFORM_BRANCH.yaml`
in the post-deploy hook to replace the records for a specific branch.
