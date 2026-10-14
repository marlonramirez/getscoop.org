import { Module } from 'scalar';
import themeToggle from './components/theme-toggle';
import search from './components/documentation-search';
import documentation from './components/documentation';

export default new Module()
.compose('[data-theme-toggle]', themeToggle)
.compose('.search-container', search)
.compose('#main-docs', documentation);
