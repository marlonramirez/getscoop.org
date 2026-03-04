import { Module } from 'scalar';
import Menu from './components/menu';

new Module()
.compose('#main-docs', Menu)
.execute();
