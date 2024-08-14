import { Module } from 'scalar';
import Messenger from './scoop/services/Messenger';
import Menu from './components/menu';

new Module(Messenger)
.compose('#main-docs', Menu)
.execute();
