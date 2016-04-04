#!/bin/sh
#/Library/TeX/texbin/latex $1.tex
#/Library/TeX/texbin/dvips $1.dvi
#ps2pdf $1.ps
/Library/TeX/texbin/pdflatex $1.tex
