import { render, screen } from '@testing-library/vue'
import { describe, it, expect } from 'vitest'
import Heading from '../Heading.vue'

describe('Heading', () => {
  it('renders title correctly', () => {
    const title = 'Test Title'
    render(Heading, {
      props: { title }
    })

    expect(screen.getByRole('heading', { level: 2 })).toHaveTextContent(title)
  })

  it('renders description when provided', () => {
    const title = 'Test Title'
    const description = 'Test description'
    
    render(Heading, {
      props: { title, description }
    })

    expect(screen.getByText(description)).toBeInTheDocument()
  })

  it('does not render description when not provided', () => {
    const title = 'Test Title'
    
    render(Heading, {
      props: { title }
    })

    expect(screen.queryByText(/test description/i)).not.toBeInTheDocument()
  })

  it('applies correct CSS classes', () => {
    const title = 'Test Title'
    
    render(Heading, {
      props: { title }
    })

    const container = screen.getByRole('heading').parentElement
    expect(container).toHaveClass('mb-8', 'space-y-0.5')
    
    const heading = screen.getByRole('heading', { level: 2 })
    expect(heading).toHaveClass('text-xl', 'font-semibold', 'tracking-tight')
  })
}) 