# Makefile for phpDocumentor
#

ROOTDIR = ../
SRCDIR = $(ROOTDIR)src/
BUILDDIR = _build/
HTMLDIR = $(BUILDDIR)/_html/
PHPDOC = phpdoc

help:
	@echo "Please use \`make <target>' where <target> is one of"
	@echo "  clean          to remove build files"
	@echo "  html           to create html doc files"

clean:
	-rm -rf $(BUILDDIR)*

html:
	$(PHPDOC) -d $(SRCDIR) -t $(HTMLDIR)

.PHONY: help clean html
