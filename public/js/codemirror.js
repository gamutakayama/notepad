import {
  closeBrackets,
  closeBracketsKeymap,
} from "https://esm.sh/@codemirror/autocomplete";
import {
  defaultKeymap,
  history,
  historyKeymap,
  indentLess,
  indentMore,
} from "https://esm.sh/@codemirror/commands";
import {
  markdown,
  markdownLanguage,
} from "https://esm.sh/@codemirror/lang-markdown";
import {
  bracketMatching,
  codeFolding,
  foldGutter,
  indentOnInput,
  indentUnit,
} from "https://esm.sh/@codemirror/language";
import { Compartment, EditorState } from "https://esm.sh/@codemirror/state";
import {
  crosshairCursor,
  drawSelection,
  dropCursor,
  EditorView,
  highlightActiveLine,
  highlightActiveLineGutter,
  highlightSpecialChars,
  keymap,
  lineNumbers,
  rectangularSelection,
} from "https://esm.sh/@codemirror/view";
import { dark } from "/public/js/codemirror-theme-dark.js";
import { light } from "/public/js/codemirror-theme-light.js";

export const initCodeMirror = (onDocChanged) => {
  const updateListener = EditorView.updateListener.of((update) => {
    if (update.docChanged) {
      onDocChanged(update);
    }
  });

  const darkMQL = window.matchMedia("(prefers-color-scheme: dark)");

  const theme = new Compartment();

  const editorView = new EditorView({
    doc: window.editorViewDoc,
    parent: document.getElementById("codemirror"),
    extensions: [
      lineNumbers(),
      highlightActiveLineGutter(),
      highlightSpecialChars(),
      history(),
      foldGutter({
        markerDOM: (open) => {
          const span = document.createElement("span");
          span.title = open ? "Fold line" : "Unfold line";

          const svg = document.createElementNS(
            "http://www.w3.org/2000/svg",
            "svg"
          );
          svg.setAttribute("width", "16");
          svg.setAttribute("height", "16");
          svg.setAttribute("viewBox", "0 0 16 16");
          svg.setAttribute("fill", "currentColor");

          const path = document.createElementNS(
            "http://www.w3.org/2000/svg",
            "path"
          );
          if (open) {
            path.setAttribute(
              "d",
              "M12.78 5.22a.749.749 0 0 1 0 1.06l-4.25 4.25a.749.749 0 0 1-1.06 0L3.22 6.28a.749.749 0 1 1 1.06-1.06L8 8.939l3.72-3.719a.749.749 0 0 1 1.06 0Z"
            );
          } else {
            path.setAttribute(
              "d",
              "M6.22 3.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.751.751 0 0 1-1.042-.018.751.751 0 0 1-.018-1.042L9.94 8 6.22 4.28a.75.75 0 0 1 0-1.06Z"
            );
          }

          svg.appendChild(path);

          span.appendChild(svg);

          return span;
        },
      }),
      codeFolding({ placeholderText: "⋯" }),
      drawSelection(),
      dropCursor(),
      EditorState.allowMultipleSelections.of(true),
      indentOnInput(),
      bracketMatching(),
      closeBrackets(),
      rectangularSelection(),
      crosshairCursor(),
      highlightActiveLine(),
      keymap.of([...closeBracketsKeymap, ...defaultKeymap, ...historyKeymap]),
      markdown({ base: markdownLanguage }),
      keymap.of([indentWithTab]),
      updateListener,
      theme.of(darkMQL.matches ? dark : light),
    ],
  });

  darkMQL.addEventListener("change", (e) => {
    editorView.dispatch({
      effects: theme.reconfigure(e.matches ? dark : light),
    });
  });

  return editorView;
};

const insertIndent = ({ state, dispatch }) => {
  if (state.selection.ranges.some((range) => !range.empty)) {
    return indentMore({ state, dispatch });
  }

  dispatch(
    state.update(state.replaceSelection(state.facet(indentUnit)), {
      scrollIntoView: true,
      userEvent: "input",
    })
  );

  return true;
};

const indentWithTab = { key: "Tab", run: insertIndent, shift: indentLess };
