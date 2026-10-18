import { Module } from 'scalar';
import themeToggle from './components/theme-toggle';
import search from './components/documentation-search';
import documentation from './components/documentation';
import headerScroll from './components/header-scroll';

export default new Module()
.compose('body > header', headerScroll)
.compose('[data-theme-toggle]', themeToggle)
.compose('.search-container', search)
.compose('#main-docs', documentation);
