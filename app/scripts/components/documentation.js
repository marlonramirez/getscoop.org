import { inject } from 'scalar';
import DocumentationRenderer from '../services/DocumentationRenderer';
import diagramViewer from './diagram-viewer';
import menu from './menu';

export default inject(DocumentationRenderer)(($, renderer) => {
  const viewer = diagramViewer($);
  return {
    ...menu($),
    ...viewer,
    mount: event => {
      viewer.mount(event);
      renderer.render(event.currentTarget).catch(error => console.error('Unable to render documentation', error));
    },
    unmount: () => {
      viewer.unmount();
      renderer.dispose();
    }
  };
});
