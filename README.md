# TYPO3 Extension `yellowpages2`

![Build Status](https://github.com/jweiland-net/yellowpages2/workflows/CI/badge.svg)

With `yellowpages2` you can create, manage and display company entries.

## 1 Features

* Create and manage companies

## 2 Usage

### 2.1 Installation

#### Installation using Composer

The recommended way to install the extension is using Composer.

Run the following command within your Composer based TYPO3 project:

```
composer require jweiland/yellowpages2
```

#### Installation as extension from TYPO3 Extension Repository (TER)

Download and install `yellowpages2` with the extension manager module.

### 2.2 Minimal setup

1) Include the static TypoScript of the extension.
2) Create company and district records on a sysfolder.
3) Add yellowpages2 plugin on a page and select at least the sysfolder as startingpoint.
