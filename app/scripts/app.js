import { IoC } from 'scalar';
import { Message } from './scoop/Message';
import { Form } from './scoop/Form';
import './fun';

IoC.provide(Message, Form);
