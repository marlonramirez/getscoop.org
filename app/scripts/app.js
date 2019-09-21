import { Module } from 'scalar';
import Messenger from './services/Messenger';
import Message from './components/message';
import Form from './components/form';
import Menu from './components/menu';
import Navbar from './components/navbar';

new Module(Messenger)
.compose('#msg', Message)
.compose('.scoop-form', Form)
.compose('header', Navbar)
.compose('#main-docs', Menu);
