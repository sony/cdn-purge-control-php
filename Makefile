# Makefile for CdnPurge

ROOTDIR = ./
SRCDIR = $(ROOTDIR)src/
DOCSDIR = $(ROOTDIR)docs/
ARTIFACTSDIR = $(ROOTDIR)artifacts/
BUILDARTIFACTSDIR = $(ROOTDIR)build/artifacts/
TESTSDIR = $(ROOTDIR)tests/
PHPUNIT = $(ROOTDIR)vendor/bin/phpunit
PHPUNITENVCONFIGFILE = $(TESTSDIR)phpunit.xml.dist.env
PHPUNITCONFIGFILE = phpunit.xml.dist
TESTREPORTFILE = $(BUILDARTIFACTSDIR)test-results/report.xml
COVERAGEDIR = $(BUILDARTIFACTSDIR)coverage

help:
	@echo "Please use \`make <target>' where <target> is one of"
	@echo "  clean          to remove build artifacts"
	@echo "  test           to perform unit tests.  Provide TEST to perform a specific test."
	@echo "  coverage       to perform unit tests with code coverage. Provide TEST to perform a specific test."
	@echo "  coverage-show  to show the code coverage report"
	@echo "  docs           to build the phpdocumentor docs"
	@echo "  docs-show      to view the phpdocumentor docs"
	@echo "  package        to build the phar and zip files"

clean:
	rm -rf $(ARTIFACTSDIR)*
	rm -rf $(BUILDARTIFACTSDIR)*
	cd $(DOCSDIR) && make clean && cd ..

# Clear any env set by phpunit config file when tests are run on Circle CI or Jenkins
# since test variables are already present there
clear-phpunit-env:
	grep -vFf $(PHPUNITENVCONFIGFILE) $(PHPUNITCONFIGFILE) > $(TESTSDIR)phpunit.xml.dist.tmp
	mv $(TESTSDIR)phpunit.xml.dist.tmp $(PHPUNITCONFIGFILE)

test:
	$(PHPUNIT) --log-junit=$(TESTREPORTFILE)

coverage:
	vendor/bin/phpunit --log-junit=$(TESTREPORTFILE) --coverage-html=$(COVERAGEDIR)

coverage-show:
	view-coverage

view-coverage:
	open $(COVERAGEDIR)index.html

docs:
	cd $(DOCSDIR) && make html && cd ..

docs-show:
	open $(DOCSDIR)/_build/_html/index.html

package:
	php build/packager.php

.PHONY: help clean clear-phpunit-env test coverage coverage-show docs docs-show package
