version 6.0
if &cp | set nocp | endif
let s:cpo_save=&cpo
set cpo&vim
nmap gx <Plug>NetrwBrowseX
nnoremap <silent> <Plug>NetrwBrowseX :call netrw#NetrwBrowseX(expand("<cWORD>"),0)
let &cpo=s:cpo_save
unlet s:cpo_save
set backspace=2
set fileencodings=ucs-bom,utf-8,default,latin1
set helplang=cn
set hlsearch
set modelines=0
set window=0
" vim: set ft=vim :
syntax on
set nocompatible
set number
set autoindent
set smartindent
set showmatch
set hls
set incsearch
set shiftwidth=4
set ts=4
set ruler
set encoding=utf-8
set fileencodings=utf-8,chinese,latin-1
